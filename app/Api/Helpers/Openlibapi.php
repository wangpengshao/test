<?php

namespace App\Api\Helpers;

use App\Models\Wxuser;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;

trait Openlibapi
{
    protected $data = [];

    protected $token;

    public function initOpenlibConfig()
    {
        $token = $this->token;
        $wxuser = Wxuser::getCache($token);
        $this->data = $wxuser->only(['openlib_appid', 'openlib_secret', 'openlib_url', 'openlib_opuser']);
    }

    public function httpMagic($token, $params, $url)
    {
        $openlibToken = $this->getOpenlibToken($token);
        $params += ['token' => $openlibToken];
        $url = $this->autoHttpUrl($params, $url);
        $http = new Client();
        $response = $http->get($url);
        return json_decode((string)$response->getBody(), true);
    }

    public function autoHttpUrl($data = [], $path)
    {
        return $this->data['openlib_url'] . $path . '?' . http_build_query($data);
    }

    public function getOpenlibToken($token)
    {
        $this->token = $token;
        $this->initOpenlibConfig();
        return Cache::get('openlibToken_' . $this->token, function () {
            return $this->sendOpenlibGetToken();
        });

    }

    public function sendOpenlibGetToken()
    {
        $params = [
            'appid' => $this->data['openlib_appid'],
            'secret' => $this->data['openlib_secret']
        ];
        $url = $this->autoHttpUrl($params, '/service/barcode/token');

        $http = new Client();
        $response = $http->get($url);
        $response = json_decode((string)$response->getBody(), true);

        if ($response['success'] === true) {
            $token = $response['messagelist'][0]['token'];
            $time = $response['messagelist'][0]['time'];
            $time = Carbon::parse($time)->subHour(1);
            Cache::put('openlibToken_' . $this->token, $token, $time);
            return $token;
        }
        return false;
    }

    public function confirmreader($token, $rdid, $rdpasswd)
    {
        $this->getOpenlibToken($token);

        $params = [
            'rdpasswd' => $rdpasswd,
            'rdid' => $rdid,
            'opuser' => $this->data['openlib_opuser'],
            'localvalid' => 1
        ];

        return $this->httpMagic($token, $params, '/service/reader/confirmreader');
    }

    public function searchreader($token, $rdid = null, $rdcertify = null, $havecluster = 0)
    {
        $params = [
            'rdid' => $rdid,
            'rdcertify' => $rdcertify,
            'havecluster' => $havecluster,
        ];
        return $this->httpMagic($token, $params, '/service/reader/searchreader');
    }

    public function currentloan($token, $rdid)
    {
        $params = [
            'rdid' => $rdid,
        ];
        return $this->httpMagic($token, $params, '/service/barcode/currentloan');
    }

    public function historyloan($token, $rdid, $startdate, $enddate, $page, $rows)
    {
        $params = [
            'rdid' => $rdid,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'page' => $page,
            'rows' => $rows,
        ];
        return $this->httpMagic($token, $params, '/service/barcode/historyloan');
    }

    //续借
    public function renewbook($token, $rdid, $barcode)
    {
        $this->getOpenlibToken($token);
        $params = [
            'rdid' => $rdid,
            'barcode' => $barcode,
            'opuser' => $this->data['openlib_opuser']
        ];

        return $this->httpMagic($token, $params, '/service/barcode/renewbook');
    }

    //检索书目
    public function searchbib($token, $request)
    {
        $params = [
            'page' => $request->input('page', 1),
            'rows' => $request->input('rows', 10),
            'libcode' => $request->input('libcode'),
            'queryparam' => $request->input('queryparam'),
            'queryvalue' => $request->input('queryvalue'),
        ];
        return $this->httpMagic($token, $params, '/service/book/searchbib');

    }

    public function getlibSecondaryList($token)
    {
        $params = [
            'type' => 1
        ];
        return $this->httpMagic($token, $params, '/service/query/libinfo');
    }

    public function searchprelendlist($token, $rdid)
    {
        $params = [
            'rdid' => $rdid
        ];
        return $this->httpMagic($token, $params, '/service/book/searchprelendlist');
    }

    public function searchreslist($token, $rdid)
    {
        $params = [
            'rdid' => $rdid
        ];
        return $this->httpMagic($token, $params, '/service/book/searchreslist');
    }

    public function sendRegisterprelend($token, $rdid, $barcode)
    {
        $params = [
            'rdid' => $rdid,
            'barcode' => $barcode
        ];
        return $this->httpMagic($token, $params, '/service/barcode/registerprelend');
    }

    public function sendCancelprelend($token, $rdid, $barcode)
    {
        $params = [
            'rdid' => $rdid,
            'barcode' => $barcode
        ];
        return $this->httpMagic($token, $params, '/service/barcode/cancelprelend');
    }

    public function getQueryHolding($token, $request)
    {
        $params = $request->only(['barcode', 'bookrecno']);
        return $this->httpMagic($token, $params, '/service/barcode/queryholding');
    }

    public function sendRegisterreserve($token, $rdid, $barcode)
    {
        $params = [
            'rdid' => $rdid,
            'barcode' => $barcode
        ];
        return $this->httpMagic($token, $params, '/service/barcode/registerreserve');
    }

    public function sendCancelreserve($token, $rdid, $barcode)
    {
        $params = [
            'rdid' => $rdid,
            'barcode' => $barcode
        ];
        return $this->httpMagic($token, $params, '/service/barcode/cancelreserve');
    }

    public function addreader($token, $params)
    {
        return $this->httpMagic($token, $params, '/service/reader/addreader');
    }

    public function searchdebt($token, $params)
    {
        return $this->httpMagic($token, $params, '/service/reader/searchdebt');
    }

    public function addprepay($token, $rdid, $money)
    {
        $this->getOpenlibToken($token);
        $params = [
            'rdid' => $rdid,
            'money' => $money,
            'moneytype' => 6,
            'opuser' => $this->data['openlib_opuser'],
        ];
        return $this->httpMagic($token, $params, '/service/reader/addprepay');
    }

    public function paymoney($token, $rdid, $money, $serialno)
    {
        $this->getOpenlibToken($token);
        $params = [
            'rdid' => $rdid,
            'money' => $money,
            'moneytype' => 6,
            'opuser' => $this->data['openlib_opuser'],
            'serialno' => $serialno,
        ];
        return $this->httpMagic($token, $params, '/service/reader/paymoney');
    }

    public function cardmanage($token, $rdid, $rdstate)
    {
//        1 恢复,2 验证,3 挂失, 4 暂停,5 注销,6 退证, 7 补办,8 延期,9 换证
        $this->getOpenlibToken($token);
        $params = [
            'currdid' => $rdid,
            'rdstate' => $rdstate,
            'opuser' => $this->data['openlib_opuser'],
        ];
        return $this->httpMagic($token, $params, '/service/reader/cardmanage');
    }

    public function searchoverdue($token, $params)
    {
        return $this->httpMagic($token, $params, '/service/reader/searchoverdue');
    }

    public function onefinhandle($token, $params)
    {
        $this->getOpenlibToken($token);
        $params['opuser'] = $this->data['openlib_opuser'];
        return $this->httpMagic($token, $params, '/service/reader/onefinhandle');
    }
}
