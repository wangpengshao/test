<?php

namespace App\Http\Controllers\Api\Micro;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Reader;
use App\Models\Wxuser;
use App\Services\CoverService;
use App\Services\FansEvent;
use App\Unified\ReaderService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * 读者相关
 * Class ReaderController
 * @package App\Http\Controllers\Api\Micro
 */
class ReaderController extends Controller
{
    use ApiResponse;

    /**
     * @param Request $request
     * @return mixed
     * @throws \Matrix\Exception
     */
    public function readerInfo(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $rdid = Reader::checkBind($openid, $token)->value('rdid');
        if (empty($rdid)) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $wxuser = Wxuser::getCache($token);
        //非集群时候  判断授权方式是否是 openlib 默认返回 opac 类型数据
        if ($wxuser['is_cluster'] != 1 && $wxuser['auth_type'] == 1) {
            $wxuser->auth_type = 2;
        }

        $readerService = new ReaderService($wxuser);
        $response = $readerService->searchUser($rdid);

        if ($response['status'] === false) {
            return $this->failed($response['message'], 400);
        }

        $reader = $response['data'];
        unset($reader['rdpasswd']);
        return $this->success($reader, true);
    }

    /**
     * 读者当前借阅
     * @param Request $request
     * @return mixed
     * @throws \Matrix\Exception
     */
    public function currentLoan(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $reader = Reader::checkBind($openid, $token)->first(['rdid', 'is_cluster']);
        if (empty($reader)) {
            return $this->failed('尚未绑定读者证!', 401);
        }

        $wxuser = Wxuser::getCache($token);
        $readerService = new ReaderService($wxuser);
        $response = $readerService->getCurrentLoan($reader['rdid'], $reader->toArray());
        if ($response['status'] === false) {
            return $this->failed($response['message'], 400);
        }
        $currentLoan = $response['data'];

        $isbnImg = [];
        foreach ($currentLoan as $k => $v) {
            if (isset($v['isbn'])) {
                $isbnImg[$v['isbn']] = $v['isbn'];
            }
        }
        unset($k, $v);
        $isbnImg = CoverService::search($isbnImg);
        foreach ($currentLoan as $k => $v) {
            $currentLoan[$k]['imgurl'] = '';
            if (isset($v['isbn'])) {
                $currentLoan[$k]['imgurl'] = Arr::get($isbnImg, $v['isbn'], '');
            }
        }
        unset($k, $v);

        return $this->success($currentLoan);
    }

    /**
     * 读者历史借阅
     * @param Request $request
     * @return mixed
     * @throws \Matrix\Exception
     */
    public function historyLoan(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $rdid = Reader::checkBind($openid, $token)->value('rdid');
        if (empty($rdid)) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $wxuser = Wxuser::getCache($token);
        $readerService = new ReaderService($wxuser);
        $response = $readerService->getHistoryLoan($rdid, $request->only('startdate', 'enddate', 'page', 'rows'));
        if ($response['status'] === false) {
            return $this->failed($response['message'], 400);
        }
        $success = [
            'total' => $response['data']['total'],
            'startdate' => $request->input('startdate'),
            'enddate' => $request->input('enddate'),
            'page' => $request->input('page'),
            'rows' => $request->input('rows'),
            'list' => []
        ];
        $list = $response['data']['hloanlist'];
        if (count($list) > 0) {
            $isbnImg = [];
            foreach ($list as $k => $v) {
                if (isset($v['isbn'])) {
                    $isbnImg[$v['isbn']] = $v['isbn'];
                }
            }
            unset($k, $v);
            $isbnImg = CoverService::search($isbnImg);
            foreach ($list as $k => $v) {
                $list[$k]['imgurl'] = '';
                if (isset($v['isbn'])) {
                    $list[$k]['imgurl'] = Arr::get($isbnImg, $v['isbn'], '');
                }
            }
            $success['list'] = $list;
        }
        return $this->success($success, true);
    }


    /**
     * 续借操作
     * @param Request $request
     * @return mixed
     * @throws \Matrix\Exception
     */
    public function renewbook(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $rdid = Reader::checkBind($openid, $token)->value('rdid');
        if (empty($rdid)) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $barcode = $request->route('barcode') ?: '';
        $wxuser = Wxuser::getCache($token);
        $readerService = new ReaderService($wxuser);
        $response = $readerService->reNewBook($rdid, $barcode);
        $messagelist = $response['data'];
        $successMes = [];
        $failureMes = [];
        if (!empty($messagelist) && count($messagelist) > 0) {
            foreach ($messagelist as $k => $v) {
                if ($v['code'] == 'R00108') {
                    $successMes[] = $v['message'];
                    continue;
                }
                $failureMes[] = $v['message'];
            }
            unset($k, $v);
        }
        $response = ['successMes' => $successMes, 'failureMes' => $failureMes, 'typeData' => '', 'typeName' => ''];
        if (count($successMes) > 0) {
            $eventService = new FansEvent($token, $openid);
            $eventData = $eventService->check('readerRenewbook');
            if ($eventData) {
                $response = array_merge($response, $eventData);
            }
        }
        return $this->success($response, true);
    }

}
