<?php
//
//namespace App\Http\Controllers\Api\Mini;
//
//use App\Http\Controllers\Controller;
//use App\Models\Mini\Registration;
//use Carbon\Carbon;
//use GuzzleHttp\Client;
//use Illuminate\Http\Request;
//use Illuminate\Support\Arr;
//use Illuminate\Support\Facades\Cache;
//
//class BaseController extends Controller
//{
//    protected $openlibConf = null;
//    public $token;
//
//    public function __construct(Request $request)
//    {
//        $this->token = $request->input('token');
//    }
//
//    protected function initOpenlibConfig()
//    {
//        if (empty($this->openlibConf)) {
//            $registration = Registration::getCache($this->token);
//            $this->openlibConf = $registration->only(['openlib_appid', 'openlib_secret', 'openlib_url', 'openlib_opuser']);
//        }
//    }
//
//    public function searchreader($rdid = null, $rdcertify = null, $havecluster = 0)
//    {
//        $params = [
//            'rdid' => $rdid,
//            'rdcertify' => $rdcertify,
//            'havecluster' => $havecluster,
//        ];
//        return $this->httpMagic($params, 'service/reader/searchreader');
//    }
//
//    public function searchreaderlist($selecttype, $queryvalue, $havecluster = 0)
//    {
//        $params = [
//            'selecttype' => $selecttype,
//            'queryvalue' => $queryvalue,
//            'havecluster' => $havecluster,
//        ];
//        return $this->httpMagic($params, 'service/reader/searchreaderlist');
//    }
//
//    public function confirmreader($rdid, $rdpasswd)
//    {
//        $this->initOpenlibConfig();
//        $params = [
//            'rdpasswd' => $rdpasswd,
//            'rdid' => $rdid,
//            'opuser' => $this->openlibConf['openlib_opuser'],
//            'localvalid' => 1
//        ];
//        return $this->httpMagic($params, 'service/reader/confirmreader');
//    }
//
//
//    public function httpMagic($params, $url)
//    {
//        $response = $this->getOpenlibToken();
//        if ($response['success'] == false) {
//            return $response;
//        }
//        $params = Arr::add($params, 'token', $response['token']);
//        $url = $this->autoHttpUrl($params, $url);
//        $http = new Client();
//        $response = $http->get($url);
//        return json_decode((string)$response->getBody(), true);
//    }
//
//    public function autoHttpUrl($data = [], $path)
//    {
//        return $this->openlibConf['openlib_url'] . $path . '?' . http_build_query($data);
//    }
//
//
//    public function getOpenlibToken()
//    {
//        $this->initOpenlibConfig();
//        $cacheKey = 'mini:Opl:' . $this->token;
//        $openlibToken = Cache::get($cacheKey);
//        if (empty($openlibToken)) {
//            $params = [
//                'appid' => $this->openlibConf['openlib_appid'],
//                'secret' => $this->openlibConf['openlib_secret']
//            ];
//            $url = $this->autoHttpUrl($params, 'service/barcode/token');
//            $http = new Client();
//            $response = $http->get($url);
//            $response = json_decode((string)$response->getBody(), true);
//
//            if ($response['success'] === true) {
//                $token = $response['messagelist'][0]['token'];
//                $time = $response['messagelist'][0]['time'];
//                $time = Carbon::parse($time)->subHour(1);
//                Cache::put($cacheKey, $token, $time);
//                return ['success' => true, 'token' => $token];
//            }
//            return $response;
//        }
//        return ['success' => true, 'token' => $openlibToken];
//    }
//
//    public function addreader($params)
//    {
//        //判断是否存在身份证,识别出生年月,默认传参
//        if (Arr::has($params, 'rdcertify')) {
//            $rdcertify = $params['rdcertify'];
//            $rdborndate = substr($rdcertify, 6, 4) . '-' . substr($rdcertify, 10, 2) . '-' . substr($rdcertify, 12, 2);
//            if (strlen($rdcertify) == 15) {
//                $rdborndate = '19' . substr($rdcertify, 6, 2) . '-' . substr($rdcertify, 8, 2) . '-' . substr($rdcertify, 10, 2);
//            }
//            $params['rdborndate'] = $rdborndate;
//        }
//        return $this->httpMagic($params, 'service/reader/addreader');
//    }
//
//    public function rdtypechange($rdid, $newrdtype)
//    {
//        $this->initOpenlibConfig();
//        $params = [
//            'rdid' => $rdid,
//            'newrdtype' => $newrdtype,
//            'opuser' => $this->openlibConf['openlib_opuser'],
//        ];
//        return $this->httpMagic($params, 'service/reader/rdtypechange');
//    }
//
//}
