<?php

namespace App\Services;

use App\Models\Wxuser;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\WebProcessor;

/**
 * Class OpenlibService
 * @package App\Services
 */
class OpenlibService
{
    /**
     * @var array
     */
    protected static $instance = array();
    /**
     * @var
     */
    protected $token;
    /**
     * @var
     */
    protected $config;

    //['appid','secret','url','opuser']
    protected $appid = 'weixin';
    protected $secret = '6b5bf263d766d5d5603817ead799f382';
    /**
     * @var
     */
    protected $client;

    /**
     * @param string $token
     * @param array  $config
     * @return $this
     */
    public static function make(string $token, $config = [])
    {
        if (isset(self::$instance[$token])) {
            return self::$instance[$token];
        }
        self::$instance[$token] = new static();
        return self::$instance[$token]->setConfig($token, $config);
    }

    /**
     * @param       $path
     * @param array $params
     * @return string
     */
    protected function autoHttpUrl($path, array $params = array())
    {
        return Str::finish($this->getConfig('url'), '/') . $path . '?' . http_build_query($params);
    }

    /**
     * 封装请求方法
     * @param string $method
     * @param        $url
     * @param array  $form_params
     * @return array|mixed
     * @throws
     */
    protected function sendRequest($method = 'GET', $url, array $form_params = array())
    {
        if (empty($this->client)) {
            $this->client = new Client();
        }
        $basis = [
            'timeout' => 10.0,
            'connect_timeout' => 15.0,
            'http_errors' => true
        ];
        //POST表单请求
        if (count($form_params) > 0 && strtoupper($method) === 'POST') {
            $basis['form_params'] = $form_params;
        }

        try {
            $response = $this->client->request($method, $url, $basis);
        } catch (RequestException $e) {
            $context = [
                'url' => $url,
                'code' => $e->getCode(),
                'mes' => $e->getMessage(),
            ];
            if ($e->hasResponse()) {
                $context['mes'] = $e->getResponse()->getReasonPhrase();
            }
            $logger = new Logger('HTTP');
            $logger->pushHandler(new RotatingFileHandler(storage_path('logs/openlib.log')));
            $logger->pushProcessor(new WebProcessor(null, ['ip']));
            $logger->error($this->token, $context);

            $response = [
                'messagelist' => [['message' => 'ope接口异常,请稍后再试', 'code' => 10000]],
                'success' => false
            ];
            return $response;
        }
        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @param string $method
     * @param        $url
     * @param        $params
     * @param array  $postData
     * @return array|mixed
     */
    protected function httpMagic($method = 'GET', $url, $params, array $postData = [])
    {
        $response = $this->getToken();
        if ($response['success'] == false) {
            return $response;
        }
        $params['token'] = Arr::get($response, 'messagelist.0.token');
        return $this->sendRequest($method, $this->autoHttpUrl($url, $params), $postData);
    }

    /**
     * @param $token
     * @param $config
     * @return $this
     */
    public function setConfig($token, $config)
    {
        $this->token = $token;
        if ($config && ($config instanceof Model || is_array($config))) {
            $appid = isset($config['openlib_appid']) ? $config['openlib_appid'] : $this->appid;
            $secret = isset($config['openlib_secret']) ? $config['openlib_secret'] : $this->secret;
            $opuser = isset($config['openlib_opuser']) ? $config['openlib_opuser'] : '';
            $this->config = [
                'appid' => $appid,
                'secret' => $secret,
                'url' => $config['openlib_url'],
                'opuser' => $opuser
            ];
        }
        return $this;
    }

    /**
     * @param $need
     * @return array|mixed
     */
    public function getConfig($need)
    {
        if (empty($this->config)) {
            $wxuser = Wxuser::getCache($this->token);
            if (empty($wxuser)) abort(404);
            $this->config = [
                'appid' => $wxuser['openlib_appid'],
                'secret' => $wxuser['openlib_secret'],
                'url' => $wxuser['openlib_url'],
                'opuser' => $wxuser['openlib_opuser']
            ];
        }
        if (is_array($need)) {
            return Arr::only($this->config, $need);
        }
        if (is_string($need)) {
            return Arr::get($this->config, $need);
        }
        return $this->config;
    }

    /**
     * @param bool $refresh
     * @return array|mixed
     */
    public function getToken($refresh = false)
    {
        $appid = $this->getConfig('appid');
        $url = $this->getConfig('url');
        $parse_url = parse_url($url);
        $s1 = isset($parse_url['port']) ? $parse_url['host'] . ':' . $parse_url['port'] : $parse_url['host'];
        $cacheKey = sprintf(config('cacheKey.openlib'), $s1, $appid);

        if ($refresh == true) {
            Cache::forget($cacheKey);
        }
        $cache = Cache::get($cacheKey);
        if ($cache === null) {
            $params = $this->getConfig(['appid', 'secret']);
            $url = $this->autoHttpUrl('service/barcode/token', $params);
            $response = $this->sendRequest('GET', $url);
            if ($response['success'] == false) {
                return $response;
            }
            if (Arr::has($response, 'messagelist.0.time')) {
                $time = Carbon::parse($response['messagelist'][0]['time'])->subHour();
            } else {
                $time = Carbon::now()->addMinutes(40);
            }
            Cache::put($cacheKey, $response, $time);
            return $response;
        }
        return $cache;
    }


    /**
     * @param       $rdid
     * @param       $rdpasswd
     * @param array $other
     * @return array|mixed
     */
    public function confirmreader($rdid, $rdpasswd, array $other = [])
    {
        /**
         * localvalid    是否本地查询 默认0  0：非本地认证  1：本地读者认证
         * havecluster   是否查集群读者
         * globalid      读者所在集群馆唯一编码 havecluster 为1时，必填
         */
        $params = [
            'rdpasswd' => $rdpasswd,
            'rdid' => $rdid,
            'opuser' => $this->getConfig('opuser'),
            'localvalid' => Arr::get($other, 'localvalid', 1),
            'havecluster' => Arr::get($other, 'havecluster', 0),
            'globalid' => Arr::get($other, 'globalid', '')
        ];
        return $this->httpMagic('GET', 'service/reader/confirmreader', $params);
    }

    /**
     * @param null $rdid
     * @param null $rdcertify
     * @param int  $havecluster
     * @return array|mixed
     */
    public function searchreader($rdid = null, $rdcertify = null, $havecluster = 0)
    {
        $params = [
            'rdid' => $rdid,
            'rdcertify' => $rdcertify,
            'havecluster' => $havecluster,
        ];
        return $this->httpMagic('GET', 'service/reader/searchreader', $params);
    }


    /**
     * @param        $rdid
     * @param string $cardtype
     * @param string $havecluster
     * @return array|mixed
     */
    public function currentloan($rdid, $havecluster = '', $cardtype = '')
    {
        $params = [
            'rdid' => $rdid,
            'havecluster' => $havecluster,
            'cardtype' => $cardtype
        ];
        return $this->httpMagic('GET', 'service/barcode/currentloan', $params);
    }

    /**
     * @param $rdid
     * @param $startdate
     * @param $enddate
     * @param $page
     * @param $rows
     * @return array|mixed
     */
    public function historyloan($rdid, $startdate, $enddate, $page, $rows)
    {
        $params = [
            'rdid' => $rdid,
            'startdate' => $startdate,
            'enddate' => $enddate,
            'page' => $page,
            'rows' => $rows,
        ];
        return $this->httpMagic('GET', 'service/barcode/historyloan', $params);
    }

    /**
     * @param $rdid
     * @param $barcode
     * @return array|mixed
     */
    public function renewbook($rdid, $barcode)
    {
        $params = [
            'rdid' => $rdid,
            'barcode' => $barcode,
            'opuser' => $this->getConfig('opuser')
        ];
        return $this->httpMagic('GET', 'service/barcode/renewbook', $params);
    }

    /**
     * @param $request
     * @return array|mixed
     */
    public function searchbib($request)
    {
        $params = [
            'page' => $request->input('page', 1),
            'rows' => $request->input('rows', 10),
            'libcode' => $request->input('libcode'),
            'queryparam' => $request->input('queryparam'),
            'queryvalue' => $request->input('queryvalue'),
        ];
        return $this->httpMagic('GET', 'service/book/searchbib', $params);
    }

    /**
     * @return array|mixed
     */
    public function getlibSecondaryList()
    {
        $params = [
            'type' => 1
        ];
        return $this->httpMagic('GET', 'service/query/libinfo', $params);
    }

    /**
     * @param $rdid
     * @return array|mixed
     */
    public function searchprelendlist($rdid)
    {
        $params = [
            'rdid' => $rdid
        ];
        return $this->httpMagic('GET', 'service/book/searchprelendlist', $params);
    }

    /**
     * @param $rdid
     * @return array|mixed
     */
    public function searchreslist($rdid)
    {
        $params = [
            'rdid' => $rdid
        ];
        return $this->httpMagic('GET', 'service/book/searchreslist', $params);
    }


    /**
     * @param        $rdid
     * @param        $barcode
     * @param string $locationcode
     * @return array|mixed
     */
    public function sendRegisterprelend($rdid, $barcode, $locationcode = '')
    {
        $params = [
            'rdid' => $rdid,
            'barcode' => $barcode,
            'locationcode' => $locationcode,
//            判断已借数量时，是否计算已预借数量（1登记、2分配） 0 否 （默认）、1 是
            'ishavePrelend' => 1,
//            判断已借数量时，是否计算已预约数量（1登记、2分配） 0 否 （默认）、1 是
            'ishaveRes' => 0,
        ];
        return $this->httpMagic('GET', 'service/barcode/registerprelend', $params);
    }

    /**
     * @param $rdid
     * @param $barcode
     * @return array|mixed
     */
    public function sendCancelprelend($rdid, $barcode)
    {
        $params = [
            'rdid' => $rdid,
            'barcode' => $barcode
        ];
        return $this->httpMagic('GET', 'service/barcode/cancelprelend', $params);
    }

    /**
     * @param $request
     * @return array|mixed
     */
    public function getQueryHolding($request)
    {
        $params = $request->only(['barcode', 'bookrecno']);
        return $this->httpMagic('GET', 'service/barcode/queryholding', $params);
    }


    /**
     * 预约图书
     * @param        $rdid   读者证号
     * @param        $barcode   图书条码
     * @param string $canceldate 取消时间
     * @param string $piclocal 取书地点
     * @param int    $ishavePrelend 判断已借数量时，是否计算已预借数量（1登记、2分配）0 否 （默认）、1 是
     * @param int    $ishaveRes 判断已借数量时，是否计算已预约数量（1登记、2分配）0 否 （默认）、1 是
     * @return array|mixed
     */
    public function sendRegisterreserve($rdid, $barcode, $canceldate = '', $piclocal = '', $ishavePrelend = 0, $ishaveRes = 0)
    {
        $params = [
            'rdid' => $rdid,
            'barcode' => $barcode,
            'canceldate' => $canceldate,
            'piclocal' => $piclocal,
            'ishavePrelend' => $ishavePrelend,
            'ishaveRes' => $ishaveRes,
        ];
        return $this->httpMagic('GET', 'service/barcode/registerreserve', $params);
    }

    /**
     * @param $rdid
     * @param $barcode
     * @return array|mixed
     */
    public function sendCancelreserve($rdid, $barcode)
    {
        $params = [
            'rdid' => $rdid,
            'barcode' => $barcode
        ];
        return $this->httpMagic('GET', 'service/barcode/cancelreserve', $params);
    }

    /**
     * 新增读者
     * @param $params
     * @return array|mixed
     */
    public function addreader($params)
    {
        //判断是否存在身份证,识别出生年月,默认传参
        if (Arr::has($params, 'rdcertify')) {
            $rdcertify = $params['rdcertify'];
            $rdborndate = substr($rdcertify, 6, 4) . '-' . substr($rdcertify, 10, 2) . '-' . substr($rdcertify, 12, 2);
            if (strlen($rdcertify) == 15) {
                $rdborndate = '19' . substr($rdcertify, 6, 2) . '-' . substr($rdcertify, 8, 2) . '-' . substr($rdcertify, 10, 2);
            }
            $params['rdborndate'] = $rdborndate;
        }

        //判断是否存在上传图片字段
        if (Arr::has($params, 'baseimg64')) {
            //baseimg64	读者照片(base64编码的二进制图片数据)
            $postData = [
                'baseimg64' => $params['baseimg64']
            ];
            unset($params['baseimg64']);
            // 传1表示读者图片不进行压缩
            $params['nocompress'] = 1;
            return $this->httpMagic('post', 'service/reader/addreader', $params, $postData);
        }

        return $this->httpMagic('get', 'service/reader/addreader', $params);
    }

    /**
     * @param $params
     * @return array|mixed
     */
    public function searchdebt($params)
    {
        return $this->httpMagic('GET', 'service/reader/searchdebt', $params);
    }

    /**
     * @param $rdid
     * @param $money
     * @return array|mixed
     */
    public function addprepay($rdid, $money)
    {
        $params = [
            'rdid' => $rdid,
            'money' => $money,
            'moneytype' => 6,
            'opuser' => $this->getConfig('opuser')
        ];
        return $this->httpMagic('GET', 'service/reader/addprepay', $params);
    }

    /**
     * @param $rdid
     * @param $money
     * @param $serialno
     * @return array|mixed
     */
    public function paymoney($rdid, $money, $serialno)
    {
        $params = [
            'rdid' => $rdid,
            'money' => $money,
            'moneytype' => 6,
            'opuser' => $this->getConfig('opuser'),
            'serialno' => $serialno,
        ];
        return $this->httpMagic('GET', 'service/reader/paymoney', $params);
    }

    /**
     * @param        $rdid
     * @param        $rdstate
     * @param string $rdremark
     * @param array  $otherParams
     * @return array|mixed
     */
    public function cardmanage($rdid, $rdstate, $rdremark = '', $otherParams = [])
    {
//        1 恢复,2 验证,3 挂失, 4 暂停,5 注销,6 退证, 7 补办,8 延期,9 换证
        $params = [
            'currdid' => $rdid,
            'rdstate' => $rdstate,
            'rdremark' => $rdremark,
            'opuser' => $this->getConfig('opuser'),
        ];
        if (isset($otherParams['data5'])) {
            $params['data5'] = $otherParams['data5'];
        }
        return $this->httpMagic('GET', 'service/reader/cardmanage', $params);
    }

    /**
     * @param $params
     * @return array|mixed
     */
    public function searchoverdue($params)
    {
        return $this->httpMagic('GET', 'service/reader/searchoverdue', $params);
    }

    /**
     * @param $params
     * @return array|mixed
     */
    public function onefinhandle($params)
    {
        $params['opuser'] = $this->getConfig('opuser');
        return $this->httpMagic('GET', 'service/reader/onefinhandle', $params);
    }


    /**
     * @param string $selecttype
     * @param        $queryvalue
     * @param int    $havecluster
     * @return array|mixed
     */
    public function searchreaderlist($selecttype = 'rdid', $queryvalue, $havecluster = 0)
    {
        $params = [
            'selecttype' => $selecttype,
            'queryvalue' => $queryvalue,
            'havecluster' => $havecluster,
        ];
        return $this->httpMagic('GET', 'service/reader/searchreaderlist', $params);
    }

    /**
     * 修改读者类型
     * @param $rdid
     * @param $newrdtype
     * @return array|mixed
     */
    public function rdtypechange($rdid, $newrdtype)
    {
        $params = [
            'rdid' => $rdid,
            'newrdtype' => $newrdtype,
            'opuser' => $this->getConfig('opuser'),
        ];
        return $this->httpMagic('GET', 'service/reader/rdtypechange', $params);
    }

    /**
     * 变更读者类型前验证
     * @param $rdid
     * @param $newrdtype
     * @return array|mixed
     */
    public function rdtypechangebefore($rdid, $newrdtype)
    {
        $params = [
            'rdid' => $rdid,
            'newrdtype' => $newrdtype,
        ];
        return $this->httpMagic('GET', 'service/reader/rdtypechangebefore', $params);
    }

}
