<?php

namespace App\Services;

use App\Models\Wxuser;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

/**
 * Class SmsService
 *
 * @package App\Services
 */
class SmsService
{
    /**
     * -u : tcsms100100
     * -p : 87trqer45
     */
    /**
     * @var
     */
    protected $token;
    /**
     * @var
     */
    protected $phone;
    /**
     * @var string
     */
    protected $cacheKey;
    /**
     * @var int
     */
    protected $cacheTime;

    /**
     * SmsService constructor.
     *
     * @param     $token
     * @param     $phone
     * @param int $time
     */
    public function __construct($token, $phone, $time = 1)
    {
        $this->token = $token;
        $this->phone = $phone;
        $this->cacheTime = $time;
        $this->cacheKey = 'smsFrequency_' . $phone;
    }

    /**
     * @param $content 内容
     *
     * @return bool
     */
    public function send($content)
    {
        /*********** 频率限制 *************/
        if (Cache::has($this->cacheKey) || !$this->Validate($this->phone)) {
            return false;
        }
        $wxuser = Wxuser::getCache($this->token);
//        $url = str_finish($wxuser['sms_url'], '/') . 'SendSms?';
        $url = $wxuser['sms_url'] . 'SendSms?';
        $username = $wxuser['sms_u'];
        $password = $wxuser['sms_p'];
        $params = http_build_query([
            'smsUser' => $username,
            'smsPassword' => $password,
            'content' => $content,
            'phone' => $this->phone,
            'sendFirst' => 1
        ]);
        $http = new Client();
        $response = $http->get($url . $params);
        $response = (string)$response->getBody()->getContents();
        $response = $this->parseNamespaceXml($response);
        if (array_get($response, 0) == 1) {
            Cache::put($this->cacheKey, 1, $this->cacheTime);
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function sendVerifyCode()
    {
        $code = rand(10000, 99999);
        $data = [
            'status' => false,
            'code' => $code
        ];
        $content = "您的验证码为 " . $code;
        if ($this->send($content)) {
            $data['status'] = true;
            $codeTime = $this->cacheTime + 1;
            Cache::put('smsVerifyCode_' . $this->phone, $code, $codeTime);
        }
        return $data;
    }


    /**
     * @param $checkCode
     *
     * @return bool
     */
    public function checkVerifyCode($checkCode)
    {
        $code = Cache::get('smsVerifyCode_' . $this->phone);
        if (empty($code) || $code != $checkCode) {
            return false;
        }
        return true;
    }

    /**
     * @param $minute
     *
     * @return $this
     */
    public function setupTime($minute)
    {
        $this->cacheTime = $minute;
        return $this;
    }

    /**
     * @param $xml
     *
     * @return mixed
     */
    protected function parseNamespaceXml($xml)
    {
        $xml = preg_replace('/\sxmlns="(.*?)"/', ' _xmlns="${1}"', $xml);
        $xml = preg_replace('/<(\/)?(\w+):(\w+)/', '<${1}${2}_${3}', $xml);
        $xml = preg_replace('/(\w+):(\w+)="(.*?)"/', '${1}_${2}="${3}"', $xml);
        $xml = simplexml_load_string($xml);
        return json_decode(json_encode($xml), true);
    }

    /**
     * @param $phone
     *
     * @return bool
     */
    protected function Validate($phone)
    {
        //中国联通号码：130、131、132、145（无线上网卡）、155、156、185（iPhone5上市后开放）、186、176（4G号段）、175（2015年9月10日正式启用，暂只对北京、上海和广东投放办理）,166,146
        //中国移动号码：134、135、136、137、138、139、147（无线上网卡）、148、150、151、152、157、158、159、178、182、183、184、187、188、198
        //中国电信号码：133、153、180、181、189、177、173、149、199
        $g = "/^1[34578]\d{9}$/";
        $g2 = "/^19[89]\d{8}$/";
        $g3 = "/^166\d{8}$/";
        if (preg_match($g, $phone)) {
            return true;
        } else if (preg_match($g2, $phone)) {
            return true;
        } else if (preg_match($g3, $phone)) {
            return true;
        }
        return false;
    }

}
