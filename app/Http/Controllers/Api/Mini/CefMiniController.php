<?php

namespace App\Http\Controllers\Api\Mini;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Jobs\OssUpPeopleImage;
use App\Models\Mini\CertificateLog;
use App\Models\Mini\CertificateOrders;
use App\Models\Mini\MiniProgram;
use App\Models\Mini\Registration;
use App\Models\Mini\StoreUserInfo;
use App\Models\Mini\MiniPay;
use App\Services\JybDes;
use App\Services\JybService;
use App\Services\OpenlibService;
use App\Services\PayHelper;
use App\Services\PayLogService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * 通用办证小程序接口
 * Class CefMiniController
 * @package App\Http\Controllers\Api\Mini
 */
class CefMiniController extends Controller
{
    use  ApiResponse;
    protected $md5Key = 'KECNl5S7bNf2HHZ';
    protected $token;

    public function __construct(Request $request)
    {
        if (basename($_SERVER['SCRIPT_NAME']) == 'artisan') {
            return null;
        }
        $this->middleware('checkCefMiniToken');
        $this->token = $request->input('token');
    }

    /**
     * 初始化openlib工具类
     * @param $registration
     * @return OpenlibService
     */
    protected function initOpenlib($registration)
    {
        return OpenlibService::make($this->token, [
            'openlib_appid' => $registration['openlib_appid'],
            'openlib_secret' => $registration['openlib_secret'],
            'openlib_url' => $registration['openlib_url'],
            'openlib_opuser' => $registration['openlib_opuser'],
        ]);
    }

    /**
     * 小程序办证 基础配置
     * @return mixed
     */
    public function getConfig()
    {
        $registration = Registration::getCache($this->token);
        $response = $registration->only(['token', 'status', 'start_at', 'end_at', 'mininame']);
        $response['color_list'] = [];
        if ($registration['colors']) {
            foreach ($registration['colors'] as $k => $v) {
                if ($v['key'] && $v['color']) {
                    $response['color_list'][$v['key']] = $v['color'];
                }
            }
        }
        $response['img_list'] = $registration->hasManyImg->pluck('img', 'key')->toArray();
        $response['public_token'] = $registration->public_token;
        $response['card_id'] = $registration->card_id;
        return $this->success($response, true);
    }

    /**
     * 小程序办证 读者类型
     * @return mixed
     */
    public function getType()
    {
        $registration = Registration::getCache($this->token);
        $array = [
            'id', 'token', 'is_pay', 'money', 'title', 'min_age', 'agreement', 'check_repetition',
            'max_age', 'prompt', 'send_code', 'region', 'data', 'img_data', 'agreement_title', 'order', 'password_limit'
        ];
        $types = $registration->hasManyType->map(function ($item, $key) use ($array) {
            return $item->only($array);
        });
        return $this->success($types, true);
    }

    /**
     * 小程序办证 微信用户信息
     * @param Request $request
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function codeGetInfo(Request $request)
    {
        if (!$request->filled('code')) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $registration = Registration::getCache($this->token);
        $app = MiniProgram::initialize($registration['app_id'], $registration['secret']);
        $response = $app->auth->session($request->input('code'));

        if (!empty($response['errcode'])) {
            return $this->setTypeCode($response['errcode'])->message($response['errmsg'], false);
        }
        return $this->success($response, true);
    }

    public function cardBindUid(Request $request)
    {
        if (!$request->filled(['uid', 'card'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $where = [
            'token' => $this->token,
            'uid' => $request->input('uid'),
            'card' => $request->input('card')
        ];
        $exists = DB::table('mini_registration_u')->where($where)->exists();
        if ($exists) {
            return $this->setTypeCode(3000)->message('该设备已绑定了对应读者证，请解绑原来的读者证。', false);
        }
        $where['created_at'] = date('Y-m-d H:i:s');
        $status = DB::table('mini_registration_u')->insert($where);
        if ($status) {
            return $this->message('绑定成功!', true);
        }
        return $this->setTypeCode(3000)->message('系统繁忙，请稍后再试!', false);
    }

    public function cardUnBindUid(Request $request)
    {
        if (!$request->filled(['uid', 'card'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $where = [
            'token' => $this->token,
            'uid' => $request->input('uid'),
            'card' => $request->input('card')
        ];
        $exists = DB::table('mini_registration_u')->where($where)->exists();
        if (!$exists) {
            return $this->setTypeCode(3000)->message('数据不存在!', false);
        }
        $status = DB::table('mini_registration_u')->where($where)->delete();
        if ($status) {
            return $this->message('解绑成功!', true);
        }
        return $this->setTypeCode(3000)->message('系统繁忙，请稍后再试!', false);
    }

    public function uidFindCard(Request $request)
    {
        if (!$request->filled(['uid'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $where = [
            'token' => $this->token,
            'uid' => $request->input('uid'),
        ];
        $first = DB::table('mini_registration_u')->where($where)->first(['card', 'uid', 'created_at']);
        if (empty($first)) {
            return $this->setTypeCode(3000)->message('数据不存在!', false);
        }
        return $this->success($first, true);
    }

    public function save(Request $request)
    {
        if (!$request->filled(['name', 'idCard', 'rdpasswd', 'typeId', 'uid'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $registration = Registration::getCache($this->token);
        $OpenlibService = $this->initOpenlib($registration);

        $type = $registration->hasManyType->firstWhere('id', $request->input('typeId'));
        if (empty($type)) {
            return $this->setTypeCode(1004)->message('parameter is invalid', false);
        }
        $requiredForm = array_merge($type['data'], $type['imgData']);
        if (!$request->filled($requiredForm)) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        [
            'name' => $name,
            'idCard' => $idCard,
            'rdpasswd' => $rdpasswd,
        ] = $request->input();
        $params = $request->only($type['data']);
        if ($request->filled('baseimg64')) {
            $params['baseimg64'] = $request->input('baseimg64');
        }
        // 广图定制
        if ($this->token == 'MINI9790113fc19b') {
            if ($request->filled('rdremark')) {
                $params['data5'] = $request->input('rdremark');
            }
        }
        // 传入是否为集群的参数
        $cluster = $registration->is_cluster;
        $searchReader = $OpenlibService->searchreader(null, $idCard, $cluster);
        if ($searchReader['success'] == true || Arr::get($searchReader, 'messagelist.0.code') == 'R00130') {
            return $this->setTypeCode(3000)->message('抱歉，您已经办理读者证，请勿重复办理!', false);
        }
//        //提交数据(基础)
        $basic = [
            'rdname' => $name,
            'rdpasswd' => $rdpasswd,
            'rdcertify' => $idCard,
            'operator' => $registration['openlib_opuser'],
            'rdlib' => $registration['libcode'],
            'rdtype' => $type['value'],
            'rdcfstate' => 1,
            'rdid' => $idCard,
        ];
        //提交数据(完整)
        $newReader = $params + $basic;
        $response = $OpenlibService->addreader($newReader);
        if ($response['success'] == true) {
            $where = [
                'token' => $this->token,
                'uid' => $request->input('uid'),
                'card' => $idCard
            ];
            $exists = DB::table('mini_registration_u')->where($where)->exists();
            if (!$exists) {
                $where['created_at'] = date('Y-m-d H:i:s');
                DB::table('mini_registration_u')->insert($where);
            }
            return $this->message('办证成功!', true);
        }
        return $this->setTypeCode(1003)->message(Arr::get($response, 'messagelist.0.message'), false);
    }

    //增加证号生成方式（身份证、随机）、去重判断
    public function save2(Request $request)
    {
        if (!$request->filled(['name', 'idCard', 'rdpasswd', 'typeId', 'uid'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $registration = Registration::getCache($this->token);
        $OpenlibService = $this->initOpenlib($registration);

        $type = $registration->hasManyType->firstWhere('id', $request->input('typeId'));
        if (empty($type)) {
            return $this->setTypeCode(1004)->message('parameter is invalid', false);
        }
        $requiredForm = array_merge($type['data'], $type['imgData']);
        if (!$request->filled($requiredForm)) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        [
            'name' => $name,
            'idCard' => $idCard,
            'rdpasswd' => $rdpasswd,
        ] = $request->input();
        $params = $request->only($type['data']);
        if ($request->filled('baseimg64')) {
            $params['baseimg64'] = $request->input('baseimg64');
        }

        if ($type->check_repetition == 1) {
            $response = $OpenlibService->searchreaderlist('rdcertify', $idCard);
            if ($response['success']) {
                foreach ($response['pagedata'] as $valuue) {
                    if ($valuue['rdtype'] == $type->value) {
                        return $this->setTypeCode(3000)->message('抱歉，您已经办理过读者证，请勿重复办理!', false);
                    }
                }
            }
        }

        //提交数据(基础)
        $basic = [
            'rdname' => $name,
            'rdpasswd' => $rdpasswd,
            'rdcertify' => $idCard,
            'operator' => $registration['openlib_opuser'],
            'rdlib' => $registration['libcode'],
            'rdtype' => $type['value'],
            'rdcfstate' => 1,
        ];
        if ($type->rdid_type == 1) {
            $basic['rdid'] = $idCard;
        }

        //提交数据(完整)
        $newReader = $params + $basic;
        $response = $OpenlibService->addreader($newReader);
        if ($response['success'] == true) {
            if ($type->rdid_type == 1) {
                $card = $idCard;
            } else {
                $card = $response['messagelist'][1]['rdid'];
            }
            $where = [

                'token' => $this->token,
                'uid' => $request->input('uid'),
            ];
            $exists = DB::table('mini_registration_u')->where($where)->exists();
            if (!$exists) {
                $where['card'] = $card;
                $where['created_at'] = date('Y-m-d H:i:s');
                DB::table('mini_registration_u')->insert($where);
            }
            return $this->success(['message' => '办证成功', 'card' => $card], true);
        }
        return $this->setTypeCode(1003)->message(Arr::get($response, 'messagelist.0.message'), false);
    }

    /**
     * 获取读者信息 (需要密码)
     * @param Request $request
     * @return mixed
     */
    public function readerInfo(Request $request)
    {
        if (!$request->filled(['card', 'password'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $rdid = $request->input('card');
        $password = $request->input('password');
        $registration = Registration::getCache($this->token);
        $OpenlibService = $this->initOpenlib($registration);
        // 传入是否为集群的参数
        $cluster = $registration->is_cluster;
        // 集群认证方式
        if ($cluster == 1) {
            $searchReader = $OpenlibService->searchreaderlist('rdid', $rdid, 1);
            if ($searchReader['success'] === false) {
                return $this->setTypeCode(3000)->message(Arr::get($searchReader, 'messagelist.0.message'), false);
            }
            $pageData = $searchReader['pagedata'];
            $find = [];
            foreach ($pageData as $k => $v) {
                $readerPassword = $v['rdpasswd'];
                $samplePassword = $password;
                //判断查询的读者的密码是否进行 md5加密
                if (preg_match("/^([a-fA-F0-9]{32})$/", $readerPassword)) {
                    $samplePassword = md5($samplePassword);
                }
                //判断密码是否正确
                if ($readerPassword == $samplePassword) {
                    $find = $v;
                    break;
                }
            }
            if (empty($find)) {
                return $this->message('证号或密码不正确,请重新输入', false);
            }
            $searchReader = $find;
        } else {
            //非集群普通认证方式
            $params = [
                'rdid' => $rdid,
                'password' => $password
            ];
            $response = $OpenlibService->confirmreader($rdid, $password, $params);
            if ($response['success'] == false) {
                return $this->setTypeCode(3000)->message(Arr::get($response, 'messagelist.0.message'), false);
            }
            $searchReader = $OpenlibService->searchreader($rdid);
            if ($searchReader['success'] == false) {
                return $this->setTypeCode(3000)->message(Arr::get($searchReader, 'messagelist.0.message'), false);
            }
        }
        unset($searchReader['rdpasswd']);
        if ($searchReader['rdcfstate'] != 1 || $searchReader['rdenddate'] < date('Y-m-d')) {
            return $this->setTypeCode(3000)->message('抱歉,该证不是有效状态,无法进行绑定!', false);
        }

        return $this->success($searchReader, true);
    }

    /**
     * 获取读者信息 （免密） md5(rdid+time+'TCUWEI2018')
     * @param Request $request
     * @return mixed
     */
    public function readerInformation(Request $request)
    {
        if (!$request->filled(['card', 'sign'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $sign = $request->input('sign');
        $time = date('Ymd');
        $card = $request->input('card');
        $key = config('envCommon.ENCRYPT_STR');
        if (md5($card . $time . $key) != $sign) {
            return $this->setTypeCode(1003)->message('The sign is invalid', false);
        }
        $registration = Registration::getCache($this->token);
        $OpenlibService = $this->initOpenlib($registration);

        $response = $OpenlibService->searchreader($card);
        if ($response['success'] == false) {
            return $this->setTypeCode(3000)->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        return $this->success($response, true);
    }

    /**
     * 获取读者信息 （需要密码)
     * @param Request $request
     * @return mixed
     */
    public function readerInformationLv2(Request $request)
    {
        if (!$request->filled(['card', 'password'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $rdid = $request->input('card');
        $password = $request->input('password');
        $registration = Registration::getCache($this->token);
        $OpenlibService = $this->initOpenlib($registration);

        $response = $OpenlibService->confirmreader($rdid, $password);
        if ($response['success'] == false) {
            return $this->setTypeCode(3000)->message(Arr::get($response, 'messagelist.0.message'), false);
        }

        $response = $OpenlibService->searchreader($rdid);

        if ($response['success'] == false) {
            return $this->setTypeCode(3000)->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        return $this->success($response, true);
    }

    /**
     * 2020-01-06 由小程序处理 临时证 转 正式证
     * @param Request $request
     * @return mixed
     */
    public function readerTypeChange(Request $request)
    {
        if (!$request->filled(['card', 'newrdtype', 'sign'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $newrdtype = $request->input('newrdtype');
        $sign = $request->input('sign');
        $rdremark = $request->input('rdremark', '');

        $time = date('Ymd');
        $card = $request->input('card');
        $key = config('envCommon.ENCRYPT_STR');
        if (md5($card . $time . $key) != $sign) {
            return $this->setTypeCode(1003)->message('The sign is invalid', false);
        }
        $registration = Registration::getCache($this->token);
        $OpenlibService = $this->initOpenlib($registration);
        //变更类型前 验证
        $response = $OpenlibService->rdtypechangebefore($card, $newrdtype);
        if ($response['success'] == false) {
            return $this->setTypeCode(3000)->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        //开始变更类型
        $response = $OpenlibService->rdtypechange($card, $newrdtype);
        if ($response['success'] == false) {
            return $this->setTypeCode(3000)->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        //变更成功之后进行证 延期
        // 广图定制
        $otherParams = [];
        if ($this->token == 'MINI9790113fc19b') {
            $otherParams = [
                'data5' => $rdremark
            ];
        }
        $manageResponse = $OpenlibService->cardmanage($card, 8, $rdremark, $otherParams);
        if ($manageResponse['success'] == false) {
            return $this->setTypeCode(3000)->message(Arr::get($manageResponse, 'messagelist.0.message'), false);
        }
        return $this->success($response, true);
    }

    //提交办证、返回支付参数
    public function saveOrPay(Request $request, PayHelper $payHelper, PayLogService $payLogService)
    {
        if (!$request->filled(['name', 'idCard', 'rdpasswd', 'typeId', 'uid', 'openid'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $registration = Registration::getCache($this->token);
        $OpenlibService = $this->initOpenlib($registration);

        $type = $registration->hasManyType->firstWhere('id', $request->input('typeId'));
        if (empty($type)) {
            return $this->setTypeCode(1004)->message('parameter is invalid', false);
        }
        $requiredForm = array_merge($type['data'], $type['imgData']);
        if (!$request->filled($requiredForm)) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        [
            'name' => $name,
            'idCard' => $idCard,
            'rdpasswd' => $rdpasswd,
        ] = $request->input();
        $params = $request->only($type['data']);
        if ($type->check_repetition == 1) {
            $response = $OpenlibService->searchreaderlist('rdcertify', $idCard);
            if ($response['success']) {
                foreach ($response['pagedata'] as $valuue) {
                    if ($valuue['rdtype'] == $type->value) {
                        return $this->setTypeCode(3000)->message('抱歉，您已经办理过读者证，请勿重复办理!', false);
                    }
                }
            }
        }

        //提交数据(基础)
        $basic = [
            'rdname' => $name,
            'rdpasswd' => $rdpasswd,
            'rdcertify' => $idCard,
            'operator' => $registration['openlib_opuser'],
            'rdlib' => $registration['libcode'],
            'rdtype' => $type['value'],
            'rdcfstate' => 1,
        ];
        if ($type['rdid_type'] == 1) {
            $basic['rdid'] = $idCard;
        }

        //提交数据(完整)
        $newReader = $params + $basic;
        //组装LOG数据
        $certificateLog = $basic;
        $certificateLog['token'] = $registration['public_token'];
        $certificateLog['mini_token'] = $this->token;
        $certificateLog['openid'] = $request->input('openid');
        $certificateLog['data'] = $params;
        $certificateLog['check_s'] = 0;

        //支付押金
        if ($type['is_pay'] == 1 && $type['money'] > 0) {

            if ($request->filled('baseimg64')) {
                $fileName = 'peopleImage/' . $this->token . '/' . date('Ymd') . '/' . uniqid() . '.jpeg';
                OssUpPeopleImage::dispatchNow($this->token, $request->input('baseimg64'), $fileName);
                $certificateLog['imgData'] = ['personal_img' => $fileName];
            }

            $payment = MiniPay::initialize($this->token);
            //生成订单号
            $out_trade_no = $payHelper->GenerateOrderNumber('MiniYJ');
            $body = sprintf('%s_小程序办证押金', $registration['mininame']);

            $order = [
                'body' => $body,
                'out_trade_no' => $out_trade_no,
                'total_fee' => $type['money'] * 100,
                'trade_type' => 'JSAPI',
                'openid' => $certificateLog['openid'],
                'notify_url' => route('miniPay_certificateLv1', $this->token)
            ];
            $result = $payment->order->unify($order);

            if (Arr::get($result, 'result_code') == 'SUCCESS') {

                $log = Arr::only($order + $result,
                    ['openid', 'out_trade_no', 'total_fee', 'trade_type', 'prepay_id', 'return_code']
                );
                //下单成功写入日志
                $payLogService->placeOrder($this->token, 'MiniCertificate', $log);
                //申请数据 存Log
                $certificateLog['status'] = 0;
                $certificateLog['is_pay'] = 1;
                $certificateLog['order_id'] = $out_trade_no;
                $certificateLog['prepay_id'] = $result['prepay_id'];
                $certificateLog['rdpasswd'] = encrypt($certificateLog['rdpasswd']);
                $logID = CertificateLog::create($certificateLog);
                //生成回调订单
                $callbackOrder = [
                    'token' => $certificateLog['token'],
                    'mini_token' => $this->token,
                    'price' => $type['money'],
                    'origin_price' => $type['money'],
                    'cash_fee' => 0,
                    'openid' => $certificateLog['openid'],
                    'pay_status' => 0,
                    'pay_type' => 0,
                    'prepay_id' => $result['prepay_id'],
                    'order_id' => $out_trade_no,
                ];
                CertificateOrders::create($callbackOrder);
                $config = $payment->jssdk->bridgeConfig($result['prepay_id'], false);
                $data = ['is_pay' => 1, 'config' => $config, 'logID' => $logID['id']];
                return $this->success($data, true);
            }
            return $this->message(Arr::get($result, 'return_msg'), false);

        } else {
            if ($request->filled('baseimg64')) {
                $newReader['baseimg64'] = $request->input('baseimg64');
            }
            //免押
            $response = $OpenlibService->addreader($newReader);
            if ($response['success'] == true) {
                if ($type->rdid_type == 1) {
                    $card = $idCard;
                } else {
                    $card = $response['messagelist'][1]['rdid'];
                }
                $where = [
                    'token' => $this->token,
                    'uid' => $request->input('uid'),
                ];
                $exists = DB::table('mini_registration_u')->where($where)->exists();
                if (!$exists) {
                    $where['card'] = $card;
                    $where['created_at'] = date('Y-m-d H:i:s');
                    DB::table('mini_registration_u')->insert($where);
                }

                //发送模板消息
                if ($registration['template_bz'] && $request->input('formId')) {
                    $miniApp = MiniProgram::initialize($registration['app_id'], $registration['secret']);
                    $res = $miniApp->template_message->send([
                        'touser' => $request->input('openid'),
                        'template_id' => $registration['template_bz'],
                        'page' => 'pages/index/pageindex',
                        'form_id' => $request->input('formId'),
                        'data' => [
                            'keyword1' => $newReader['rdname'],
                            'keyword2' => hidenIdCard($newReader['rdcertify']),
                            'keyword3' => $card,
                            'keyword4' => '0.00元',
                        ],
                    ]);
                }

                return $this->success(['message' => '办证成功', 'card' => $card], true);
            }
            return $this->setTypeCode(1003)->message(Arr::get($response, 'messagelist.0.message'), false);
        }

    }

    public function checkRdid(Request $request)
    {
        if (!$request->filled('card')) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $type = $request->input('type', 1);
        $card = $request->input('card');
        $registration = Registration::getCache($this->token);
        $OpenlibService = $this->initOpenlib($registration);
        // 传入是否为集群的参数
        $cluster = $registration->is_cluster;
        if ($type == 1) {
            $response = $OpenlibService->searchreader(null, $card, $cluster);
        } else {
            $response = $OpenlibService->searchreader($card, null, $cluster);
        }
        if ($response['success'] == false) {
            return $this->setTypeCode(3000)->message('未找到证号为:' . $card . '信息', true);
        }
        return $this->setTypeCode(1003)->message('已存在证号', false);
    }

    public function checkRepetition(Request $request)
    {
        if (!$request->filled(['card', 'idCard', 'typeId'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $card = $request->input('card');
        $idCard = $request->input('idCard');
        $typeId = $request->input('typeId');

        $registration = Registration::getCache($this->token);
        $type = $registration->hasManyType->firstWhere('id', $typeId);

        if (empty($type)) {
            return $this->setTypeCode(1004)->message('parameter is invalid', false);
        }

        if ($type->check_repetition != 1) {
            return $this->setTypeCode(3000)->message('未设置查重', true);
        }

        $registration = Registration::getCache($this->token);
        $OpenlibService = $this->initOpenlib($registration);

        if ($type->rdid_type == 1) { //读者证号等于身份证号
            $response = $OpenlibService->searchreaderlist('rdid', $idCard);
        } else {
            $response = $OpenlibService->searchreaderlist('rdcertify', $idCard);
        }

        if ($response['success'] == false) {
            return $this->setTypeCode(3000)->message('未找到相匹配的信息', true);
        }

        foreach ($response['pagedata'] as $valuue) {
            if ($valuue['rdtype'] == $type->value) {
                return $this->setTypeCode(3000)->message('当前读者类型已办理过读者证号', false);
            }
        }
        return $this->setTypeCode(3000)->message('未找到相匹配的信息', true);

    }

    public function qrCode(Request $request, JybService $jybService)
    {
        if (!$request->filled('uid')) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $where = [
            'token' => $this->token,
            'uid' => $request->input('uid'),
//            'card' => $request->input('card')
        ];
        $first = DB::table('mini_registration_u')->where($where)->first();
        if (empty($first)) {
            return $this->setTypeCode(3000)->message('尚未绑定读者证!', false);
        }
        $registration = Registration::getCache($this->token);
        if ($registration['qr_type'] === 0) {
            return $this->setTypeCode(3000)->message('读者二维码功能未开启!', false);
        }
        $http = new Client();
        $rdid = $first->card;
//        dd($rdid);
        if ($registration['qr_type'] === 1) {
            $time = date('Ymd');
            $ticket = md5($rdid . $time . $registration['glc']);
            $url = $registration['opacurl'] . 'reader/getReaderQrcode?';
            $params = http_build_query([
                'rdid' => $rdid,
                'time' => $time,
                'ticket' => $ticket
            ]);
            $response = $http->get($url . $params);
            $response = json_decode((string)$response->getBody(), true);
            if ($response['flag'] == 1) {
                return $this->success(['qrcode' => $response['qrcode']], true);
            }
        }

        if ($registration['qr_type'] === 2) {
            $response = $jybService->getElectronicCard($registration, $rdid);

            if ($response['code'] == 200) {
                return $this->success(['qrcode' => $response['uuid']], true);
            }
        }
        return $this->setTypeCode(3000)->message('服务器繁忙，请稍后再试!', false);

    }

    public function getAccessToken(Request $request)
    {
        $cacheKey = sprintf(config('cacheKey.miniAccessToken'), $this->token);
        $data = Cache::get($cacheKey);
        $refresh = $request->input('refresh');
        if ($refresh == 1) {
            $data = null;
        }
        if (empty($data)) {
            $registration = Registration::getCache($this->token);
            $app = MiniProgram::initialize($registration['app_id'], $registration['secret']);
            $response = $app->auth->getAccessToken()->getToken(true);
            $time = now()->addSeconds(5000);
            $response['expires_in'] = $time->toDateTimeString();
            Cache::put($cacheKey, $response, $time);
            $data = $response;
        }
        return $this->success($data, true);
    }

    // 获取微信用户信息
    public function storeFansInfo(Request $request)
    {
        if (!$request->filled(['token', 'openid'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $fansInfo = array(
            'token' => $request->input('token'),
            'openid' => $request->input('openid'),
            'nickName' => $request->input('nickName', ''),
            'avatarUrl' => $request->input('avatarUrl', ''),
            'province' => $request->input('province', ''),
            'city' => $request->input('city', ''),
            'gender' => $request->input('gender', ''),
            'language' => $request->input('language', ''),
            'country' => $request->input('country', '')
        );
        $exists = StoreUserInfo::where('openid', $fansInfo['openid'])->where('token', $fansInfo['token'])->exists();
        if (!$exists) {
            //若用户不存在，则添加数据
            StoreUserInfo::insert($fansInfo);
            return $this->setTypeCode(200)->message('用户信息添加成功', true);
        }
        //若用户存在，进行更新操作
        StoreUserInfo::where('openid', $fansInfo['openid'])->where('openid', $fansInfo['openid'])->update($fansInfo);
        return $this->setTypeCode(200)->message('用户信息更新成功', true);
    }

    /**
     * @param Request $request
     * @param JybDes $des
     * @return mixed
     * @author Jay 19-08-14
     * @description 自助机刷脸字段加密——内部Des加密
     */
    public function encryptField(Request $request, JybDes $des)
    {
        $salt = 'B3389BF3A96F50C3F3';
        $postData = $request->input();
        if (!$request->filled(['rdid', 'glc', 'checkKey', 'returnUrl', 'opacUrl', 'key'])) {
            return $this->setTypeCode(1003)->message('lack of parameter', false);
        }
        $resData = [];
        $resData['glc'] = $libcode = $des->encrypt($postData['glc'], $salt);
        $resData['checkKey'] = $checkKey = $des->encrypt($postData['checkKey'], $salt);
        $glc = substr($postData['glc'] . '00000000', 0, 8);
        $glc .= $glc;
        $resData['rdid'] = $des->encrypt($postData['rdid'], $glc);
        $resData['returnUrl'] = $postData['returnUrl'];
        $resData['opacUrl'] = $postData['opacUrl'];
        $resData['key'] = md5($postData['key']);

        return $this->success($resData, true);
    }

    public function CheckResult(Request $request)
    {
        $id = $request->route('id');
        $certificateLog = CertificateLog::where('id', $id)->first(['openid', 'rdid', 'status']);
        if (!$certificateLog) {
            return $this->failed('非法访问', 400);
        }
        $response = $certificateLog->toArray();
        return $this->success($response, true);
    }
}
