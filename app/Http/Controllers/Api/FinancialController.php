<?php

namespace App\Http\Controllers\Api;

use App\Models\Wechat\ArrearsConfig;
use App\Models\Wechat\ArrearsLog;
use App\Models\Wechat\ArrearsOrders;
use App\Models\Wechat\DfArrearsLog;
use App\Models\Wechat\DfArrearsOrders;
use App\Models\Wechat\WechatPay;
use App\Models\Wxuser;
use App\Services\FansEvent;
use App\Services\IcbcService;
use App\Services\OpenlibService;
use App\Services\PayHelper;
use App\Services\PayLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinancialController extends BaseController
{

    public function getArrears(Request $request)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $token = $request->user()->token;
        $openlibService = OpenlibService::make($token);

        $response = $openlibService->searchdebt(['rdid' => $reader['rdid']]);
        $data = [
            'total' => 0,
            'list' => [],
            'message' => ''
        ];
        if ($response['success'] == false) {
            $data['message'] = Arr::get($response, 'messagelist.0.message');
            return $this->success($data, false);
        }

        $list = $response['debtdetails'];
        $isbnImg = [];
        foreach ($list as $k => $v) {
            if (isset($v['isbn'])) {
                $isbnImg[$v['isbn']] = str_replace('-', '', $v['isbn']);
            }
        }
        unset($k, $v);
        $isbnImg = $this->giveImgApi($isbnImg);
        foreach ($list as $k => $v) {
            $list[$k]['imgurl'] = '';
            if (isset($v['isbn'])) {
                $list[$k]['imgurl'] = (isset($isbnImg[$v['isbn']])) ? $isbnImg[$v['isbn']] : '';
            }
        }
        unset($k, $v);
        $data['total'] = $response['totaldebt'];
        $data['list'] = $list;
        return $this->success($data, true);
    }

    public function payArrears(Request $request, PayHelper $payHelper, PayLogService $payLogService)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $tranids = $request->input('tranids');
        if (empty($tranids)) return $this->failed('缺少必须参数!', 400);
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $openlibService = OpenlibService::make($token);

        //创建订单  关联 财经订单id
        $tranids = explode(',', $tranids);
        $params = ['rdid' => $reader['rdid']];
        $response = $openlibService->searchdebt($params);

        if ($response['success'] == false) {
            return $this->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        $debtdetails = Arr::get($response, 'debtdetails');
        $checkTranids = array_column($debtdetails, 'tranid');
        $intersect = array_intersect($checkTranids, $tranids);
        $diff = array_diff($tranids, $intersect);
        if (count($diff) !== 0) {
            return $this->message('数据存在差异,请刷新再试!', false);
        }
        $out_trade_no = $payHelper->GenerateOrderNumber('ZFQK');
        $created_at = date('Y-m-d H:i:s');

        $arrearsLog = [];
        $total = 0;
        foreach ($debtdetails as $k => $v) {
            if (in_array($v['tranid'], $tranids)) {
                $arrearsLog[] = [
                    'token' => $token,
                    'price' => $v['fee'],
                    'feetype' => $v['feetype'],
                    'rdid' => $reader['rdid'],
                    'openid' => $openid,
                    'status' => 0,
                    'is_pay' => 1,
                    'tranid' => $v['tranid'],
                    'order_id' => $out_trade_no,
                    'created_at' => $created_at
                ];
                $total += $v['fee'];
            }
        }
        //先生成订单  返回支付配置&&id
        $payment = WechatPay::initialize($token);
        $order = [
            'body' => '支付欠款_' . $out_trade_no,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total * 100,
            'trade_type' => 'JSAPI',
            'openid' => $openid,
            'notify_url' => route('wxPay_payArrears', $token)
        ];
        $result = $payment->order->unify($order);

        if (array_get($result, 'result_code') == 'SUCCESS') {
            $log = array_only($order + $result,
                ['openid', 'out_trade_no', 'total_fee', 'trade_type', 'prepay_id', 'return_code']
            );
            //下单成功写入日志
            $payLogService->placeOrder($token, 'payArrears', $log);
            //申请数据 存Log
            DB::table('financial_arrears_log')->insert($arrearsLog);
            //生成回调订单
            $callbackOrder = [
                'token' => $token,
                'price' => $total,
                'origin_price' => $total,
                'cash_fee' => 0,
                'openid' => $openid,
                'pay_status' => 0,
                'pay_type' => 0,
                'prepay_id' => $result['prepay_id'],
                'order_id' => $out_trade_no,
                'rdid' => $reader['rdid']
            ];
            $logID = ArrearsOrders::create($callbackOrder);
            $jssdk = $payment->jssdk;
            $sdkConfig = $jssdk->sdkConfig($logID['prepay_id']);
            $data = ['is_pay' => 1, 'sdkConfig' => $sdkConfig, 'logID' => $logID['id']];
            return $this->success($data, true);
        }
        return $this->failed(Arr::get($result, 'return_msg'), 400);
    }

    public function payArrearsIcbc(Request $request, PayHelper $payHelper, PayLogService $payLogService)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $tranids = $request->input('tranids');
        $return_url = urldecode($request->input('return_url'));
        if (empty($tranids) || empty($return_url)) return $this->failed('缺少必须参数!', 400);

        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $openlibService = OpenlibService::make($token);

        //创建订单  关联 财经订单id
        $tranids = explode(',', $tranids);
        $params = ['rdid' => $reader['rdid']];
        $response = $openlibService->searchdebt($params);

        if ($response['success'] == false) {
            return $this->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        $debtdetails = Arr::get($response, 'debtdetails');
        $checkTranids = array_column($debtdetails, 'tranid');
        $intersect = array_intersect($checkTranids, $tranids);
        $diff = array_diff($tranids, $intersect);
        if (count($diff) !== 0) {
            return $this->message('数据存在差异,请刷新再试!', false);
        }
        $out_trade_no = $payHelper->GenerateOrderNumber('ZFQK');
        $created_at = date('Y-m-d H:i:s');

        $arrearsLog = [];
        $total = 0;
        foreach ($debtdetails as $k => $v) {
            if (in_array($v['tranid'], $tranids)) {
                $arrearsLog[] = [
                    'token' => $token,
                    'price' => $v['fee'],
                    'feetype' => $v['feetype'],
                    'rdid' => $reader['rdid'],
                    'openid' => $openid,
                    'status' => 0,
                    'is_pay' => 1,
                    'tranid' => $v['tranid'],
                    'order_id' => $out_trade_no,
                    'created_at' => $created_at
                ];
                $total += $v['fee'];
            }
        }

        $log = [
            'openid' => $openid,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total * 100,
            'trade_type' => 'ICBC-JSAPI',
            'prepay_id' => '',
            'return_code' => ''
        ];
        //申请数据 存Log
        DB::table('financial_arrears_log')->insert($arrearsLog);
        //下单成功写入日志
        $payLogService->placeOrder($token, 'payArrears', $log);

        //生成回调订单
        $callbackOrder = [
            'token' => $token,
            'price' => $total,
            'origin_price' => $total,
            'cash_fee' => 0,
            'openid' => $openid,
            'pay_status' => 0,
            'pay_type' => 2,    //0 - 微信支付  1 - 扫码支付    2 - 工行聚合支付
            'prepay_id' => '',
            'order_id' => $out_trade_no,
            'rdid' => $reader['rdid']
        ];
        $logID = ArrearsOrders::create($callbackOrder);

        $now = time();
        $wxuser = $this->getWxuserCache($token);
        $requestData = array(
            "method" => 'POST',
            "isNeedEncrypt" => true,
            "extraParams" => null,
            "biz_content" => array(
                "tp_app_id" => $wxuser->appid,
                "tp_open_id" => $openid,
                "out_trade_no" => $out_trade_no,
                "tran_type" => "OfflinePay",
                "order_date" => (string) date('YmdHis', $now),
                "end_time" => (string) date('YmdHis', $now+300),
                "goods_body" => '支付欠款',
                "goods_detail" => '{"good_name":"支付欠款","good_id":1,"good_num":"1"}',
                "order_amount" => (string) ($total * 100),    //总金额(单位分)
                "spbill_create_ip" => $request->getClientIp(),
                "install_times" => "1",
                "return_url" => $return_url . "&logID=" . $logID->id,   // 支付回显
                "notify_url" => route('aggregatePayment_default', $token), // 支付结果通知
                "notify_type" => "HS",
                "result_type" => "0",
                'order_channel' => "101"
            ),
        );

        $msg_id = $payHelper->GenerateMsgId($token);
        $icbc = IcbcService::make($token);
        $formParams = $icbc->buildFormParams($requestData, $msg_id, 'api_payment');

        return $this->success($formParams, true);
    }

    /**
     * @param Request $request
     * @return mixed
     *  status  =>  1       => 销账成功
     *  pay_status  =>  1   => 支付成功
     */
    public function payArrearsStatus(Request $request)
    {
        $id = $request->route('id');
        if (!is_numeric($id)) return $this->failed('非法访问!', 400);
        $openid = $request->user()->openid;
        $token = $request->user()->token;
        $arrearsOrders = ArrearsOrders::where([
            'id' => $id,
            'token' => $token,
            'openid' => $openid
        ])->first(['order_id', 'pay_status', 'id']);

        if (empty($arrearsOrders)) return $this->failed('非法访问!', 400);
        $response = ['status' => 0, 'payStatus' => 0, 'typeData' => '', 'typeName' => ''];
        if ($arrearsOrders['pay_status'] == 1) {
            $response['payStatus'] = 1;
            $status = ArrearsLog::where([
                'token' => $token,
                'order_id' => $arrearsOrders['order_id']
            ])->pluck('status');
            $check = collect($status)->every(function ($value, $key) {
                return $value === 1;
            });
            if ($check) {
                $response['status'] = 1;
                $eventService = new FansEvent($request->user()->token, $request->user()->openid);
                $eventData = $eventService->check('payArrearsStatus');
                if ($eventData) {
                    $response = array_merge($response, $eventData);
                }
            }
        }
        return $this->success($response, true);
    }

    public function payArrearsLog(Request $request)
    {
        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $row = $request->input('row', 10);
        $token = $request->user()->token;
        $arrearsOrders = ArrearsOrders::select('id', 'order_id', 'pay_status', 'pay_at', 'created_at', 'price')
            ->with('logList')->where(['token' => $token, 'rdid' => $reader['rdid']])
            ->orderBy('created_at', 'Desc')->paginate($row);
        return $arrearsOrders;
    }

    public function arrearsConfig(Request $request)
    {
        $token = $request->input('token');
        $arrearsConfig = ArrearsConfig::where('token', $token)->first();
        if (empty($arrearsConfig)) return $this->message('抱歉功能尚未开启', false);
        $response = [
            'pay_sw' => $arrearsConfig['pay_sw'],
            'df_sw' => $arrearsConfig['df_sw'],
            'payment_type' => $arrearsConfig['payment_type']
        ];
        return $this->success($response, true);
    }

    //************************** 代付 *****************************//
    public function checkGuest(Request $request)
    {
        if (!$request->filled(['rdid', 'password'])) {
            return $this->failed('缺少必须参数!', 400);
        }
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $rdid = $request->input('rdid');
        $password = $request->input('password');
        //读者证认证
        $openlibService = OpenlibService::make($token);
        $response = $openlibService->confirmreader($rdid, $password);
        if ($response['success'] == false) {
            return $this->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        $enData = [
            'openid' => $openid,
            'rdid' => $rdid,
            'time' => time()
        ];
        $code = encrypt($enData);
        return $this->success(['secretCode' => $code, 'rdid' => $rdid], true);
    }

    public function getDfArrears(Request $request)
    {
        $secretCode = $request->input('secretCode');
        if (!$secretCode) return $this->failed('缺少必须参数!', 400);
        $data = decrypt($secretCode);
        $openid = $data['openid'];
        $rdid = $data['rdid'];
        $time = $data['time'];
        $token = $request->user()->token;
        if ($openid != $request->user()->openid || time() - $time > 1800) {
            return $this->failed('非法访问!', 400);
        }
        $openlibService = OpenlibService::make($token);
        $response = $openlibService->searchdebt(['rdid' => $rdid]);
        $data = [
            'total' => 0,
            'list' => [],
            'message' => ''
        ];
        if ($response['success'] == false) {
            $data['message'] = Arr::get($response, 'messagelist.0.message');
            return $this->success($data, false);
        }

        $list = $response['debtdetails'];
        $isbnImg = [];
        foreach ($list as $k => $v) {
            if (isset($v['isbn'])) {
                $isbnImg[$v['isbn']] = str_replace('-', '', $v['isbn']);
            }
        }
        unset($k, $v);
        $isbnImg = $this->giveImgApi($isbnImg);
        foreach ($list as $k => $v) {
            $list[$k]['imgurl'] = '';
            if (isset($v['isbn'])) {
                $list[$k]['imgurl'] = (isset($isbnImg[$v['isbn']])) ? $isbnImg[$v['isbn']] : '';
            }
        }
        unset($k, $v);
        $data['total'] = $response['totaldebt'];
        $data['list'] = $list;
        return $this->success($data, true);
    }

    public function payDfArrears(Request $request, PayHelper $payHelper, PayLogService $payLogService)
    {
        $token = $request->user()->token;
        $tranids = $request->input('tranids');
        $secretCode = $request->input('secretCode');
        if (empty($tranids) || empty($secretCode)) return $this->failed('缺少必须参数!', 400);
        $data = decrypt($secretCode);
        $openid = $data['openid'];
        $rdid = $data['rdid'];
        $time = $data['time'];
        if ($openid != $request->user()->openid || time() - $time > 1800) {
            return $this->failed('非法访问!', 400);
        }
        //创建订单  关联 财经订单id
        $tranids = explode(',', $tranids);
        $params = ['rdid' => $rdid];

        $openlibService = OpenlibService::make($token);
        $response = $openlibService->searchdebt($params);

        if ($response['success'] == false) {
            return $this->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        $debtdetails = Arr::get($response, 'debtdetails');
        $checkTranids = array_column($debtdetails, 'tranid');
        $intersect = array_intersect($checkTranids, $tranids);
        $diff = array_diff($tranids, $intersect);
        if (count($diff) !== 0) {
            return $this->message('数据存在差异,请刷新再试!', false);
        }
        $out_trade_no = $payHelper->GenerateOrderNumber('DFQK');
        $created_at = date('Y-m-d H:i:s');

        $arrearsLog = [];
        $total = 0;
        foreach ($debtdetails as $k => $v) {
            if (in_array($v['tranid'], $tranids)) {
                $arrearsLog[] = [
                    'token' => $token,
                    'price' => $v['fee'],
                    'feetype' => $v['feetype'],
                    'rdid' => $rdid,
                    'openid' => $openid,
                    'status' => 0,
                    'is_pay' => 1,
                    'tranid' => $v['tranid'],
                    'order_id' => $out_trade_no,
                    'created_at' => $created_at
                ];
                $total += $v['fee'];
            }
        }
        //先生成订单  返回支付配置&&id
        $payment = WechatPay::initialize($token);
        $order = [
            'body' => '代付欠款_' . $out_trade_no,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total * 100,
            'trade_type' => 'JSAPI',
            'openid' => $openid,
            'notify_url' => route('wxPay_dfArrears', $token)
        ];
        $result = $payment->order->unify($order);

        if (array_get($result, 'result_code') == 'SUCCESS') {
            $log = array_only($order + $result,
                ['openid', 'out_trade_no', 'total_fee', 'trade_type', 'prepay_id', 'return_code']
            );
            //下单成功写入日志
            $payLogService->placeOrder($token, 'dfArrears', $log);
            //申请数据 存Log
            DB::table('financial_df_arrears_log')->insert($arrearsLog);
            //生成回调订单
            $callbackOrder = [
                'token' => $token,
                'price' => $total,
                'origin_price' => $total,
                'cash_fee' => 0,
                'openid' => $openid,
                'pay_status' => 0,
                'pay_type' => 0,
                'prepay_id' => $result['prepay_id'],
                'order_id' => $out_trade_no,
                'rdid' => $rdid
            ];
            $logID = DfArrearsOrders::create($callbackOrder);
            $jssdk = $payment->jssdk;
            $sdkConfig = $jssdk->sdkConfig($logID['prepay_id']);
            $data = ['is_pay' => 1, 'sdkConfig' => $sdkConfig, 'logID' => $logID['id']];
            return $this->success($data, true);
        }
        return $this->failed(array_get($result, 'return_msg'), 400);
    }

    public function payDfArrearsStatus(Request $request, $id)
    {
        if (!is_numeric($id)) return $this->failed('非法访问!', 400);
        if (!$request->filled(['token', 'openid'])) return $this->failed('缺少参数!', 400);
        $openid = $request->input('openid');
        $token = $request->input('token');
        $arrearsOrders = DfArrearsOrders::where([
            'id' => $id,
            'token' => $token,
            'openid' => $openid
        ])->first(['order_id', 'pay_status', 'id']);
        if (empty($arrearsOrders)) return $this->failed('非法访问!', 400);
        $response = ['status' => 0, 'payStatus' => 0, 'typeData' => '', 'typeName' => ''];
        if ($arrearsOrders['pay_status'] == 1) {
            $response['payStatus'] = 1;
            $status = DfArrearsLog::where([
                'token' => $token,
                'order_id' => $arrearsOrders['order_id']
            ])->pluck('status');
            $check = collect($status)->every(function ($value, $key) {
                return $value === 1;
            });
            if ($check) {
                $response['status'] = 1;
                $eventService = new FansEvent($token, $openid);
                $eventData = $eventService->check('payDfArrearsStatus');
                if ($eventData) {
                    $response = array_merge($response, $eventData);
                }
            }
        }
        return $this->success($response, true);
    }

    public function dfArrearsLog(Request $request)
    {
        $row = $request->input('row', 10);
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $arrearsOrders = DfArrearsOrders::select('id', 'order_id', 'pay_status', 'pay_at', 'created_at', 'price', 'rdid')
            ->with('logList')->where(['token' => $token, 'openid' => $openid])
            ->orderBy('created_at', 'Desc')->paginate($row);
        return $arrearsOrders;
    }

}
