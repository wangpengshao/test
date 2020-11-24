<?php

namespace App\Http\Controllers\Api\Custom;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Certificate;
use App\Models\Wechat\CertificateLog;
use App\Models\Wechat\CertificateOrders;
use App\Models\Wechat\TransactType;
use App\Models\Wechat\WechatPay;
use App\Models\Wxuser;
use App\Services\IcbcService;
use App\Services\OpenlibService;
use App\Services\PayHelper;
use App\Services\PayLogService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Vue前端 定制页面接口
 * Class VueController
 * @package App\Http\Controllers\Api\Custom
 */
class VueController extends Controller
{
    // 通用api辅助函数
    use ApiResponse;

    /**
     * 开采事业部 海南省 定制办证
     * 1.固定默认密码
     * 2.分馆选择
     * 3.非海南省身份证，查询社保缴纳情况
     * @param Request       $request
     * @param PayLogService $payLogService
     * @param PayHelper     $payHelper
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function haiNanCefSave(Request $request, PayLogService $payLogService, PayHelper $payHelper)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $return_url = urldecode($request->input('return_url'));
        if ($token != '6fe4c383a57b' && $token != '18c6684c') {
            abort(404);
        }
        if (!$request->filled(['libcode', 'typeId', 'name', 'idCard', 'phone'])) {
            return $this->failed('lack of parameter!', 400);
        }
        [
            'libcode' => $libcode,
            'typeId' => $typeId,
            'name' => $name,
            'idCard' => $idCard,
            'phone' => $phone,
        ] = $request->input();
        $rdpasswd = 123456;  //海南规定默认密码

        $certificateWhere = ['token' => $token, 'status' => 1, 'type' => 0];
        $certificate = Certificate::where($certificateWhere)->first(['options', 'data', 'rdid_type', 'imgData']);
        if (empty($certificate)) {
            return $this->failed('办证功能尚未开启!', 400);
        }
        if (!in_array($typeId, $certificate['options'])) {
            return $this->failed('该读者类型不存在!', 400);
        }
//        //判断是否符合海南身份证，不符合则调用社保接口进行查询
//        if (Str::limit($idCard, 2, '') != "46") {
//            $url = 'http://120.79.63.232:8083/opcs/interface/validateAssociationCardFromOpcs?';
//            $params = http_build_query([
//                'certify' => $idCard,
//                'rdidName' => $name
//            ]);
//            $http = new Client();
//            $response = $http->get($url . $params);
//            $response = (string)$response->getBody();
//            //1  ：查询成功，存在缴费记录
//            if ($response != 1) {
//                $text = '';
//                switch ($response) {
//                    case 0:
//                        $text = '未查询到用户的社保记录';
//                        break;
//                    case -1:
//                        $text = '社保查询失败，请稍后再试';
//                        break;
//                    case 2:
//                        $text = '社保卡挂失状态，不能参加选书活动';
//                        break;
//                    case 3:
//                        $text = '社保卡注销状态，不能参加选书活动';
//                        break;
//                }
//                return $this->failed($text, 400);
//            }
//        }

        $openlibService = OpenlibService::make($token);
        $searchReader = $openlibService->searchreader($idCard);
        if ($searchReader['success'] == true || Arr::get($searchReader, 'messagelist.0.code') == 'R00130') {
            return $this->failed('抱歉，您已经办理读者证，请勿重复办理!', 400);
        }
        $transactType = TransactType::find($typeId);
        //提交数据(基础)
        $basic = [
            'rdname' => $name,
            'rdpasswd' => encrypt($rdpasswd),
            'rdcertify' => $idCard,
            'operator' => 'WXZH',
            'rdlib' => $libcode,
            'rdtype' => $transactType['value'],
            'rdcfstate' => 1,
            'rdid' => $idCard
        ];
//        if ($certificate['rdid_type'] == 1) {
//            $basic['rdid'] = $idCard;
//        }
        $params = [
            'rdloginid' => $phone
        ];
        //提交数据(完整)
        $newReader = $params + $basic;

        //组装LOG数据
        $certificateLog = $basic;
        $certificateLog['token'] = $token;
        $certificateLog['openid'] = $openid;
        $certificateLog['type'] = 0;
        $certificateLog['data'] = $params;
        if ($transactType['is_check'] == 1) {
            $certificateLog['check_s'] = -1;
        }

        if ($transactType['is_pay'] == 1 && $transactType['money'] > 0) {

            $wxconfig = Wxuser::getCache($token);

            if($wxconfig['payment_opt'] == 1){
                if(empty($return_url)){
                    return $this->failed('参数缺失', 400);
                }
                //生成商户订单号
                $out_trade_no = $payHelper->GenerateOrderNumber('BZYJ');
                $log = [
                    'openid' => $openid,
                    'out_trade_no' => $out_trade_no,
                    'total_fee' => $transactType['money'] * 100,
                    'trade_type' => 'ICBC-JSAPI',
                    'prepay_id' => '',
                    'return_code' => ''
                ];
                $payLogService->placeOrder($token, 'Certificate', $log);
                $certificateLog['status'] = 0;
                $certificateLog['is_pay'] = 1;
                $certificateLog['order_id'] = $out_trade_no;
                $logID = CertificateLog::create($certificateLog);

                //生成回调订单
                $callbackOrder = [
                    'token' => $token,
                    'price' => $transactType['money'],
                    'origin_price' => $transactType['money'],
                    'cash_fee' => 0,
                    'openid' => $openid,
                    'pay_status' => 0,
                    'pay_type' => 2,    //工行公众号聚合支付
                    'order_id' => $out_trade_no,
                ];
                CertificateOrders::create($callbackOrder);

                $now = time();
                $wxuser = Wxuser::getCache($token);
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
                        "goods_detail" => '{"good_name":"普通办证押金","good_id":1,"good_num":"1"}',
                        "order_amount" => (string) ($transactType['money'] * 100),    //总金额(单位分)
                        "spbill_create_ip" => $request->getClientIp(),
                        "install_times" => "1",
                        "return_url" => $return_url . "&logID=" . $logID->id,   // 支付回显
                        "notify_url" => route('aggregatePayment_certificateLv1', $token), // 支付结果通知
                        "notify_type" => "HS",
                        "result_type" => "0",
                        'order_channel' => "101"
                    ),
                );

                $msg_id = $payHelper->GenerateMsgId($token);
                $icbc = IcbcService::make($token);
                $formParams = $icbc->buildFormParams($requestData, $msg_id, 'api_payment');

                return $this->success($formParams, true);
            } else {
                $payment = WechatPay::initialize($token);
                //生成订单号
                $out_trade_no = $payHelper->GenerateOrderNumber('BZYJ');
                $body = sprintf('%s_%s_普通办证押金_%s', '海南省', $token, $out_trade_no);
                $order = [
                    'body' => $body,
                    'out_trade_no' => $out_trade_no,
                    'total_fee' => $transactType['money'] * 100,
                    'trade_type' => 'JSAPI',
                    'openid' => $openid,
                    'notify_url' => route('wxPay_certificateLv1', $token)
                ];

                $result = $payment->order->unify($order);

                if (Arr::get($result, 'result_code') == 'SUCCESS') {

                    $log = Arr::only($order + $result,
                        ['openid', 'out_trade_no', 'total_fee', 'trade_type', 'prepay_id', 'return_code']
                    );
                    //下单成功写入日志
                    $payLogService->placeOrder($token, 'Certificate', $log);
                    //申请数据 存Log
                    $certificateLog['status'] = 0;
                    $certificateLog['is_pay'] = 1;
                    $certificateLog['order_id'] = $out_trade_no;
                    $certificateLog['prepay_id'] = $result['prepay_id'];
                    $logID = CertificateLog::create($certificateLog);
                    //生成回调订单
                    $callbackOrder = [
                        'token' => $token,
                        'price' => $transactType['money'],
                        'origin_price' => $transactType['money'],
                        'cash_fee' => 0,
                        'openid' => $openid,
                        'pay_status' => 0,
                        'pay_type' => 0,
                        'prepay_id' => $result['prepay_id'],
                        'order_id' => $out_trade_no,
                    ];
                    CertificateOrders::create($callbackOrder);
                    $jssdk = $payment->jssdk;
                    $sdkConfig = $jssdk->sdkConfig($logID['prepay_id']);
                    $data = ['is_pay' => 1, 'sdkConfig' => $sdkConfig, 'logID' => $logID['id']];
                    return $this->success($data, true);

                }
                return $this->failed(Arr::get($result, 'return_msg'), 400);
            }
        } else {
            $certificateLog['status'] = 1;
            $certificateLog['is_pay'] = 0;
            //免费，免审,直接生成读者
            if ($transactType['is_check'] != 1) {
                $newReader['rdpasswd'] = $rdpasswd;
                $response = $openlibService->addreader($newReader);
                if ($response['success'] == true) {
                    $certificateLog['rdid'] = Arr::get($response, 'messagelist.1.rdid') ?: $newReader['rdid'];
                } else {
                    return $this->failed(Arr::get($response, 'messagelist.0.message'), 400);
                }
            }
            //免费，需审
            $logID = CertificateLog::create($certificateLog);
            $data = ['is_pay' => 0, 'logID' => $logID['id']];
            return $this->success($data, true);
        }
    }

}
