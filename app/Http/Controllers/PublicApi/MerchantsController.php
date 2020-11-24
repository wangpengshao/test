<?php

namespace App\Http\Controllers\PublicApi;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Merchants\SparkPayer;
use App\Models\Merchants\SparkPayerLog;
use App\Models\Wechat\WechatPay;
use App\Pages\RedisLock\Lock;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

/**
 * 星火商户号 内部 运营接口
 * Class MerchantsController
 * @package App\Http\Controllers\PublicApi
 */
class MerchantsController extends Controller
{
    use ApiResponse;
    //  公众号:文化服务消息中心  商户号:星火 payerPocketMoney 代理提现公众号
    private $spa_token = '542ef3edc367';

    /**
     * 企业付款到零钱
     * @param \Illuminate\Http\Request $request
     * @return mixed
     * @throws \Exception
     */
    public function payerPocketMoney(Request $request)
    {
        $success = [
            'message' => '',
            'err_code' => '',
            'err_code_des' => '',
        ];
        if (!$request->filled('payer_token', 'openid', 'sign', 'time', 'amount', 'desc')) {
            $success['message'] = 'missing required parameters';
            return $this->success($success, false);
        }
        $payer_token = $request->input('payer_token');
        $sign = $request->input('sign');
        $openid = $request->input('openid');
        $time = $request->input('time');
        $amount = $request->input('amount'); //金额 单位:分
        $desc = $request->input('desc'); //备注
        $check_name = $request->input('check_name', 'NO_CHECK');
        $re_user_name = $request->input('re_user_name', '');
        $currentTime = time();

        // 时间戳格式 校验: 数字 10位
//        if (!is_numeric($time) || strlen($time) !== 10 || $currentTime - $time > 120 || $currentTime - $time < -120) {
        if (!is_numeric($time) || strlen($time) !== 10) {
            $success['message'] = 'parameter (t) is invalid';
            return $this->success($success, false);
        }

        // 金额格式 校验: 数字  30 < 值 < 20000
        if (!is_numeric($amount) || $amount < 30 || $amount > 20000 || strpos($amount, '.') !== false) {
            $success['message'] = 'parameter (a) is invalid';
            return $this->success($success, false);
        }

        // 查询支付者帐号 配置项
        $payerConf = SparkPayer::where('pay_token', $payer_token)->first();
        if (empty($payerConf) || $payerConf->status !== 1 || $payerConf->expiration_at < date('Y-m-d H:i:s', $currentTime)
            || $payerConf->type !== 1) {
            $success['message'] = 'unauthorized access';
            return $this->success($success, false);
        }

        // 校验签名
        $ENCRYPT_STR = config('envCommon.ENCRYPT_STR');
        $check_sign = sprintf('%s_%s_%s_%s_%s', $time, $payer_token, $payerConf->secret, $ENCRYPT_STR, $openid);
        if (md5($check_sign) !== $sign) {
            $success['message'] = 'parameter (s) is invalid';
            return $this->success($success, false);
        }
        // 判断ip白名单            ======================>>>>>>>>> 待处理

        // 判断金额是否足够扣减
        $currentMoney = (int)($payerConf->money * 100);
        if ($currentMoney < $amount) {
            $success['message'] = "isn't enough money";
            return $this->success($success, false);
        }

        // NO_CHECK：不校验真实姓名, FORCE_CHECK：强校验真实姓名
        if ($check_name === 'FORCE_CHECK' && !$request->filled('re_user_name')) {
            $success['message'] = 'lack of parameter (re_user_name)';
            return $this->success($success, false);
        }

        // 请求记录数据
        $logCreate = [
            'pay_token' => $payer_token,
            'amount' => $amount,
            'type' => 1,
            'openid' => $openid,
            'return_code' => '',
            'result_code' => '',
            'payment_no' => '',
            'partner_trade_no' => '',
            'current_money' => $payerConf->money,
            'desc' => $desc,
        ];
        $sparkPayerLog = SparkPayerLog::create($logCreate);
        $payer_id = $payerConf->id;
        $partner_trade_no = $this->createOrderNumber($payer_id);
        // 组装提现数据
        $params = [
            'partner_trade_no' => $partner_trade_no, // 商户订单号，需保持唯一性(只能是字母或者数字，不能包含有符号)
            'openid' => $openid,
            'check_name' => $check_name, //
            're_user_name' => $re_user_name, // 如果 check_name 设置为FORCE_CHECK，则必填用户真实姓名
            'amount' => $amount, // 企业付款金额，单位为分
            'desc' => $desc, // 企业付款操作说明信息。必填
            //            'spbill_create_ip'=>'',  //Ip地址
        ];

        // 进行事务并发控制 余额扣减 start
        $Lock = new Lock();
        $lock_val = 'sparkpayer:u:' . $payer_id;
        $success = $Lock->queueLock(function () use ($payer_id, $params, $sparkPayerLog, $success) {
            // 判断金额是否足够扣减
            $conf = SparkPayer::where('id', $payer_id)->first(['money', 'id']);
            $currentMoney = (int)($conf->money * 100);
            if ($currentMoney < $params['amount']) {
                $success['message'] = "isn't enough money";
                return $success;
            }
            // 余额足够 开始提现
            $response = WechatPay::initialize($this->spa_token)->transfer->toBalance($params);
            // 更新请求记录
            $sparkPayerLog->current_money = $conf->money;
            $sparkPayerLog->response_info = json_encode($response, JSON_UNESCAPED_UNICODE);
            $sparkPayerLog->return_code = $response['return_code'];
            $sparkPayerLog->result_code = $response['result_code'];
            if (isset($response['partner_trade_no'])) {
                $sparkPayerLog->partner_trade_no = $response['partner_trade_no'];
            }
            if (isset($response['payment_no'])) {
                $sparkPayerLog->payment_no = $response['payment_no'];
            }
            $sparkPayerLog->save();

            // 通信状态检查
            if ($response['return_code'] !== 'SUCCESS') {
                $success['message'] = '通信失败,请稍后再试!';
                if (isset($response['err_code'])) {
                    $success['err_code'] = $response['err_code'];
                    $success['err_code_des'] = $this->pocketMoneyErrcode($response['err_code']);
                }
                return $success;
            }
            if ($response['result_code'] !== 'SUCCESS') {
                $success['message'] = '付款失败,请稍后再试!';
                if (isset($response['err_code'])) {
                    $success['err_code'] = $response['err_code'];
                    $success['err_code_des'] = $this->pocketMoneyErrcode($response['err_code']);
                    if (isset($response['err_code_des'])) {
                        $success['err_code_des'] = $response['err_code_des'];
                    }
                }
                return $success;
            }
            // 提现成功 扣掉相应的余额
            $conf->decrement('money', $params['amount'] / 100);

            return $response;

        }, $lock_val, 24);
        // 进行事务并发控制 余额扣减 end

        if (isset($success['message'])) {
            return $this->success($success, false);
        }
        unset($success['mchid'], $success['mch_appid'], $success['nonce_str']);
        return $this->success($success, true);
    }

    /**
     * @param int $id
     * @return string
     */
    protected function createOrderNumber(int $id)
    {
        $random = strtoupper(Str::uuid()->getNodeHex() . Str::random(7));
        return 'PAYER' . Hashids::encode($id) . $random;
    }

    /**
     * @param $code
     * @return mixed|string
     */
    protected function pocketMoneyErrcode($code)
    {
        $infos = [
            "NO_AUTH" => "没有该接口权限",
            "AMOUNT_LIMIT" => "金额超限",
            "PARAM_ERROR" => "参数错误",
            "OPENID_ERROR" => "Openid错误",
            "SEND_FAILED" => "付款错误",
            "NOTENOUGH" => "余额不足",
            "SYSTEMERROR" => "系统繁忙，请稍后再试。",
            "NAME_MISMATCH" => "姓名校验出错",
            "SIGN_ERROR" => "签名错误",
            "XML_ERROR" => "Post内容出错",
            "FATAL_ERROR" => "两次请求参数不一致",
            "FREQ_LIMIT" => "超过频率限制，请稍后再试。",
            "MONEY_LIMIT" => "已经达到今日付款总额上限/已达到付款给此用户额度上限",
            "CA_ERROR" => "商户API证书校验出错",
            "V2_ACCOUNT_SIMPLE_BAN" => "无法给未实名用户付款",
            "PARAM_IS_NOT_UTF8" => "请求参数中包含非utf8编码字符",
            "SENDNUM_LIMIT" => "该用户今日付款次数超过限制,如有需要请进入【微信支付商户平台-产品中心-企业付款到零钱-产品设置】进行修改",
            "RECV_ACCOUNT_NOT_ALLOWED " => "收款账户不在收款账户列表 ",
            "PAY_CHANNEL_NOT_ALLOWED " => "本商户号未配置API发起能力  ",
        ];
        if (isset($infos[$code])) {
            return $infos[$code];
        }
        return '';
    }

}
