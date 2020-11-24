<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use App\Services\Des;
use App\Services\JybDes;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

/**
 * Class BaseController
 *
 * @package App\Http\Controllers\Api
 */
class BaseController extends Controller
{
    // 其他通用的Api帮助函数
    use ApiResponse;

    /**
     * @var string
     */
    protected $enKey = '376B4A409E5789CE';
    /**
     * @var string
     */
    protected $jybEnKey = 'B3389BF3A96F50C3F3';


    /**
     * @param     $indexMenu
     * @param     $user
     * @param int $type
     *
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function returnMenuUrl($indexMenu, $user, $type = 0)
    {
        /************   初始化数据    *************/
        $params = [];
        $token = $user['token'];
        $wxuser = $this->getWxuserCache($token);
        $relevance = $indexMenu->relevance;
        $getField = [
            'need_bind', 'token', 'add_info', 'add_rdid', 'add_libcode', 'add_glc', 'signKey', 'rdid_str',
            'libcode_str', 'glc_str', 'en_type', 'extra', 'url', 'custom_url'
        ];

        if (!empty($relevance)) {
            $indexMenu = $relevance->only($getField);
        } else {
            $indexMenu = $indexMenu->only($getField);
        }
        //替换链接
        if ($indexMenu['custom_url'] && empty($indexMenu['url'])) {
            $custom_url = explode(',', $indexMenu['custom_url']);
            $indexMenu['url'] = array_get($wxuser, $custom_url[0]) . array_get($custom_url, 1);
        }

        /************   初始化key    *************/
        $rdidKey = $indexMenu['rdid_str'] ?: 'rdid';
        $libcodeKey = '';
        $glcKey = '';

        /************   初始化参数    *************/
        if ($indexMenu['need_bind'] == 1 || $indexMenu['add_rdid'] == 1) {
            $reader = Reader::userGetBind($user)->first(['rdid']);
            if ($indexMenu['need_bind'] == 1) {
                if (empty($reader)) {
                    return $this->failed('需要绑定才能访问!!', 401, false);
                }
            }
            if ($indexMenu['add_rdid'] == 1) {
                $params[$rdidKey] = array_get($reader, 'rdid');
                $params['unload_rdid'] = array_get($reader, 'rdid');
            }
        }

        if ($indexMenu['add_libcode'] == 1) {
            $libcodeKey = $indexMenu['libcode_str'] ?: 'libcode';
            $params[$libcodeKey] = $wxuser['libcode'];
        }

        if ($indexMenu['add_glc'] == 1) {
            $glcKey = $indexMenu['glc_str'] ?: 'glc';
            $params[$glcKey] = $wxuser['glc'];
        }
        /************   加密处理    *************/
        if ($indexMenu['en_type'] == 1) {
            $des = new Des();
            if (array_get($params, $rdidKey)) {
                $key = substr($wxuser['glc'] . '00000000', 0, 8);
                $key .= $key;
                $params[$rdidKey] = $des->encrypt($params[$rdidKey], $key);
                if ($glcKey) {
                    $params[$glcKey] = $des->encrypt($wxuser['glc'], $this->enKey);
                }
            }
            if (array_get($params, $glcKey) && !array_get($params, 'rdid')) {
                $params[$glcKey] = $des->encrypt($wxuser['glc'], $this->enKey);
            }
            if (array_get($params, $libcodeKey)) {
                $params[$libcodeKey] = $des->encrypt($wxuser['libcode'], $this->enKey);
            }
        }

        if ($indexMenu['en_type'] == 2) {
            $des = new JybDes();
            if (array_get($params, $rdidKey)) {
                $key = substr($wxuser['glc'] . '00000000', 0, 8);
                $key .= $key;
                $params[$rdidKey] = $des->encrypt($params[$rdidKey], $key);
                if ($glcKey) {
                    $params[$glcKey] = $des->encrypt($wxuser['glc'], $this->jybEnKey);
                }
            }

            if (array_get($params, $glcKey) && !array_get($params, $rdidKey)) {
                $params[$glcKey] = $des->encrypt($wxuser['glc'], $this->jybEnKey);
            }

            if (array_get($params, $libcodeKey)) {
                $params[$libcodeKey] = $des->encrypt($wxuser['libcode'], $this->jybEnKey);
            }
        }
        /************   特殊处理    *************/
        $params = $this->initExtra($params, $indexMenu['extra'], $wxuser);

        /************   粉丝信息    *************/
        if (array_get($indexMenu, 'add_info.0')) {
            $params += $user->only($indexMenu['add_info']);
        }

        if (count($params) > 0) {
            $params = array_except($params, ['unload_rdid']);

            $key = config('envCommon.MENU_ENCRYPT_STR');
            $timestamp = time();
            $signKey = $indexMenu['signKey'];
            $sign = md5($key . $timestamp . $token . $signKey);
            $params += [
                'time' => $timestamp, 'token' => $token, 'sign' => $sign
            ];
            $params = http_build_query($params);
            $url = $indexMenu['url'];

            $contains = str_contains($url, '?');
            $url = ($contains) ? str_finish($url, '&') : str_finish($url, '?');
            $indexMenu['url'] = $url . $params;

        }
        $indexMenu['url'] = str_replace('{token}', $token, $indexMenu['url']);
        if ($type == 1) {
            return $indexMenu['url'];
        }
        return $this->success(['url' => $indexMenu['url']]);
    }


    /**
     * @param       $request
     * @param array $field
     * @param int $isbind
     *
     * @return mixed
     */
    public function firstBind($request, $field = [], $isbind = 1)
    {
        $where = [
            'token' => $request->user()->token,
            'openid' => $request->user()->openid,
            'is_bind' => $isbind
        ];
        return (count($field) > 0) ? Reader::where($where)->first($field) : Reader::where($where)->first();
    }

    //获取书本封面

    /**
     * @param array $isbnImg
     *
     * @return array
     */
    public function giveImgApi(array $isbnImg)
    {
        $isbnImg = array_filter($isbnImg);   //除空
        $isbnImg = array_unique($isbnImg);   //去重
        $isbns = implode(',', $isbnImg);     //拼接
        $params = http_build_query([
            'glc' => '',
            'cmdACT' => 'getImages',
            'type' => 0
        ]);
        //旧版封面接口
//        $url = 'https://api.interlib.com.cn/interlibopac/websearch/metares?' . $params . '&isbns=' . $isbns;
        //新版封面接口
        $url = 'https://book-resource.dataesb.com/websearch/metares?' . $params . '&isbns=' . $isbns;
        $http = new Client();
        $response = $http->get($url);
        $response = (string)$response->getBody();
        $response = str_replace_first('(', '', $response);
        $response = str_replace_last(')', '', $response);
//        $response = json_decode($response, true)['result'];
        $response = Arr::get(json_decode($response, true), 'result');
        $replyData = [];
        if ($response) {
            foreach ($response as $k => $v) {
                foreach ($isbnImg as $key => $val) {
                    if ($v['isbn'] == $val) {
                        $replyData[$key] = $v['coverlink'];
                    }
                }
                unset($key, $val);
            }
            unset($k, $v);
        }
        return $replyData;
    }

    /**
     * @param $token
     *
     * @return mixed
     */
    public function getWxuserCache($token)
    {
        $wxuser = Wxuser::getCache($token);
        return $wxuser;
    }


    /**
     * @param $params
     * @param $extra
     * @param $wxuser
     *
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function initExtra($params, $extra, $wxuser)
    {
        $wxuser = $wxuser->toArray();
        $wxuser['rdid'] = array_get($params, 'unload_rdid');
        $wxuser = array_merge($wxuser, $this->initVueUrl($wxuser['token']), $this->initTime());

        for ($a = 1; $a <= 4; $a++) {
            $paramsKey = array_get($extra, 'text' . $a);
            //键名存在进行数据组装
            if ($paramsKey) {
                $data = $extra['data' . $a];
                $source = array_get($extra, 'source' . $a);
                $enType = $extra['enType' . $a];
                $enKey = $extra['enKey' . $a];
                if ($source) {
                    $data = $wxuser[$source];
                }
                if ($data) {
                    $data = $this->replaceData($data, $wxuser);
                }
                if ($enKey) {
                    $enKey = $this->replaceData($enKey, $wxuser);
                }
                $params[$paramsKey] = $this->encryptCaster($data, $enType, $enKey);
            }
        }
        return $params;
    }

    /**
     * @param $token
     *
     * @return array
     */
    protected function initVueUrl($token)
    {
        $indexUrl = config('vueRoute.index');
        $indexUrl = str_replace('{token}', $token, $indexUrl);

        $bindUrl = config('vueRoute.bindReader');
        $bindUrl = str_replace('{token}', $token, $bindUrl);
        return [
            'index_url' => $indexUrl,
            'bind_url' => $bindUrl,
        ];
    }


    /**
     * @param      $str
     * @param      $type
     * @param null $key
     *
     * @return string
     */
    protected function encryptCaster($str, $type, $key = null)
    {
        if (!$type) return $str;
        switch ($type) {
            case 1:
                $des = new Des();
                $key = $key ?: $this->enKey;
                $str = $des->encrypt($str, $key);
                return $str;
                break;
            case 2:
                $des = new JybDes();
                $key = $key ?: $this->jybEnKey;
                $str = $des->encrypt($str, $key);
                return $str;
                break;
            case 3:
                return md5($key);
                break;
            case 4:
                return strtoupper(md5($key));
                break;
        }
    }

    /**
     * @return array
     */
    protected function initTime()
    {
        $time10 = time();
        $sss = substr(microtime(), 2, 3);
        $time13 = $time10 . $sss;
        $date = date('YmdHis', $time10);
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);
        $hour = substr($date, 8, 2);
        $minute = substr($date, 10, 2);
        $second = substr($date, 12, 2);
        $date6 = $year . $month;
        $date8 = $year . $month . $day;
        $date10 = $year . $month . $day . $hour;
        return [
            'time10' => $time10,
            'time13' => $time13,
            'date' => $date,
            'date10' => $date10,
            'date8' => $date8,
            'date6' => $date6,
            'date.year' => $year,
            'date.month' => $month,
            'date.day' => $day,
            'date.hour' => $hour,
            'date.minute' => $minute,
            'date.second' => $second,
        ];

    }

    /**
     * @param $data
     * @param $rawData
     *
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function replaceData($data, $rawData)
    {
        if ($data == '{jssdk_ticket}') {
            $app = Wechatapp::initialize($rawData['token']);
            $jssdk = $app->jssdk->getTicket();
            return $jssdk['ticket'];
        }
        $a = [];
        preg_match_all('/(?<=\{)[^\}]+/', $data, $a);

        if (empty($a[0])) {
            return $data;
        }
        $arr = $a[0];
        foreach ($arr as $v) {
            $str = array_get($rawData, $v);
            if ($str) {
                $data = str_replace('{' . $v . '}', $str, $data);
            }
        }
        unset($rawData, $v);
        return $data;
    }


}
