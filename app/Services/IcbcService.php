<?php

namespace App\Services;

use App\Models\Wechat\AggregatePayment;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

Class IcbcService
{
    /**
     * @var APP编号
     */
    private $appId;

    /**
     * @var 商户号
     */
    private $mer_id;

    /**
     * @var 应用私钥
     */
    private $privateKey;

    /**
     * @var 应用公钥
     */
    private $pulicKey;

    /**
     * @var 签名类型，’CA’-工行颁发的证书认证;’RSA’表示RSAWithSha1;’RSA2’表示RSAWithSha256;缺省为RSA
     */
    private $signType;

    /**
     * @var 字符集
     */
    private $charset = 'UTF-8';

    /**
     * @var 请求参数格式
     */
    private $format = 'json';

    /**
     * @var 加密类型
     */
    private $encryptType;

    /**
     * @var AES加密密钥
     */
    private $encryptKey;

    /**
     * @var 当签名类型为CA时，通过该字段上送证书公钥
     */
    private $ca;

    /**
     * @var 当签名类型为CA时，通过该字段上送证书密码
     */
    private $password;

    /**
     * @var 网关公钥
     */
    private $gateWayKey;

    /**
     * @var api路径
     */
    private $api_path = [
        'api_payment' => [
            'url' => 'https://gw.open.icbc.com.cn/ui/aggregate/payment/request/V2',   //公众号支付
            'version' => '1.0.0.1'
            ],
        'api_payment_query' => [
            'url' => 'https://gw.open.icbc.com.cn/api/qrcode/V2/query'                //支付结果查询
            ],
        'api_reject' => [
            'url' => 'https://gw.open.icbc.com.cn/api/qrcode/V2/reject'                //支付结果查询
        ]
    ];

    private $interface_version;

    /**
     * @var
     */
    private $http;

    public static function make(string $token, array $customConfig = [])
    {
        return (new static())->initConfig($token, $customConfig);
    }

    protected function initConfig(string $token, array $customConfig)
    {
        $this->http = new Client();
        if ($customConfig) {
            $config = $customConfig;
        } else {
            $config = AggregatePayment::getCache($token);
        }

        $this->appId = $config['icbc_app_id'];
        $this->privateKey = $config['icbc_private_key'];
        $this->pulicKey = $config['icbc_public_key'];
        $this->gateWayKey = $config['icbc_geteway_publickey'];

        if(empty($config['icbc_sign_type'])){
            $this->signType = 'RSA';
        }else{
            $this->signType = $config['icbc_sign_type'];
        }

        if(empty($config['icbc_encrypt_type'])){
            $this->encryptType = 'AES';
        }else{
            $this->encryptType = $config['icbc_encrypt_type'];
        }

        $this->encryptKey = $config['icbc_encrypt_key'];
        $this->mer_id = $config['icbc_mer_id'];

        $this->password = $config['icbc_password'];
        // 去除签名数据及证书数据中的空格
        if (!$config['icbc_ca']) {
            $ca = preg_replace("/\s*|\t/", "", $config['icbc_ca']);
        }
        $this->ca = $ca;

        return $this;
    }

    /**
     * @param $request
     * @param $msgId
     * @param string $requestType
     * @return mixed
     * @throws Exception
     */
    public function execute($requestData, $msgId, string $requestType = 'api_payment')
    {
        $requestData['serviceUrl'] = $this->api_path[$requestType]['url'];
        $requestData['biz_content']['mer_id'] = $this->mer_id;
        $params = $this->prepareParams($requestData, $msgId);

        if($requestData["method"] == 'POST'){
            $data = [
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded'
                ],
                'form_params' => $params
            ];
        } else {
            $data = $params;
        }

        //发送请求,接收响应
        $response = $this->http->request($requestData["method"], $requestData["serviceUrl"], $data);
        $response = json_decode((string)$response->getBody(), true);
        $respBizContentStr = json_encode($response['response_biz_content'],320);
        $sign = $response['sign'];

        //验证响应签名
        $passed = $this->verify($respBizContentStr, $sign, 'RSA');
        if(!$passed){
            throw new Exception("icbc sign verify not passed!");
        }
        if($requestData["isNeedEncrypt"]){
            $respBizContentStr = $this->aesDecrypt(substr($respBizContentStr, 1 , strlen($respBizContentStr)-2), $this->encryptKey);
        }

        //返回解析结果
        return json_decode($respBizContentStr, true);

    }

    /**
     * @param array $requestData
     * @param string $msgId
     * @param string $requestType
     * @return mixed
     * @throws Exception
     */
    public function buildPostForm(array $requestData, string $msgId, string $requestType = 'api_payment')
    {
        $requestData['serviceUrl'] = $this->api_path[$requestType]['url'];
        $requestData['biz_content']['interface_version'] = $this->api_path[$requestType]['version'];
        $requestData['biz_content']['mer_id'] = $this->mer_id;

        $params = $this->prepareParams($requestData, $msgId, null);

        $urlQueryParams = self::buildUrlQueryParams($params);

        $url = self::buildGetUrl($requestData["serviceUrl"],$urlQueryParams);

        return self::buildForm($url,$this->buildBodyParams($params));
    }

    /**
     * @param array $requestData
     * @param string $msgId
     * @param string $requestType
     * @return array
     * @throws Exception
     */
    public function buildFormParams(array $requestData, string $msgId, string $requestType = 'api_payment')
    {
        $requestData['serviceUrl'] = $this->api_path[$requestType]['url'];
        $requestData['biz_content']['interface_version'] = $this->api_path[$requestType]['version'];
        $requestData['biz_content']['mer_id'] = $this->mer_id;

        $params = $this->prepareParams($requestData, $msgId, null);

        $urlQueryParams = self::buildUrlQueryParams($params);

        $url = self::buildGetUrl($requestData["serviceUrl"],$urlQueryParams);

        $returnParams = [
            'url' => $url,
            'params' => $this->buildBodyParams($params)
        ];

        return $returnParams;
    }

    /**
     * @param string $plaintext
     * @param string $key
     * @return false|string
     */
    protected function aesEncrypt(string $plaintext, string $key = null)
    {
        if(empty($plaintext) || empty($key)){
            return '';
        }

        $method = 'AES-128-CBC';
        $ivlen = openssl_cipher_iv_length($method);
        $iv = $iv = str_repeat("\0", $ivlen);
        $encryptStr = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);

        return base64_encode($encryptStr);
    }

    /**
     * @param string $encrypted
     * @param string $key
     * @return false|string
     */
    protected  function aesDecrypt(string $encrypted, string $key = null)
    {
        if(empty($encrypted) || empty($key)){
            return '';
        }
        $method = 'AES-128-CBC';
        $ivlen = openssl_cipher_iv_length($method);
        $iv = $iv = str_repeat("\0", $ivlen);

        $str = openssl_decrypt(base64_decode($encrypted), $method, $key, OPENSSL_RAW_DATA, $iv);

        return $str;
    }

    /**
     * Generate Signature String
     * @param $content
     * @param $privateKey
     * @param $signType
     * @return string
     * @throws Exception
     */
    public function sign(string $content, string $signType, string $privateKey)
    {
        if('RSA' == $signType){
            openssl_sign($content,$signature,"-----BEGIN PRIVATE KEY-----\n".$privateKey."\n-----END PRIVATE KEY-----", OPENSSL_ALGO_SHA1);
        }elseif ('RSA2' == $signType) {
            openssl_sign($content,$signature,"-----BEGIN PRIVATE KEY-----\n".$privateKey."\n-----END PRIVATE KEY-----", OPENSSL_ALGO_SHA256);
        }else{
            throw new Exception("Only support OPENSSL_ALGO_SHA1 or OPENSSL_ALGO_SHA256 algorithm signature!");
        }
        return base64_encode($signature);
    }

    /**
     * Verify signature
     * @param $content
     * @param $signature
     * @param $gateWayKey
     * @param $signType
     * @return int
     * @throws Exception
     */
    public  function verify(string $content, string $signature, string $signType = 'RSA')
    {
        if('RSA' == $signType){
            return openssl_verify($content,base64_decode($signature),"-----BEGIN PUBLIC KEY-----\n".$this->gateWayKey."\n-----END PUBLIC KEY-----", OPENSSL_ALGO_SHA1);
        }elseif ('RSA2' == $signType) {
            return openssl_verify($content,base64_decode($signature),"-----BEGIN PUBLIC KEY-----\n".$this->gateWayKey."\n-----END PUBLIC KEY-----", OPENSSL_ALGO_SHA256);
        }else{
            throw new Exception("Only support OPENSSL_ALGO_SHA1 or OPENSSL_ALGO_SHA256 algorithm signature verify!");
        }
    }

    /**
     * @param array $requestData
     * @param string $msgId
     * @param $appAuthToken
     * @return array
     * @throws Exception
     */
    protected  function prepareParams(array $requestData, string $msgId, string $appAuthToken=null)
    {
        $bizContentStr = json_encode($requestData["biz_content"]);

        $path = parse_url($requestData["serviceUrl"],PHP_URL_PATH);

        $params = array();

        if(isset($requestData["extraParams"]) && !empty($requestData["extraParams"])){
            $params = array_merge($params,$requestData["extraParams"]);
        }

        $params['app_id']          =  $this->appId;
        $params['sign_type']       =  $this->signType;
        $params['charset']         =  $this->charset;
        $params['format']          =  $this->format;
        $params['ca']              =  $this->ca;
        $params['app_auth_token']  =  $appAuthToken;
        $params['msg_id']          =  $msgId;

        date_default_timezone_set('Asia/Shanghai');
        $params['timestamp'] = date('Y-m-d H:i:s');

        if ($requestData["isNeedEncrypt"]){
            if ($bizContentStr != null) {
                $params['encrypt_type'] = $this->encryptType;
                $params['biz_content'] = $this->aesEncrypt($bizContentStr, base64_decode($this->encryptKey));
            }
        } else {
            $params['biz_content'] = $bizContentStr;
        }

        $strToSign = $this->buildSignStr($path, $params);
        $signedStr = $this->sign($strToSign, $this->signType, $this->privateKey);
        $params['sign'] = $signedStr;

        return $params;
    }

    /**
     * Build signature string
     * @param $path
     * @param $params
     * @return string
     */
    public function buildSignStr(string $path, array $params)
    {
        $isSorted = Arr::sortRecursive($params);
        $comSignStr = $path.'?';

        $hasParam = false;
        foreach ($isSorted as $key => $value) {
            if(empty($key) || empty($value)){
                continue;
            }else{
                if ($hasParam) {
                    $comSignStr=$comSignStr.'&';
                }else{
                    $hasParam=true;
                }
                $comSignStr=$comSignStr.$key.'='.$value;
            }
        }

        return $comSignStr;
    }

    /**
     * @param $params
     * @return mixed
     */
    protected static function buildUrlQueryParams(array $params)
    {
        $apiParamNames['sign'] = 'sign';
        $apiParamNames['app_id'] = 'app_id';
        $apiParamNames['sign_type'] = 'sign_type';
        $apiParamNames['charset'] = 'charset';
        $apiParamNames['format'] = 'format';
        $apiParamNames['encrypt_type'] = 'encrypt_type';
        $apiParamNames['timestamp'] = 'timestamp';
        $apiParamNames['msg_id'] = 'msg_id';

        $urlQueryParams = [];
        foreach ($params as $key => $value) {
            if(Arr::has($apiParamNames,$key)){
                $urlQueryParams[$key]=$value;
            }
        }
        return $urlQueryParams;
    }

    /**
     * @param array $params
     * @return mixed
     */
    public function buildBodyParams(array $params)
    {
        $apiParamNames['sign'] = 'sign';
        $apiParamNames['app_id'] = 'app_id';
        $apiParamNames['sign_type'] = 'sign_type';
        $apiParamNames['charset'] = 'charset';
        $apiParamNames['format'] = 'format';
        $apiParamNames['encrypt_type'] = 'encrypt_type';
        $apiParamNames['timestamp'] = 'timestamp';
        $apiParamNames['msg_id'] = 'msg_id';

        foreach ($params as $key => $value) {
            if (Arr::has($apiParamNames,$key)) {
                continue;
            }
            if(empty($value)) continue;
            $urlQueryParams[$key] = $value;
        }

        return $urlQueryParams;
    }

    /**
     * @param $strUrl
     * @param $params
     * @return string
     */
    protected static function buildGetUrl(string $strUrl, array $params)
    {
        if ($params == null || count($params) == 0) {
            return $strUrl;
        }
        $buildUrlParams = http_build_query($params);
        if(!Str::endsWith($strUrl, '?')){ //最后是否以？结尾
            return $strUrl.'?'.$buildUrlParams;
        }
        return $strUrl.$buildUrlParams;
    }

    /**
     * @param $url
     * @param $params
     * @return string
     */
    protected static function buildForm(string $url, array $params)
    {
        $buildedFields = self::buildHiddenFields($params);
        return '<form name="auto_submit_form" method="post" action="'.$url.'" >'."\n".$buildedFields.'<input type="submit" value="立刻提交" style="display:none" >'."\n".'</form>'."\n".'<script>document.forms[0].submit();</script>';
    }

    /**
     * @param $params
     * @return string
     */
    protected static function buildHiddenFields(array $params)
    {
        if ($params == null || count($params) == 0) {
            return '';
        }

        $result = '';
        foreach ($params as $key => $value) {
            if($key == null || $value == null){
                continue;
            }
            $buildfield = self::buildHiddenField($key,$value);
            $result = $result.$buildfield;
        }
        return $result;
    }

    /**
     * @param $key
     * @param $value
     * @return string
     */
    protected static function buildHiddenField(string $key, string $value)
    {
        return '<input type="hidden" name="'.$key.'" value="'.preg_replace('/"/', '&quot;', $value).'">'."\n";
    }

}