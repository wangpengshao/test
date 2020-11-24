<?php

namespace App\Services;


use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

class FaceIDService
{
    protected $appid = 'tcsoft_wx_portals';
    protected $secret = 'tcsoft_wx_portals123';
    protected $globallibid = 'c_wx_portal';
    protected $systemcertify = 'tc_wx_portal';

    protected $host = 'http://open.interlib.cn/biometricservice';

    protected $faceIDCacheKey = 'faceID_token_wechat';

    protected $token;

    public function initialize()
    {
        $this->token = Cache::get($this->faceIDCacheKey, function () {
            return $this->getToken();
        });
    }

    public function getToken()
    {
        $http = new Client();
        $url = $this->host . '/service/token?';
        $params = http_build_query([
            'appid' => $this->appid,
            'secret' => $this->secret
        ]);
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);

        if ($response['success'] === true) {
            $token = $response['map']['token'];
            $time = $response['map']['time'];
            $time = Carbon::parse($time)->subHour(1);
            Cache::put($this->faceIDCacheKey, $token, $time);
            return $token;
        }
        return false;
    }


    /**
     * @param $idCard
     * @param $name
     * @param $faceImg base64
     *
     * @return mixed|\Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function check($idCard, $name, $faceImg)
    {
        $this->initialize();
        $http = new Client();
        $url = $this->host . '/verify/service/personverify?';
        $params = http_build_query([
            'token' => $this->token
        ]);
//        $faceImg = str_replace('+', '%2B', $faceImg);
        $form = [
            'usercertify' => $idCard,
            'username' => $name,
            'globallibid' => $this->globallibid,
            'systemcertify' => $this->systemcertify,
            'callversion' => '2.0',
            'baseimg64' => $faceImg
        ];

        $response = $http->request('POST', $url . $params, ['form_params' => $form]);
        $response = json_decode((string)$response->getBody(), true);
        return $response;
    }
}
