<?php

namespace App\Unified;

use App\Services\AdapterResponse;
use App\Services\OpenlibService;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;

/**
 * 统一用户适配器
 * Class UamAdapter
 * @package App\Unified
 */
class UamAdapter implements ReaderAdapter
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
            'openlib_url',
            'openlib_appid',
            'openlib_secret',
            'openlib_opuser',
            'token',
            'is_cluster'
        ];
    }


    /**
     * @param $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    public function certification($params)
    {
        $rdid = $params['rdid'];
        $password = $params['password'];
        $appcode = 'wmh';
        $static = 'wmh';
        $url = 'http://222.218.153.29:31580/onecard/api/reader/readerLogin?';
        $query = http_build_query([
            'rdId' => $rdid,
            'rdPassword' => md5($password),
            'appcode' => $appcode,
            'enc' => md5($appcode . date('Ymd') . $static)
        ]);
        $http = new Client();
        $response = $http->get($url . $query);
        $response = json_decode(json_encode(simplexml_load_string((string)$response->getBody())), true);
        if ($response['success'] != '1') {
            return $this->message($response['message'], false);
        }
        $success = [
            'name' => $response['rdName'],
            'rdid' => $response['rdId'],
            'origin_libcode' => $response['rdLib'], //开户馆
            'origin_glc' => $response['libraryId'], //开户馆
        ];
        $success = array_merge($response, $success);
        return $this->success($success, true);
    }

    /**
     * 读者信息查询
     * @param $params
     * @return array
     */
    public function searchUser($params)
    {
        $rdid = $params['rdid'];
        $IDCard = $params['IDCard'];
        if (empty($rdid) && empty($IDCard)) {
            return $this->message('lack of parameter', false);
        }

        $OpenlibService = OpenlibService::make($this->config['token'], $this->config);
        $searchReader = $OpenlibService->searchreader($rdid, $IDCard, $this->config['is_cluster']);
        if ($searchReader['success'] == false) {
            return $this->message(Arr::get($searchReader, 'messagelist.0.message'), false);
        }
        return $this->success($searchReader, true);
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

        $OpenlibService = OpenlibService::make($this->config['token'], $this->config);
        $currentLoan = $OpenlibService->currentloan($rdid, $this->config['is_cluster']);
        if ($currentLoan['success'] == false) {
            return $this->message(Arr::get($currentLoan, 'messagelist.0.message'), false);
        }
        //集群模式数据结构进行规范
        $success = $currentLoan['loanlist'];
        foreach ($success as $k => $v) {
            $success[$k]['author'] = Arr::get($v, 'author', '');  //作者数据
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
        $OpenlibService = OpenlibService::make($this->config['token'], $this->config);
        $startdate = Arr::get($params, 'startdate', '2017-01-01');
        $enddate = Arr::get($params, 'enddate', date('Y-m-d'));
        $page = Arr::get($params, 'page', 1);
        $rows = Arr::get($params, 'rows', 20);

        $historyloan = $OpenlibService->historyloan($rdid, $startdate, $enddate, $page, $rows);
        if ($historyloan['success'] == false) {
            return $this->message(Arr::get($historyloan, 'messagelist.0.message'), false);
        }
        unset($historyloan['success']);
        return $this->success($historyloan, true);
    }

    /**
     * 续借操作
     * @param $rdid
     * @param $barcode
     * @return array
     */
    public function renewbook($rdid, $barcode)
    {
        $OpenlibService = OpenlibService::make($this->config['token'], $this->config);
        $response = $OpenlibService->renewbook($rdid, $barcode);
        return $this->success($response['messagelist'], true);
    }

}
