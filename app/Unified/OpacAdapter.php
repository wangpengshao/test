<?php

namespace App\Unified;

use App\Services\AdapterResponse;
use App\Services\OpacService;
use App\Services\OpacSoap;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Opac 适配器
 * Class OpacAdapter
 * @package App\Unified
 */
class OpacAdapter implements ReaderAdapter
{
    use AdapterResponse;
    /**
     * @var
     */
    private $config;

    /**
     * @return array
     */
    public function mustParams(): array
    {
        return [
            'opacurl',
            'token',
            'glc',
            'libcode',
            'opackey'
        ];
    }

    /**
     * @param $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * 读者认证
     * @param $params
     * @return array
     */
    public function certification($params)
    {
        $rdid = $params['rdid'];
        $password = $params['password'];
        $time = date('YmdHis');
        $libcode = $this->config['libcode'];
        $opackey = $this->config['opackey'];
        $sn = md5($rdid . $opackey . $password . $time);
        $query = http_build_query([
            'uid' => $rdid,
            'sn' => $sn,
            'time' => $time,
            'libcode' => $libcode,
            'password' => $password
        ]);
        $url = $this->config['opacurl'] . '/reader/validationUser?' . $query;
        $response = OpacService::request($url);
        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->message($response['errMes'], false);
        }
        $response = json_decode($response, true);
        if ($response['code'] === 1) {
            $success = [
                'name' => $response['displayname'],
                'rdid' => $response['rdid'],
            ];
            return $this->success($success, true);
        }

        if (Arr::has($response, 'msg')) {
            return $this->message(Arr::get($response, 'msg'), false);
        }
        return $this->message('绑定失败,请检查读者证号密码是否正确！', false);
    }

    /**
     * 查询读者
     * @param $params
     * @return array
     */
    public function searchUser($params)
    {
        $rdid = $params['rdid'];
        if (empty($rdid)) {
            return $this->message('lack of parameter', false);
        }

        if (config('app.env') == 'local') {
            $this->config['opacurl'] = 'http://j.tcsoft.info:28080/opac/';
            $rdid = 'y1';
        }

        $opacSoap = OpacSoap::make($this->config, 'webservice/readerWebservice');
        $arguments = [
            'rdid' => $rdid,
            'key' => 'TCSOFT_INTERLIB'
        ];
        $response = $opacSoap->requestFunction('getReader', $arguments);
        if (Arr::get($response, 'errMes')) {                        //...异常处理
            return $this->message(Arr::get($response, 'errMes'), false);
        }
        if (empty($response)) return $this->message('抱歉,没有找到读者数据!', false);
        $response = $response['return'];
        $success = array_change_key_case($response);
        //有用到的数据，返回字段统一以openlib标准h
        $success['deposit'] = $success['rddeposit'];   //押金
        $success['rdstartdate'] = date('Y-m-d', strtotime($success['startdate']));   //启用时间
        $success['rdenddate'] = date('Y-m-d', strtotime($success['enddate']));       //终止时间

        return $this->success($success, true);
    }

    /**
     * 读者当前借阅
     * @param $params
     * @return array
     */
    public function currentLoan($params)
    {
        $rdid = $params['rdid'];
        if (empty($rdid)) {
            return $this->message('lack of parameter', false);
        }

        if (config('app.env') == 'local') {
            $this->config['opacurl'] = 'http://j.tcsoft.info:28080/opac/';
            $rdid = 'y1';
        }
        $response = $this->searchUser($params);
        if ($response['status'] == false) {
            return $this->message($response['message'], false);
        }
        $arguments = [
            'rdid' => $rdid,
            'password' => $response['data']['rdpasswd'],
            'doPage' => true,
            'toPage' => 1,
            'pageSize' => 200
        ];
        $opacSoap = OpacSoap::make($this->config, 'webservice/loanWebservice');
        $response = $opacSoap->requestFunction('getCurrentLoanList', $arguments);
        if (empty($response)) {
            return $this->success([], true);
        }
        $currentLoan = $response['return'];
        $success = [];
        if (isset($currentLoan['biblios'])) {
            //只有一条数据的时候
            $success[] = [
                'author' => Arr::get($currentLoan, 'biblios.author', ''),
                'isbn' => Arr::get($currentLoan, 'biblios.isbn', ''),
                'bookrecno' => $currentLoan['biblios']['bookrecno'],
                'title' => Arr::get($currentLoan, 'biblios.title', ''),
                'returndate' => date('Y-m-d', strtotime($currentLoan["returnDate"])),
                'barcode' => $currentLoan["holding"]["barcode"],
                'rewnewcount' => $currentLoan["loanCount"]
            ];
        } else {
            foreach ($currentLoan as $k => $v) {
                $success[] = [
                    'author' => Arr::get($v, 'biblios.author', ''),
                    'isbn' => Arr::get($v, 'biblios.isbn', ''),
                    'bookrecno' => $v['biblios']['bookrecno'],
                    'title' => Arr::get($v, 'biblios.title', ''),
                    'returndate' => date('Y-m-d', strtotime($v["returnDate"])),
                    'barcode' => $v["holding"]["barcode"],
                    'rewnewcount' => $v["loanCount"]
                ];
            }
        }
        return $this->success($success, true);
    }

    /**
     * 读者历史借阅
     * @param $params
     * @return array
     */
    public function historyLoan($params)
    {
        $rdid = $params['rdid'];
        if (empty($rdid)) {
            return $this->message('lack of parameter', false);
        }

        if (config('app.env') == 'local') {
            $this->config['opacurl'] = 'http://j.tcsoft.info:28080/opac/';
            $rdid = 'y1';
        }

        $response = $this->searchUser($params);
        if ($response['status'] == false) {
            return $this->message($response['message'], false);
        }
        $page = Arr::get($params, 'page', 1);
        $rows = Arr::get($params, 'rows', 20);

        $arguments = [
            'rdid' => $rdid,
            'password' => $response['data']['rdpasswd'],
            'doPage' => true,
            'toPage' => $page,
            'pageSize' => $rows
        ];
        $opacSoap = OpacSoap::make($this->config, 'webservice/loanWebservice');
        $response = $opacSoap->requestFunction('getHistoryLoanList', $arguments);

        $success = ['total' => 0, 'hloanlist' => []];
        if (empty($response)) {
            return $this->success($success, true);
        }
        $currentLoan = $response['return'];
        $hloanlist = [];
        if (isset($currentLoan['biblios'])) {
            //只有一条数据的时候
            $hloanlist[] = [
                'author' => Arr::get($currentLoan, 'biblios.author', ''),
                'isbn' => Arr::get($currentLoan, 'biblios.isbn', ''),
                'bookrecno' => $currentLoan['biblios']['bookrecno'],
                'title' => Arr::get($currentLoan, 'biblios.title', ''),
                'optime' => date('Y-m-d H:i:s', strtotime($currentLoan['regTime'])),
                'barcode' => $currentLoan["holding"]["barcode"],
                'logtype' => $currentLoan["logType"]
            ];
        } else {
            foreach ($currentLoan as $k => $v) {
                $hloanlist[] = [
                    'author' => Arr::get($v, 'biblios.author', ''),
                    'isbn' => Arr::get($v, 'biblios.isbn', ''),
                    'bookrecno' => $v['biblios']['bookrecno'],
                    'title' => Arr::get($v, 'biblios.title', ''),
                    'optime' => date('Y-m-d H:i:s', strtotime($v['regTime'])),
                    'barcode' => $v["holding"]["barcode"],
                    'logtype' => $v["logType"]
                ];
            }
        }
        $success['hloanlist'] = $hloanlist;
        $success['total'] = count($hloanlist);
        return $this->success($success, true);
    }

    /**
     * 续借操作
     * @param $rdid
     * @param $barcode
     * @return array
     */
    public function renewbook($rdid, $barcode)
    {
        if (config('app.env') == 'local') {
            $this->config['opacurl'] = 'http://j.tcsoft.info:28080/opac/';
            $rdid = 'y1';
        }

        $response = $this->searchUser(['rdid' => $rdid]);
        if ($response['status'] == false) {
            return $this->message($response['message'], false);
        }
        $params = http_build_query([
            'referer' => 'wx',
            'rdid' => $rdid,
            'password' => md5($response['data']['rdpasswd']),
            'barcode' => $barcode
        ]);
        $url = $this->config['opacurl'] . 'interface/loan/renew?' . $params;
        $xmlResponse = OpacService::request($url);
        $jsonResponse = json_encode(simplexml_load_string($xmlResponse)->messages);
        $response = json_decode($jsonResponse, true);
        $message = implode(',', $response);

        $code = 'R00100';
        if (!Str::contains($message, '失败')) {
            $code = 'R00108';
        }
        $success = [
            0 => [
                'message' => $message,
                'code' => $code
            ]
        ];
        return $this->success($success, true);
    }
}
