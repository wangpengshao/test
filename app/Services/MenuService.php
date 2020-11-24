<?php

namespace App\Services;

use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use App\Unified\ReaderService;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Class MenuService
 * @package App\Services
 */
class MenuService
{
    /**
     * @var
     */
    protected static $instance;
    /**
     * @var string
     */
    protected $enKey = '376B4A409E5789CE';
    /**
     * @var string
     */
    protected $jybEnKey = 'B3389BF3A96F50C3F3';
    /**
     * @var
     */
    protected $wxuser;
    /**
     * @var
     */
    protected $Des;
    /**
     * @var
     */
    protected $JybDes;
    /**
     * @var
     */
    protected $timeData;
    /**
     * @var
     */
    protected $token;
    /**
     * @var
     */
    protected $vueUrl;
    /**
     * @var
     */
    protected $nonce;
    /**
     * @var
     */
    protected $uuid;
    /**
     * @var
     */
    protected $reader;

    /**
     * @param string $instance
     * @param        $token
     * @return MenuService
     */
    public static function make($instance = "self", $token)
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }
        self::$instance = new static();
        return self::$instance->initData($token);
    }

    /**
     * @param $token
     * @return $this
     */
    protected function initData($token)
    {
        $this->token = $token;
        $this->wxuser = Wxuser::getCache($token);
        $this->Des = new Des();
        $this->JybDes = new JybDes();
        $this->initTime();
        $this->initNonce();
        $this->initUuid();
        $this->initVueUrl($token);
        return $this;
    }

    public function returnUrl($indexMenu, $user = [], $type = '')
    {
        /************   初始化数据    *************/
        $params = [];
        $token = $this->token;
        $wxuser = $this->wxuser;
        $relevance = $indexMenu->relevance;
        // 添加粉丝信息
        $wxuser['openid'] = $user['openid'];
        $wxuser['headimgurl'] = $user['headimgurl'];
        $wxuser['nickname'] = $user['nickname'];

        $getField = [
            'need_bind', 'token', 'add_info', 'add_rdid', 'add_libcode', 'add_glc', 'signKey', 'rdid_str',
            'libcode_str', 'glc_str', 'en_type', 'extra', 'url', 'custom_url', 'id'
        ];
        if (!empty($relevance)) {
            $indexMenu = $relevance->only($getField);
        } else {
            $indexMenu = $indexMenu->only($getField);
        }
        //替换链接
        if ($indexMenu['custom_url'] && empty($indexMenu['url'])) {
            //判断是否是内部特殊链接
            if (Arr::get($this->vueUrl, $indexMenu['custom_url'])) {
                $indexMenu['url'] = Arr::get($this->vueUrl, $indexMenu['custom_url']);
            } else {
                $custom_url = explode(',', $indexMenu['custom_url']);
                $indexMenu['url'] = Arr::get($wxuser, $custom_url[0]) . Arr::get($custom_url, 1);
            }
        }
        /************   初始化key    *************/
        $rdidKey = $indexMenu['rdid_str'] ?: 'rdid';
        $libcodeKey = '';
        $glcKey = '';

        /************   初始化参数    *************/
        if ($indexMenu['need_bind'] == 1 || $indexMenu['add_rdid'] == 1) {
//            $reader = $this->readerModel->checkBind($user['openid'], $token)
            $reader = Reader::checkBind($user['openid'], $token)
                ->first(['rdid', 'name', 'origin_libcode', 'origin_glc']);

            if ($reader) {
                $this->reader = $reader;
            }

            if ($indexMenu['need_bind'] == 1) {
                if (empty($reader)) {
                    if ($type == 'prompt') {
                        return 1;
                    }
                    //返回绑定的链接，并且附带菜单id
                    $bindUrl = config('vueRoute.bindReader');
                    $bindUrl = str_replace('{token}', $token, $bindUrl);
                    return $bindUrl . '?id=' . $indexMenu['id'];
                }
            }
            if ($indexMenu['add_rdid'] == 1 && Arr::has($reader, 'rdid')) {
                $this->wxuser->origin_libcode = $reader->origin_libcode;
                $this->wxuser->origin_glc = $reader->origin_glc;
                $params[$rdidKey] = $reader->rdid;
                $params['unload_rdid'] = $reader->rdid;
                $params['name'] = $reader->name;
                //是否集群
                if ($wxuser['is_cluster'] == 1) {
                    $this->wxuser->libcode = $reader->origin_libcode;
                    $this->wxuser->glc = $reader->origin_glc;
                }
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
            $des = $this->Des;
            if (Arr::get($params, $rdidKey)) {
                $key = substr($wxuser['glc'] . '00000000', 0, 8);
                $key .= $key;
                $params[$rdidKey] = $des->encrypt($params[$rdidKey], $key);
                if ($glcKey) {
                    $params[$glcKey] = $des->encrypt($wxuser['glc'], $this->enKey);
                }
            }
            if (Arr::get($params, $glcKey) && !Arr::get($params, 'rdid')) {
                $params[$glcKey] = $des->encrypt($wxuser['glc'], $this->enKey);
            }
            if (Arr::get($params, $libcodeKey)) {
                $params[$libcodeKey] = $des->encrypt($wxuser['libcode'], $this->enKey);
            }
        }

        if ($indexMenu['en_type'] == 2) {
            $des = $this->JybDes;
            if (Arr::get($params, $rdidKey)) {
                $key = substr($wxuser['glc'] . '00000000', 0, 8);
                $key .= $key;
                $params[$rdidKey] = $des->encrypt($params[$rdidKey], $key);
                if ($glcKey) {
                    $params[$glcKey] = $des->encrypt($wxuser['glc'], $this->jybEnKey);
                }
            }

            if (Arr::get($params, $glcKey) && !Arr::get($params, $rdidKey)) {
                $params[$glcKey] = $des->encrypt($wxuser['glc'], $this->jybEnKey);
            }

            if (Arr::get($params, $libcodeKey)) {
                $params[$libcodeKey] = $des->encrypt($wxuser['libcode'], $this->jybEnKey);
            }
        }
        /************   特殊处理    *************/
        $params = $this->initExtra($params, $indexMenu['extra']);

        /************   粉丝信息    *************/
        if (Arr::get($indexMenu, 'add_info.0')) {
            $params += Arr::only($user, $indexMenu['add_info']);
        }

        if (count($params) > 0) {
            $params = Arr::except($params, ['unload_rdid']);

            $key = config('envCommon.MENU_ENCRYPT_STR');
            $timestamp = time();
            $signKey = $indexMenu['signKey'];
            $sign = md5($key . $timestamp . $token . $signKey);
            $params += [
                'uweiTime' => $timestamp, 'uweiToken' => $token, 'uweiSign' => $sign
            ];
            $params = http_build_query($params);
            $url = $indexMenu['url'];

            $contains = Str::contains($url, '?');
            if ($contains) {
                $url = Str::endsWith($url, '?') ? $url : $url . '&';
            } else {
                $url = Str::finish($url, '?');
            }
            $indexMenu['url'] = $url . $params;

        }
        $indexMenu['url'] = str_replace('{token}', $token, $indexMenu['url']);
        return $indexMenu['url'];
    }

    /**
     * @param $params
     * @param $extra
     * @return mixed
     */
    protected function initExtra($params, $extra)
    {
        $wxuser = $this->wxuser->toArray();
        $wxuser['rdid'] = Arr::get($params, 'unload_rdid');
        $wxuser = array_merge($wxuser, $this->vueUrl, $this->timeData, $this->nonce, $this->uuid);

        for ($a = 1; $a <= 4; $a++) {
            $paramsKey = Arr::get($extra, 'text' . $a);
            //键名存在进行数据组装
            if ($paramsKey) {
                $data = $extra['data' . $a];
                $source = Arr::get($extra, 'source' . $a);
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

    protected function initVueUrl($token)
    {
        $vueRoute = config('vueRoute');
        $array = [];
        $array['bind_url'] = str_replace('{token}', $token, $vueRoute['bindReader']);
        foreach ($vueRoute as $k => $v) {
            $array[$k . '_url'] = str_replace('{token}', $token, $v);
        }
        $this->vueUrl = $array;
    }

    /**
     * @param      $str
     * @param      $type
     * @param null $key
     * @return string
     */
    protected function encryptCaster($str, $type, $key = null)
    {
        if (!$type) return $str;
        switch ($type) {
            case 1:
                $key = $key ?: $this->enKey;
                $str = $this->Des->encrypt($str, $key);
                return $str;
                break;
            case 2:
                $key = $key ?: $this->jybEnKey;
                $str = $this->JybDes->encrypt($str, $key);
                return $str;
                break;
            case 3:
                return md5($key);
                break;
            case 4:
                return strtoupper(md5($key));
                break;
            case 5:
                return hash('sha256', $key);
                break;
        }
    }

    protected function initTime()
    {
        $time10 = time();
        $sss = substr(microtime(), 2, 3);
        $time13 = $time10 . $sss;
        $date = date('YmdHis', $time10);
        $today = date('Y-m-d', $time10);
        $year = substr($date, 0, 4);
        $month = substr($date, 4, 2);
        $day = substr($date, 6, 2);
        $hour = substr($date, 8, 2);
        $minute = substr($date, 10, 2);
        $second = substr($date, 12, 2);
        $date6 = $year . $month;
        $date8 = $year . $month . $day;
        $date10 = $year . $month . $day . $hour;

        $this->timeData = [
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
            'today' => $today
        ];

    }


    protected function initNonce()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = "";
        for ($i = 0; $i < 32; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        $this->nonce = [
            'nonce32' => $str,
        ];
    }


    protected function initUuid()
    {
        $this->uuid = [
            'uuid' => (string)Str::uuid()
        ];
    }

    protected function replaceData($data, $rawData)
    {
        if ($data == '{jssdk_ticket}') {
            $app = Wechatapp::initialize($this->token);
            $jssdk = $app->jssdk->getTicket();
            return $jssdk['ticket'];
        }
        if ($data == '{uwei_password}') {
            if ($this->reader) {
                $readerService = new ReaderService($this->wxuser, 2);
                $response = $readerService->searchUser($this->reader->rdid);
                if ($response['status'] === true) {
                    return $response['data']['rdpasswd'];
                }
            }
            return 'null';
        }

        $a = [];
        preg_match_all('/(?<=\{)[^\}]+/', $data, $a);

        if (empty($a[0])) {
            return $data;
        }
        $arr = $a[0];
        foreach ($arr as $v) {
            $str = Arr::get($rawData, $v);
            if ($str) {
                $data = str_replace('{' . $v . '}', $str, $data);
            } elseif ($v == 'libcode' && empty($str)) {
                //兼容分馆代码为空的情况
                $data = str_replace('{libcode}', '', $data);
            }
        }
        unset($rawData, $v);
        return $data;
    }

}
