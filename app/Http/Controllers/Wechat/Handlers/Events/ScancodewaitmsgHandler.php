<?php


namespace App\Http\Controllers\Wechat\Handlers\Events;

use App\Models\Wechat\Reader;
use App\Models\Wxuser;
use App\Services\Des;
use App\Services\IsbnService;
use App\Services\JybService;
use App\Unified\ReaderService;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * 微信公众号-菜单扫码等待事件
 * Class ScancodewaitmsgHandler
 * @package App\Http\Controllers\Wechat\Handlers\Events
 */
class ScancodewaitmsgHandler
{
    /**
     * @var
     */
    protected $token;
    /**
     * @var
     */
    protected $openid;
    /**
     * @var
     */
    protected $wxuser;


    public static function handle($data = null)
    {
        $token = request()->route('token');
        $openid = $data['FromUserName'];
        $scanType = $data['ScanCodeInfo']['ScanType'];
        $scanResult = $data['ScanCodeInfo']['ScanResult'];

        $myClass = (new self());
        $myClass->token = $token;
        $myClass->openid = $openid;
        $myClass->wxuser = Wxuser::getCache($token);

        //兼容旧版DES 二维码 自助机扫码登录
        if (strlen($scanResult) > 200 && ctype_xdigit($scanResult)) {
            $des = new Des();
            $decryptStr = $des->decrypt($scanResult, '376B4A409E5789CE');
            $decryptArr = json_decode($decryptStr, true);
            if ($decryptArr) {
                return $myClass->oldSelfService($decryptArr);
            }
            return '二维码无效，请刷新二维码重新进行扫码!';
        }

        //自助机扫码登录
        $isURL = filter_var($scanResult, FILTER_VALIDATE_URL);
        $vueUrlKey = 'login/' . $token . '/';

        if ($isURL && Str::contains($scanResult, $vueUrlKey)) {
            $parse_url = parse_url($scanResult);
            $uuid = Str::after($parse_url['fragment'], $vueUrlKey);
//            http://b.dataesb.com/?code=023VEnfr0C8Bbo1ITebr0kSifr0VEnfm&state=STATE#/login/18c6684c/123123asdasd
            if ($parse_url['host'] === 'b.dataesb.com' && !empty($uuid)) {
                return $myClass->selfService($uuid);
            }
        }

        $typeArray = [
            'gzlibZyzqd',   //广州志愿者签到
            'HDQD',         //活动签到
            'HDBM',         //活动报名
            'PXQD',         //培训签到
        ];
        $text = false;
        //安卓App 扫码bug(官方) ScanType => 空  临时处理
        if ($scanType === 'qrcode' || $scanType == '') {
            $scanJson = json_decode($scanResult, true);
            $typeJson = Arr::get($scanJson, 'Uwsyskey');
            if ($typeJson) {
                if (in_array($typeJson, $typeArray)) {
                    return $myClass->$typeJson($scanJson);
                }
            }
        }

        if ($scanType === 'barcode') {
            $IsbnService = new IsbnService();
            $isbn = Str::after($scanResult, ',');
            if ($IsbnService->is_isbn($isbn) && !empty($myClass->wxuser['opcs_url'])) {
                return $myClass->scanJG($isbn);
            }

        }

        return $text;
    }


    /**
     * 查询当前绑定读者
     * @return mixed
     */
    protected function getReader()
    {
        $where = ['token' => $this->token, 'openid' => $this->openid, 'is_bind' => 1];
        return Reader::where($where)->first(['rdid', 'name']);
    }


    /**
     * 输出 提示绑定文本
     * @return string
     */
    protected function goBindText()
    {
        $bindUrl = config('vueRoute.bindReader');
        $bindUrl = str_replace('{token}', $this->token, $bindUrl);
        return '您尚未绑定帐号，<a href="' . $bindUrl . '">点击这里</a>进行绑定';
    }

    /**
     * 广州图书馆 志愿者家
     * @param $scanJson
     * @return mixed|string
     */
    protected function gzlibZyzqd($scanJson)
    {
        $reader = $this->getReader();
        if (!$reader) {
            return $this->goBindText();
        }
        $params = http_build_query([
            'aid' => $scanJson['id'],
            'rdid' => $reader['rdid'],
            'auth' => md5($reader['rdid'] . $scanJson['id'] . 'tworker'),
            'time' => time()
        ]);
        $http = new Client();
        $response = $http->get($scanJson['url'] . '?' . $params);
        $response = json_decode((string)$response->getBody(), true);
        if ($response['state'] == '-1' || $response['state'] == '0') {
            return Arr::get($response, 'msg');
        }
        return '志愿者系统繁忙，请稍后再试!';

    }

    /**
     * 活动系统-签到
     * @param $scanJson
     * @return mixed|string
     */
    protected function HDQD($scanJson)
    {
        $reader = $this->getReader();
        if (!$reader) {
            return $this->goBindText();
        }
        $params = http_build_query([
            'cmd' => 'actSign',
            'contentType' => 'json',
            'rdid' => $reader['rdid'],
//            'rdpasswd' => md5($info['data']['rdpasswd']),
            'specialId' => Arr::get($scanJson, 'specialId'),
            'dateId' => Arr::get($scanJson, 'dateId'),
            'time' => Arr::get($scanJson, 'time'),
        ]);
        $http = new Client();
        $response = $http->get($this->wxuser['activity_url'] . 'web/actionApi?' . $params);
        $response = json_decode((string)$response->getBody(), true);
        return Arr::get($response, 'message');
    }

    /**
     * 活动系统-报名
     * @param $scanJson
     * @return mixed|string
     * @throws \Matrix\Exception
     */
    protected function HDBM($scanJson)
    {
        $reader = $this->getReader();
        if (!$reader) {
            return $this->goBindText();
        }
        $wxuser = $this->wxuser;
        $readerService = new ReaderService($wxuser);
        $info = $readerService->searchUser($reader['rdid']);
        if ($info['status'] != true) {
            return $info['message'];
        }
        $params = http_build_query([
            'cmd' => 'enterFor',
            'rdid' => $reader['rdid'],
            'rdpasswd' => md5($info['data']['rdpasswd']),
            'specialId' => $scanJson['specialId'],
        ]);
        $http = new Client();
        $response = $http->get($wxuser['activity_url'] . 'mb/mobileApi?' . $params);
        $response = json_decode((string)$response->getBody(), true);
        return Arr::get($response, 'message');
    }

    /**
     * 扫码登录(新)
     * @param $uuid
     * @return string
     */
    protected function selfService($uuid)
    {
        $reader = $this->getReader();
        if (!$reader) {
            return $this->goBindText();
        }
        $jybService = new JybService();

        $wxuser = $this->wxuser;
        $response = $jybService->saveSerial($wxuser, $reader, $uuid);

        if (Arr::get($response, 'code') === '200001') {
            return '二维码无效，请刷新二维码重新进行扫码!';
        }
        if (Arr::get($response, 'code') === '200003') {
            return '当前的馆与读者证所在馆不匹配!';
        }
        if (Arr::get($response, 'code') === '200') {
            return '扫码登录成功!';
        }
        return '系统繁忙,请稍后再试!';
    }

    /**
     * 扫码荐购
     * @param $isbn
     * @return string
     */
    public function scanJG($isbn)
    {
        $reader = $this->getReader();
        if (!$reader) {
            return $this->goBindText();
        }
        $wxuser = $this->wxuser;

        $time = date('Y-m-d');
        $params = http_build_query([
            'refer' => 'wx',
            'rdid' => $reader['rdid'],
            'isbn' => $isbn,
            'glc' => $wxuser['glc'],
            'token' => md5($wxuser['glc'] . $reader['rdid'] . $isbn),
            'ticket' => md5($isbn . $time . $reader['rdid']),
            'libcode' => $wxuser['libcode'],
        ]);

        $http = new Client();
        $response = $http->get($wxuser['opcs_url'] . 'interface/addBookStoreRecommend?' . $params);
        $response = json_decode((string)$response->getBody(), true);

        $flag = Arr::get($response, 'flag');
        switch ($flag) {
            case 'fail':
                $mes = $response['msg'];
                break;
            case 'suc':
                $mes = '恭喜你成功荐购：《' . Arr::get($response, 'commend.title') . '》,请将荐购的书籍拿给前台工作人员进行图书借阅操作，感谢您的参与';
                break;
            case 'serialBooks':
                $mes = '该书为套书，请拿到前台工作人员进行借阅操作';
                break;
            case 'books':
                $mes = '该书为套书，请拿到前台工作人员进行借阅操作';
                break;
            default:
                $mes = '';
        }
        return $mes;

    }

    /**
     * 扫码登录(旧)
     * @param array $array
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function oldSelfService(array $array): string
    {
        $reader = $this->getReader();
        if (!$reader) {
            return $this->goBindText();
        }
        if ($array['t'] == 2) {
            if (!Arr::has($array, 'd.serialNo')) {
                return '流水号为空';
            }
            $serialNo = $array['d']['serialNo'];
            $globalCode = $array['c'];
            $params = http_build_query([
                'serialNo' => $serialNo,
                'globalCode' => $globalCode
            ]);
            $http = new Client();
            $response = $http->get('http://services.dataesb.com:81/onecard/interface/serial/getData?' . $params);
            $response = json_decode((string)$response->getBody(), true);
            if (count($response['data']) === 0) {
                $time = (string)time();
                $params = [
                    'token' => $this->token,
                    'openid' => $this->openid,
                    'time' => $time,
                    'accesskey' => md5($this->openid . '$TC_2016@@WX' . $time),
                    'globalCode' => $array['c'],
                    'serialNo' => $array['d']['serialNo'],
                    'rdid' => $reader['rdid'],
                ];
                $response = $http->request('POST', 'http://services.dataesb.com:81/onecard/interface/serial/add', [
                    'json' => $params
                ]);
                $response = json_decode((string)$response->getBody(), true);
                if ($response['state'] === 1) {
                    return '登录成功';
                }
                return '系统繁忙,请稍后再试!';
            }
            return '二维码已失效,请刷新二维码重新扫描';
        }

    }

}
