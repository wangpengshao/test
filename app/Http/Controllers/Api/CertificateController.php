<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Jobs\OssUpPeopleImage;
use App\Models\IdCard\MobileRegion;
use App\Models\IdCard\Region;
use App\Models\Wechat\Certificate;
use App\Models\Wechat\CertificateLog;
use App\Models\Wechat\CertificateOrders;
use App\Models\Wechat\Reader;
use App\Models\Wechat\TransactType;
use App\Models\Wechat\WechatPay;
use App\Models\Wxuser;
use App\Services\FaceIDService;
use App\Services\FansEvent;
use App\Services\IcbcService;
use App\Services\OpenlibService;
use App\Services\PayHelper;
use App\Services\PayLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    use ApiResponse;

    //******************实名办证-START***********************//
    public function lv2config(Request $request)
    {
        $token = $request->input('token');
        $certificate = Certificate::where(['token' => $token, 'status' => 1, 'type' => 1])
            ->first(['options', 'data', 'agreement', 'agreementTitle', 'region', 'phone_region', 'sendCode']);

        if (empty($certificate)) {
            return $this->failed('实名认证办证功能尚未开启!', 400);
        }

        if (empty($certificate['options'])) {
            return $this->failed('读者类型尚未配置!', 400);
        }
        $agreement = ['info' => $certificate['agreement'], 'title' => $certificate['agreementTitle']];

        $readerConfig = Arr::only(config('addReaderOp'), $certificate['data']);

        $transactType = TransactType::whereIn('id', $certificate['options'])
            ->get(['is_pay', 'money', 'title', 'min_age', 'max_age', 'id', 'prompt', 'password_limit']);

        $need_check = (count($certificate['phone_region']) > 0) ? 1 : 0;

        $data = [
            'attribute' => $readerConfig,
            'type' => $transactType,
            'agreement' => $agreement,
            'phoneOptions' => [
                'sendCode' => $certificate['sendCode'],
                'need_check' => $need_check
            ],
            'cardOptions' => $certificate['region']
        ];
        return $this->success($data, true);
    }

    public function lv2save(Request $request, PayLogService $payLogService, PayHelper $payHelper)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;

        $certificateWhere = ['token' => $token, 'status' => 1, 'type' => 1];
        $certificate = Certificate::where($certificateWhere)->first(['options', 'data', 'rdid_type']);

        if (empty($certificate)) {
            return $this->failed('实名认证办证功能尚未开启!', 400);
        }

        $requiredForm = array_merge($certificate['data'], ['name', 'idCard', 'typeId', 'rdpasswd']);
        if ($request->filled($requiredForm) === false) {
            return $this->failed('缺少必填信息!', 400);
        };

        [
            'name' => $name,
            'idCard' => $idCard,
            'typeId' => $typeId,
            'rdpasswd' => $rdpasswd,
        ] = $request->input();

        $params = $request->only($certificate['data']);

        if (!in_array($typeId, $certificate['options'])) {
            return $this->failed('该读者类型不存在!', 400);
        }
        $openlibService = OpenlibService::make($token);

        $peopleInfo = DB::table('w_people_idcard')->where(['name' => $name, 'idCard' => $idCard])->first();
        if ($peopleInfo) {
            $transactType = TransactType::find($typeId);
            $wxuser = Wxuser::getCache($token);
            //提交数据(基础)
            $basic = [
                'rdname' => $name,
                'rdpasswd' => encrypt($rdpasswd),
                'rdcertify' => $idCard,
                'operator' => $wxuser['openlib_opuser'],
                'rdlib' => $wxuser['libcode'],
                'rdtype' => $transactType['value'],
                'rdcfstate' => 1
            ];

            if ($certificate['rdid_type'] == 1) {
                $basic['rdid'] = $idCard;
            }
            //提交数据(完整)
            $newReader = $params + $basic;

            //组装LOG数据
            $certificateLog = $basic;
            $certificateLog['token'] = $token;
            $certificateLog['openid'] = $openid;
            $certificateLog['type'] = 1;
            $certificateLog['data'] = $params;

            //存在读者?
            $searchReader = $openlibService->searchreader(null, $idCard);
            if ($searchReader['success'] == true || Arr::get($searchReader, 'messagelist.0.code') == 'R00130') {
                return $this->message('抱歉,您已经办理证了,无法重复办理!!', false);
            }
            //缴费?
            if ($transactType['is_pay'] == 1 && $transactType['money'] > 0) {
                $payment = WechatPay::initialize($token);
                //生成订单号
                $out_trade_no = $payHelper->GenerateOrderNumber('BZYJ');

                $body = sprintf('%s_%s_办证押金_%s', $wxuser['wxname'], $token, $out_trade_no);
                $order = [
                    'body' => $body,
                    'out_trade_no' => $out_trade_no,
                    'total_fee' => $transactType['money'] * 100,
                    'trade_type' => 'JSAPI',
                    'openid' => $openid,
                    'notify_url' => route('wxPay_certificateLv2', $token)
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
                return $this->message(Arr::get($result, 'return_msg'), false);

            } else {
                //免费,直接生成
                $newReader['rdpasswd'] = $rdpasswd;
                //默认将图片上传至业务系统
                if ($peopleInfo->personal_img) {
                    $storage = Storage::disk('oss');
                    $existsFile = $storage->exists($peopleInfo->personal_img);
                    if ($existsFile) {
                        $newReader['baseimg64'] = base64_encode($storage->get($peopleInfo->personal_img));  //base64数据
                    }
                }
                $response = $openlibService->addreader($newReader);

                if ($response['success'] == true) {
                    //存LOG
                    $certificateLog['status'] = 1;
                    $certificateLog['is_pay'] = 0;
                    $certificateLog['rdid'] = Arr::get($response, 'messagelist.1.rdid', $newReader['rdid']);
                    $logID = CertificateLog::create($certificateLog);
                    $data = ['is_pay' => 0, 'logID' => $logID['id']];
                    return $this->success($data, true);
                }
                return $this->message(Arr::get($response, 'messagelist.0.message'), false);
            }

        }
        //实名验证没通过
        return $this->message('实名验证没通过', false);
    }

    public function lv2CheckResult(Request $request)
    {
        $id = $request->route('id');
        $certificateLog = CertificateLog::where('id', $id)->first(['openid', 'rdid', 'status']);
        if (!$certificateLog || $request->user()->openid != $certificateLog['openid']) {
            return $this->failed('非法访问', 400);
        }
        $response = $certificateLog->toArray();
        //关联事件统一处理  ====>>  集卡特殊处理
        $eventService = new FansEvent($request->user()->token, $request->user()->openid);
        $eventData = $eventService->check('certificateCard');
        if ($eventData) {
            $response = array_merge($response, $eventData);
        }
        return $this->success($response, true);
    }
    //******************实名办证-END***********************//

    //******************普通办证-START***********************//
    public function lv1config(Request $request)
    {
        $token = $request->input('token');
        $certificate = Certificate::where(['token' => $token, 'status' => 1, 'type' => 0])
            ->first(['options', 'data', 'agreement', 'agreementTitle', 'region', 'phone_region', 'sendCode', 'imgData']);

        if (empty($certificate)) {
            return $this->failed('普通办证功能尚未开启!', 400);
        }

        if (empty($certificate['options'])) {
            return $this->failed('读者类型尚未配置!', 400);
        }
        $agreement = ['info' => $certificate['agreement'], 'title' => $certificate['agreementTitle']];

        $readerConfig = Arr::only(config('addReaderOp'), $certificate['data']);
        $imgOptions = Arr::only(config('addReaderImgOp'), $certificate['imgData']);

        $transactType = TransactType::whereIn('id', $certificate['options'])->orderBy('order', 'DESC')
            ->get(['is_pay', 'money', 'title', 'min_age', 'max_age', 'id', 'prompt', 'password_limit']);

        $need_check = (count($certificate['phone_region']) > 0) ? 1 : 0;

        $data = [
            'attribute' => $readerConfig,
            'type' => $transactType,
            'agreement' => $agreement,
            'phoneOptions' => [
                'sendCode' => $certificate['sendCode'],
                'need_check' => $need_check
            ],
            'cardOptions' => $certificate['region'],
            'imgOptions' => $imgOptions
        ];
        return $this->success($data, true);
    }

    public function lv1save(Request $request, PayLogService $payLogService, PayHelper $payHelper)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;

        $certificateWhere = ['token' => $token, 'status' => 1, 'type' => 0];
        $certificate = Certificate::where($certificateWhere)->first(['options', 'data', 'rdid_type', 'imgData']);

        if (empty($certificate)) {
            return $this->failed('办证功能尚未开启!', 400);
        }
        $requiredForm = array_merge($certificate['data'], $certificate['imgData'], ['name', 'idCard', 'typeId', 'rdpasswd']);
        //队列上传图片 people
        if ($request->filled($requiredForm) === false) {
            return $this->failed('缺少必填信息!', 400);
        };

        [
            'name' => $name,
            'idCard' => $idCard,
            'typeId' => $typeId,
            'rdpasswd' => $rdpasswd,
        ] = $request->input();
        $params = $request->only($certificate['data']);
        $imageParams = $request->only($certificate['imgData']);

        if (!in_array($typeId, $certificate['options'])) {
            return $this->failed('该读者类型不存在!', 400);
        }
        $openlibService = OpenlibService::make($token);

        $searchReader = $openlibService->searchreader(null, $idCard);
        if ($searchReader['success'] == true || Arr::get($searchReader, 'messagelist.0.code') == 'R00130') {
            return $this->failed('抱歉,您已经办理证了,无法重复办理!', 400);
        }
        $transactType = TransactType::find($typeId);
        $wxuser = Wxuser::getCache($token);
        //提交数据(基础)
        $basic = [
            'rdname' => $name,
            'rdpasswd' => encrypt($rdpasswd),
            'rdcertify' => $idCard,
            'operator' => $wxuser['openlib_opuser'],
            'rdlib' => $wxuser['libcode'],
            'rdtype' => $transactType['value'],
            'rdcfstate' => 1
        ];
        if ($certificate['rdid_type'] == 1) {
            $basic['rdid'] = $idCard;
        }
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

        if (count($imageParams) > 0) {
            $imageData = [];
            foreach ($imageParams as $k => $v) {
                $fileName = 'peopleImage/' . $token . '/' . date('Ymd') . '/' . uniqid() . '.jpeg';
                //如果是个人照即立即上传，无需入队列
                if ($k == 'personal_img') {
                    OssUpPeopleImage::dispatchNow($token, $v, $fileName);
                } else {
                    OssUpPeopleImage::dispatch($token, $v, $fileName);
                }
                $imageData[$k] = $fileName;
            }
            unset($k, $v);
            $certificateLog['imgData'] = $imageData;
        }

        if ($transactType['is_pay'] == 1 && $transactType['money'] > 0) {
            $payment = WechatPay::initialize($token);
            //生成订单号
            $out_trade_no = $payHelper->GenerateOrderNumber('BZYJ');

            $body = sprintf('%s_%s_普通办证押金_%s', $wxuser['wxname'], $token, $out_trade_no);
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

        } else {
            $certificateLog['is_pay'] = 0;                    //免支付
            $certificateLog['status'] = 4;                    //默认需要审核
            if ($transactType['is_check'] != 1) {             //免费，免审 直接生成读者
                $certificateLog['status'] = 1;
                $newReader['rdpasswd'] = $rdpasswd;
                //是否需要上传图片
                if (count($imageParams) > 0 && Arr::has($imageParams, 'personal_img')) {
                    $newReader['baseimg64'] = $imageParams['personal_img'];  //base64数据
                }

                $response = $openlibService->addreader($newReader);

                if ($response['success'] == true) {
                    $certificateLog['rdid'] = Arr::get($response, 'messagelist.1.rdid');
                    if (empty($certificateLog['rdid']) && isset($newReader['rdid'])) {
                        $certificateLog['rdid'] = $newReader['rdid'];
                    }
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

    //争对工行聚合支付
    public function lv1saveIcbc(Request $request, PayLogService $payLogService, PayHelper $payHelper)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $return_url = urldecode($request->input('return_url'));

        $certificateWhere = ['token' => $token, 'status' => 1, 'type' => 0];
        $certificate = Certificate::where($certificateWhere)->first(['options', 'data', 'rdid_type', 'imgData']);

        if (empty($certificate)) {
            return $this->failed('办证功能尚未开启!', 400);
        }
        $requiredForm = array_merge($certificate['data'], $certificate['imgData'], ['name', 'idCard', 'typeId', 'rdpasswd','return_url']);
        //队列上传图片 people
        if ($request->filled($requiredForm) === false) {
            return $this->failed('缺少必填信息!', 400);
        };

        [
            'name' => $name,
            'idCard' => $idCard,
            'typeId' => $typeId,
            'rdpasswd' => $rdpasswd,
        ] = $request->input();
        $params = $request->only($certificate['data']);
        $imageParams = $request->only($certificate['imgData']);

        if (!in_array($typeId, $certificate['options'])) {
            return $this->failed('该读者类型不存在!', 400);
        }
        $openlibService = OpenlibService::make($token);

        $searchReader = $openlibService->searchreader(null, $idCard);
        if ($searchReader['success'] == true || Arr::get($searchReader, 'messagelist.0.code') == 'R00130') {
            return $this->failed('抱歉,您已经办理证了,无法重复办理!', 400);
        }
        $transactType = TransactType::find($typeId);
        $wxuser = Wxuser::getCache($token);
        //提交数据(基础)
        $basic = [
            'rdname' => $name,
            'rdpasswd' => encrypt($rdpasswd),
            'rdcertify' => $idCard,
            'operator' => $wxuser['openlib_opuser'],
            'rdlib' => $wxuser['libcode'],
            'rdtype' => $transactType['value'],
            'rdcfstate' => 1
        ];
        if ($certificate['rdid_type'] == 1) {
            $basic['rdid'] = $idCard;
        }
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

        if (count($imageParams) > 0) {
            $imageData = [];
            foreach ($imageParams as $k => $v) {
                $fileName = 'peopleImage/' . $token . '/' . date('Ymd') . '/' . uniqid() . '.jpeg';
                //如果是个人照即立即上传，无需入队列
                if ($k == 'personal_img') {
                    OssUpPeopleImage::dispatchNow($token, $v, $fileName);
                } else {
                    OssUpPeopleImage::dispatch($token, $v, $fileName);
                }
                $imageData[$k] = $fileName;
            }
            unset($k, $v);
            $certificateLog['imgData'] = $imageData;
        }

        if ($transactType['is_pay'] == 1 && $transactType['money'] > 0) {
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
            $certificateLog['is_pay'] = 0;                    //免支付
            $certificateLog['status'] = 4;                    //默认需要审核
            if ($transactType['is_check'] != 1) {             //免费，免审 直接生成读者
                $certificateLog['status'] = 1;
                $newReader['rdpasswd'] = $rdpasswd;
                //是否需要上传图片
                if (count($imageParams) > 0 && Arr::has($imageParams, 'personal_img')) {
                    $newReader['baseimg64'] = $imageParams['personal_img'];  //base64数据
                }

                $response = $openlibService->addreader($newReader);

                if ($response['success'] == true) {
                    $certificateLog['rdid'] = Arr::get($response, 'messagelist.1.rdid');
                    if (empty($certificateLog['rdid']) && isset($newReader['rdid'])) {
                        $certificateLog['rdid'] = $newReader['rdid'];
                    }
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

    public function lv1CheckResult(Request $request)
    {
        $id = $request->route('id');
        $certificateLog = CertificateLog::where('id', $id)->first(['openid', 'rdid', 'status', 'check_s']);
        if (!$certificateLog || $request->user()->openid != $certificateLog['openid']) {
            return $this->failed('非法访问', 400);
        }
        if ($certificateLog['check_s'] == -1) {
            $certificateLog['status'] = 3;
            $certificateLog['message'] = '办证申请已提交,等待工作人员审核(1-2工作日)';
        }
        $response = $certificateLog->toArray();
        //关联事件统一处理  ====>>  集卡特殊处理
        $eventService = new FansEvent($request->user()->token, $request->user()->openid);
        $eventData = $eventService->check('certificateCard');
        if ($eventData) {
            $response = array_merge($response, $eventData);
        }
        return $this->success($response, true);
    }

    //******************普通办证-END***********************//

    public function checkPhoneRegion(Request $request)
    {
        $token = $request->input('token');
        $phone = $request->input('phone');
        $type = $request->input('type');
        if (empty($phone) || empty($type)) {
            return $this->failed('缺少必填参数!', 400);
        }
        switch ($type) {
            case 'lv1':
                $c_type = 0;
                break;
            case 'lv2':
                $c_type = 1;
                break;
            default:
                $c_type = 100;
        }
        if ($c_type == 100) {
            return $this->failed('非法参数!', 400);
        }
        $phone_region = Certificate::where(['token' => $token, 'status' => 1, 'type' => $c_type])->value('phone_region');
        if (count($phone_region) > 0) {
            $area_code = Region::whereIn('code', $phone_region)->pluck('area_code')->toArray();
            $area_code = array_map(function ($item) {
                return (int)$item;
            }, $area_code);
            $beforeSeven = Str::limit($phone, 7, '');
            $areaCode = MobileRegion::where('mobile', $beforeSeven)->value('areaCode');
            if (!in_array($areaCode, $area_code)) {
                return $this->message('验证不通过', false);
            }
        }
        return $this->message('验证通过', true);
    }

    public function checkFaceID(Request $request, FaceIDService $faceIDService)
    {
        if (!$request->filled(['idCard', 'name', 'faceImg'])) {
            return $this->failed('缺少必填信息!', 400);
        };
        [
            'idCard' => $idCard,
            'name' => $name,
            'faceImg' => $faceImg
        ] = $request->input();

        $query = DB::table('w_people_idcard');

        $where = ['name' => $name, 'idCard' => $idCard];
        $exists = $query->where($where)->exists();
        $message = '通过验证!';
        if (!$exists) {
            //身份证 + 姓名 + 脸部照片  => 实名认证
            $check = $faceIDService->check($idCard, $name, $faceImg);
            $message = Arr::get($check, 'message');
            if ($check['success'] == true || Arr::get($check, 'map.ERROR_CODE') == '300007') {
                $exists = 1;
                //实名认证通过队列上传图片
                $fileName = 'peopleImage/common/' . date('Ymd') . '/' . uniqid() . '.jpeg';
                OssUpPeopleImage::dispatchNow('', $faceImg, $fileName);
                $insert = [
                    'name' => $name,
                    'idCard' => $idCard,
                    'personal_img' => $fileName
                ];
                $query->insert($insert);
            }
        }
        if (!$exists) return $this->message($message, false);

        $openlibService = OpenlibService::make($request->user()->token);
        $searchReader = $openlibService->searchreader(null, $idCard);
        if ($searchReader['success'] == true || Arr::get($searchReader, 'messagelist.0.code') == 'R00130') {
            return $this->message('抱歉,您已经办理证了,无法重复办理!', false);
        }
        return $this->message($message, true);

    }

    /**
     * 办证成功之后交互 绑定证号
     * @param Request $request
     * @return mixed
     */
    public function afterSuccessful(Request $request)
    {
        if (!$request->filled('id')) {
            return $this->failed('非法访问', 400);
        }
        $id = $request->input('id');

        $token = $request->user()->token;
        $openid = $request->user()->openid;

        $certificateLog = CertificateLog::where('id', $id)->first(['openid', 'rdid', 'status', 'rdname', 'rdpasswd']);
        if (!$certificateLog || $openid != $certificateLog['openid'] || $certificateLog['status'] != 1) {
            return $this->failed('非法访问', 400);
        }
        $rdid = $certificateLog['rdid'];
        $name = $certificateLog['rdname'];
        $password = $certificateLog['rdpasswd'];

        //检查此openid是否已绑定
        $currentBind = Reader::checkBind($openid, $token)->first();
        if ($currentBind) {
            //判断证号是否相同
            if ($currentBind['rdid'] == $rdid) {
                $currentBind->name = $name;
                $currentBind->save();
                return $this->message('绑定成功', true);
            }
            //不相同进行解绑
            $log = [
                'token' => $token,
                'openid' => $openid,
                'rdid' => $rdid,
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 0
            ];
            DB::table('admin_wechat_reader_log')->insert($log);
            $currentBind->is_bind = 0;
            $currentBind->save();
        }
        //查看该证号是否已被绑
        $rdidCurrentBind = Reader::rdidGetBind($rdid, $token)->first();
        if ($rdidCurrentBind) {
            //进行解绑
            $rdidCurrentBind->is_bind = 0;
            $rdidCurrentBind->save();
        }
        //新增绑定
        $create = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $rdid,
            'password' => $password,
            'is_bind' => 1,
            'name' => $name
        ];
        $status = Reader::create($create);
        if ($status == false) {
            return $this->internalError('服务器繁忙，请稍后再试!');
        }
        $log = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $status['rdid'],
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 1
        ];
        DB::table('admin_wechat_reader_log')->insert($log);
        return $this->message('绑定成功', true);

    }

}
