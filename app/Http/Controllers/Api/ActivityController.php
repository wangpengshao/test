<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\ActContent;
use App\Models\Wechat\Reader;
use App\Models\Wechat\ReaderToMany;
use App\Models\Wxuser;
use App\Services\ActivityApi;
use App\Services\FansEvent;
use App\Services\OpenlibService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ActivityController extends Controller
{
    use ApiResponse;

    public function activityCard(Request $request)
    {
        $token = $request->input('token');
        $openid = $request->input('openid');
        $page = $request->input('page', 1);
        $rows = $request->input('rows', 10);

        $actCollect = [];
        $offset = ($page - 1) * $rows;
        $params = [
            'cmd' => 'listNewActions',
            'offset' => $offset,
            'rows' => $rows
        ];
        $wxuser = Wxuser::getCache($token);

        if (!empty($wxuser['libcode'])) {
            $params['libcode'] = $wxuser['libcode'];
        }
        $activityApi = ActivityApi::make($wxuser['activity_url'], $token);

        $response = $activityApi->request('mb/mobileApi', $params);
        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }

        $data = Arr::get($response, 'data');
        if (count($data) > 0) {
            if ($openid) {
                $reader = Reader::checkBind($openid, $token)->first();
                if ($reader != false) {
                    $actCollect = ReaderToMany::findReader($reader)->value('act_collect');
                }
            }
            foreach ($data as $k => $v) {
                $data[$k]['play_bill_pic'] = $activityApi->completionCover($v['play_bill_pic']);
                $data[$k]['is_collect'] = 0;
                if (isset($actCollect) && count($actCollect) > 0 && in_array($v['id'], $actCollect)) {
                    $data[$k]['is_collect'] = 1;
                }
            }
            return $this->success($data, true);
        }
        return $this->success($data, false);
    }

    public function activityList(Request $request)
    {
        $page = $request->input('page', 1);
        $rows = $request->input('rows', 10);
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $offset = ($page - 1) * $rows;
        $params = [
            'cmd' => 'listAllActions',
            'offset' => $offset,
            'rows' => $rows
        ];
        $wxuser = Wxuser::getCache($token);
        if (!empty($wxuser['libcode'])) {
            $params['libcode'] = $wxuser['libcode'];
        }

        $activityApi = ActivityApi::make($wxuser['activity_url'], $token);
        $response = $activityApi->request('mb/mobileApi', $params);
        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }

        $data = $response['data'];
        if (count($data) > 0) {
            $reader = Reader::checkBind($openid, $token)->first();
            if ($reader != false) {
                $actCollect = ReaderToMany::findReader($reader)->value('act_collect');
            }
            foreach ($data as $k => $v) {
                $data[$k]['play_bill_pic'] = $activityApi->completionCover($v['play_bill_pic']);
                $data[$k]['is_collect'] = 0;
                if (isset($actCollect) && count($actCollect) > 0 && in_array($v['id'], $actCollect)) {
                    $data[$k]['is_collect'] = 1;
                }
            }
            $response['data'] = $data;
            return $this->success($response, true);
        }
        return $this->success($data, false);
    }

    public function getDetails(Request $request)
    {
        if (!$request->filled(['token', 'openid'])) {
            return $this->failed('缺少必带参数!!', 400);
        }
        $id = $request->route('id');
        $token = $request->input('token');
        $openid = $request->input('openid');

        $params = [
            'cmd' => 'viewAction',
            'id' => $id
        ];
        $wxuser = Wxuser::getCache($token);
        $activityApi = ActivityApi::make($wxuser['activity_url'], $token);
        $response = $activityApi->request('mb/mobileApi', $params);
        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        if (Arr::get($response, 'id')) {
            $response['play_bill_pic'] = $activityApi->completionCover($response['play_bill_pic']);
            $response['is_collect'] = 0;
            if ($openid) {
                $reader = Reader::checkBind($openid, $token)->first();
                $readerToMany = ReaderToMany::findReader($reader)->first();
                if (!empty($readerToMany) && in_array($response['id'], $readerToMany['act_collect'])) {
                    $response['is_collect'] = 1;
                }
            }
            return $this->success($response, true);
        }
        return $this->failed(Arr::get($response, 'message'), 400);
    }

    public function searchList(Request $request)
    {
        $page = $request->input('page', 1);
        $rows = $request->input('rows', 10);
        $offset = ($page - 1) * $rows;
        $params = [
            'cmd' => 'listSearchActions',
            'offset' => $offset,
            'rows' => $rows
        ];
        $token = $request->user()->token;
        $openid = $request->user()->openid;

        $wxuser = Wxuser::getCache($token);

        if (!empty($wxuser['libcode'])) {
            $params['libcode'] = $wxuser['libcode'];
        }
        $activityApi = ActivityApi::make($wxuser['activity_url'], $token);

        if ($request->filled(['categoryId', 'state'])) {
            $params += $request->only(['categoryId', 'state']);
        }
        $response = $activityApi->request('mb/mobileApi', $params);
        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $data = $response['data'];
        if (count($data) > 0) {
            $reader = Reader::checkBind($openid, $token)->first();
            if ($reader != false) {
                $actCollect = ReaderToMany::findReader($reader)->value('act_collect');
            }
            foreach ($data as $k => $v) {
                $data[$k]['play_bill_pic'] = $activityApi->completionCover($v['play_bill_pic']);
                $data[$k]['is_collect'] = 0;
                if (isset($actCollect) && count($actCollect) > 0 && in_array($v['id'], $actCollect)) {
                    $data[$k]['is_collect'] = 1;
                }
            }
            $response['data'] = $data;
            return $this->success($response, true);
        }
        return $this->success($data, false);
    }

    public function getCategory(Request $request)
    {
        $token = $request->input('token');
        $wxuser = Wxuser::getCache($token);
        $params = [
            'cmd' => 'listCategory',
            'libcode' => $wxuser['libcode'],
        ];
        $activityApi = ActivityApi::make($wxuser['activity_url'], $token);
        $response = $activityApi->request('mb/mobileApi', $params);
        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $stateList = [
            'stateList' => [
                ['val' => 1, 'name' => '发布'],
                ['val' => 2, 'name' => '签到'],
                ['val' => 3, 'name' => '评分'],
                ['val' => 4, 'name' => '结束'],
                ['val' => 5, 'name' => '进行']
            ]
        ];
        $category = ['category' => []];
        if (count($response['category']) > 0) {
            foreach ($response['category'] as $k => $v) {
                if (count($v['child_category']) > 0) {
                    foreach ($v['child_category'] as $key => $val) {
                        $category['category'][] = ['val' => $val['c_id'], 'name' => $val['c_name']];
                    }
                    unset($key, $val);
                }
            }
            unset($k, $v);
        }
        $list = array_merge($stateList, $category);
        return $this->success($list, true);
    }

    public function sendApply(Request $request)
    {
        $is_guest_enter_for = $request->input('is_guest_enter_for', 0);
        $openid = $request->user()->openid;
        $token = $request->user()->token;

        $reader = Reader::checkBind($openid, $token)->first();
        if ($reader == false && $is_guest_enter_for != 1) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $wxuser = Wxuser::getCache($token);
        $info = [];
        if ($reader) {
            $openlibService = OpenlibService::make($token, $wxuser);
            $info = $openlibService->searchreader($reader->rdid);
        }
        $otherParams = $request->only([
            'attachName',
            'attachSex',
            'attachAge',
            'attachMobile',
            'attachDesc',
            'carryNum',
            'mobile',
            'attachIdentity'
        ]);
        $params = [
            'cmd' => 'enterFor',
            'rdid' => Arr::get($info, 'rdid', ''),
            'rdpasswd' => (Arr::get($info, 'rdpasswd')) ? md5(Arr::get($info, 'rdpasswd')) : '',
            'specialId' => $request->route('specialId'),
        ];
        if ($is_guest_enter_for == 1) {
            $params['isGuestEnterFor'] = 1;
        }
        $params = array_merge($params, $otherParams);

        $activityApi = ActivityApi::make($wxuser['activity_url'], $token);
        $response = $activityApi->request('mb/mobileApi', $params);
        if (is_array($response) && Arr::get($response, 'errMes')) {
            return $this->failed(Arr::get($response, 'errMes'), 400);
        }
        $success = ['message' => $response['message'], 'typeData' => '', 'typeName' => ''];
        if ($response['state'] == 0) {
            return $this->success($success, false);
        }
        $eventService = new FansEvent($request->user()->token, $request->user()->openid);
        $eventData = $eventService->check('applyAction');
        if ($eventData) {
            $success = array_merge($success, $eventData);
        }
        return $this->success($success, true);
    }

    public function cancelApply(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $reader = Reader::checkBind($openid, $token)->first();
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $wxuser = Wxuser::getCache($token);
        $openlibService = OpenlibService::make($token, $wxuser);
        $info = $openlibService->searchreader($reader->rdid);
        $params = [
            'cmd' => 'cancelAction',
            'rdid' => $reader['rdid'],
            'rdpasswd' => md5($info['rdpasswd']),
            'type' => 'cancel',
            'id' => $request->route('specialId'),
        ];
        $activityApi = ActivityApi::make($wxuser['activity_url'], $token);
        $response = $activityApi->request('mb/mobileApi', $params);
        if ($response['state'] == 0) {
            return $this->success($response['message'] ?: '取消失败!', false);
        }
        return $this->success($response['message'], true);
    }

    public function getMyList(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $reader = Reader::checkBind($openid, $token)->first();
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $wxuser = Wxuser::getCache($token);
        $openlibService = OpenlibService::make($token, $wxuser);
        $info = $openlibService->searchreader($reader->rdid);
        $params = [
            'cmd' => 'listReaderActions',
            'rdid' => $reader['rdid'],
            'rdpasswd' => md5($info['rdpasswd'])
        ];
        $activityApi = ActivityApi::make($wxuser['activity_url'], $token);
        $response = $activityApi->request('mb/mobileApi', $params);

//        if ()
        if (isset($response['data'])) {
            foreach ($response['data'] as $k => $v) {
                $response['data'][$k]['play_bill_pic'] = $activityApi->completionCover($v['play_bill_pic']);
            }
        }
        return $this->success($response, true);
    }

    //读者报名活动
    public function readerAction(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $reader = Reader::checkBind($openid, $token)->first();
        if ($reader == false) {
//            return $this->failed('尚未绑定读者证!', 401);
            return $this->message('尚未绑定读者证!', false);
        }
        $wxuser = Wxuser::getCache($token);
        $openlibService = OpenlibService::make($token, $wxuser);

        $info = $openlibService->searchreader($reader->rdid);

        if (Arr::get($info, 'success') == false) {
            return $this->message(Arr::get($info, 'messagelist.0.message'), false);
        }
        $params = [
            'cmd' => 'viewReaderAction',
            'rdid' => $reader['rdid'],
            'rdpasswd' => md5($info['rdpasswd']),
            'specialId' => $request->route('specialId'),
        ];
        $activityApi = ActivityApi::make($wxuser['activity_url'], $token);
        $response = $activityApi->request('mb/mobileApi', $params);
        if (isset($response['message'])) {
            return $this->message($response['message'], false);
        }
        return $this->success($response, true);
    }

    public function getRecord(Request $request)
    {
        $token = $request->user()->token;
//        $openid = $request->user()->openid;
        $params = [
            'cmd' => 'listReaderActionsBySpecial',
            'specialId' => $request->route('specialId'),
            'signType' => $request->input('signType', 0)
        ];
        $wxuser = Wxuser::getCache($token);

        $activityApi = ActivityApi::make($wxuser['activity_url'], $token);
        $response = $activityApi->request('mb/mobileApi', $params);
//        $response = $this->activityHttpMagic($request->user()->token, $params, 'mb/mobileApi');
        return $this->success($response, true);
    }

    public function saveCollect(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $reader = Reader::checkBind($openid, $token)->first();
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }

        $specialId = $request->route('specialId');
        $readerToMany = ReaderToMany::findReader($reader)->first();
        // 收藏操作
        $status = 1;
        if (empty($readerToMany)) {
            $create = ['token' => $reader['token'], 'rdid' => $reader['rdid'], 'act_collect' => [$specialId]];
            ReaderToMany::create($create);
            $message = '活动收藏成功！';
            return $this->message($message, $status);
        }
        $actCollect = $readerToMany['act_collect'];
        //判断是否存在
        if (in_array($specialId, $actCollect)) {
            //取消收藏的状态
            $status = 2;
            $message = '收藏取消成功！';
            $actCollect = array_diff($actCollect, [$specialId]);
        } else {
            $message = '活动收藏成功！';
            $actCollect[] = $specialId;
        }
        $readerToMany->act_collect = $actCollect;
        $readerToMany->save();

        return $this->message($message, $status);

    }

    public function collectList(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $reader = Reader::checkBind($openid, $token)->first();
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        $page = $request->input('page', 1);
        $rows = $request->input('rows', 10);
        $offset = ($page - 1) * $rows;

        $readerToMany = ReaderToMany::findReader($reader)->first();

        $params = [
            'cmd' => 'listSearchActions',
            'offset' => $offset,
            'rows' => $rows
        ];
        $wxuser = Wxuser::getCache($token);
//        $wxuser = $this->getWxuserCache($request->user()->token);
        if (!empty($wxuser['libcode'])) {
            $params['libcode'] = $wxuser['libcode'];
        }
        if (!empty($readerToMany) && count($readerToMany['act_collect']) > 0) {
            $act_collect = implode(':', $readerToMany['act_collect']);
            $params += ['ids' => $act_collect];

            $activityApi = ActivityApi::make($wxuser['activity_url'], $token);
            $response = $activityApi->request('mb/mobileApi', $params);

//            $response = $this->activityHttpMagic($request->user()->token, $params, 'mb/mobileApi');
            foreach ($response['data'] as $k => $v) {
                $response['data'][$k]['play_bill_pic'] = $activityApi->completionCover($v['play_bill_pic']);
            }
            return $this->success($response, true);
        }
        return $this->success([], false);
    }

    public function contentList(Request $request)
    {
        if (!$request->route('specialId')) {
            return $this->failed('活动ID不能为空!!', 400);
        }
        $rows = $request->input('rows', 10);
        $where = ['token' => $request->user()->token, 'act_id' => $request->route('specialId')];

        return ActContent::with('fansInfo:nickname,headimgurl,openid')
            ->where($where)->orderBy('id', 'desc')->paginate($rows);
    }

    public function saveContent(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;
        $reader = Reader::checkBind($openid, $token)->first();
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }
        if (!$request->filled(['id', 'content'])) {
            return $this->failed('缺少必带参数!!', 400);
        }
        $specialId = $request->input('id');
        $content = $request->input('content');

        $create = [
            'status' => 1,
            'act_id' => $specialId,
            'openid' => $openid,
            'token' => $token,
            'rdid' => $reader['rdid'],
            'content' => $content,
        ];

        $status = ActContent::create($create);
        $code = 1;
        $message = '评论成功';
        if (!$status) {
            $code = 0;
            $message = '评论失败';
        }
        return $this->message($message, $code);

    }

}
