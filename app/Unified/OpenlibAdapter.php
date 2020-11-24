<?php

namespace App\Unified;

use App\Services\AdapterResponse;
use App\Services\OpenlibService;
use Illuminate\Support\Arr;

/**
 * Openlib 适配器
 * Class OpenlibAdapter
 * @package App\Unified
 */
class OpenlibAdapter implements ReaderAdapter
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
            'is_cluster',
            'glc'
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

        if (empty($rdid) || empty($password)) {
            return $this->message('lack of parameter', false);
        }

        $OpenlibService = OpenlibService::make($this->config['token'], $this->config);
        //集群认证方式
        if ($this->config['is_cluster'] == 1) {
            $searchReader = $OpenlibService->searchreaderlist('rdid', $rdid, 1);
            if ($searchReader['success'] === false) {
                return $this->message('证号或密码不正确,请重新输入', false);
            }
            $pageData = $searchReader['pagedata'];
            $find = [];
            foreach ($pageData as $k => $v) {
                $readerPassword = $v['rdpasswd'];
                $samplePassword = $password;
                //判断查询的读者的密码是否进行 md5加密
                if (preg_match("/^([a-fA-F0-9]{32})$/", $readerPassword)) {
                    $samplePassword = md5($samplePassword);
                }
                //判断密码是否正确
                if ($readerPassword == $samplePassword) {
                    $find = $v;
                    break;
                }
            }
            if (empty($find)) {
                return $this->message('证号或密码不正确,请重新输入', false);
            }
            $searchReader = $find;
        } else {
            //非集群普通认证方式
            $response = $OpenlibService->confirmreader($rdid, $password, $params);
            if ($response['success'] == false) {
                return $this->message(Arr::get($response, 'messagelist.0.message'), false);
            }
            //验证完密码 进行读者状态跟卡号有效时间验证
            $searchReader = $OpenlibService->searchreader($params['rdid']);
        }

        //检查证件状态是否有效   状态10 (借阅宝办证)
        if ($searchReader['rdcfstate'] != 1 && $searchReader['rdcfstate'] != 10) {
            return $this->message('抱歉,该证不是有效状态,无法进行绑定!', false);
        }

        if ($searchReader['rdenddate'] < date('Y-m-d')) {
            return $this->message('抱歉,该证已过有效期,无法进行绑定!', false);
        }
        $success = [
            'name' => $searchReader['rdname'],
            'rdid' => $searchReader['rdid'],
            'origin_libcode' => $searchReader['rdlib'],
            'origin_glc' => Arr::get($searchReader, 'globalid', $this->config['glc']),  //全局馆(非必有参数)
            'is_cluster' => Arr::get($searchReader, 'iscluster', 0),                    //集群(非必有参数)
        ];
        $success = array_merge($searchReader, $success);
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
        $is_cluster = 0;
        if ($this->config['is_cluster'] === 1) {
            $is_cluster = $params['is_cluster'];
        }

        $OpenlibService = OpenlibService::make($this->config['token'], $this->config);
        $currentLoan = $OpenlibService->currentloan($rdid, $is_cluster);
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
