<?php

namespace App\Http\Controllers\Web\Vote;

use App\Api\Helpers\ApiResponse;
use App\Jobs\OssUpPeopleImage;
use App\Models\Vote\VoteBlacklist;
use App\Models\Vote\VoteGroup;
use App\Models\Vote\VoteItems;
use App\Models\Vote\VoteRecord;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use App\Services\WebOAuthService;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class IndexController extends BaseController
{
    use ApiResponse;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        View::share('config', $this->config);
        View::share('templatePath', asset('wechatWeb/vote/template') . $this->config['template_id']);
    }

    public function index(Request $request, WebOAuthService $webOAuthService)
    {
        if ($this->config['template_id'] == 9) {
            return redirect()->route('Vote::index2', ['a_id' => $request->input('a_id'), 'token' => $request->input('token')]);
        }

        $fansInfo = $webOAuthService->checkOauth();
        $fansInfo['subscribe'] = 1;
        if ($this->config['sub_sw'] == 1) {
            $fansInfo['subscribe'] = $webOAuthService->checkSubscribe($fansInfo['openid']);
        }
        $qrCode = Wxuser::getCache($this->token)->qr_code;

        $g_id = $request->input('g_id');
        $groupList = VoteGroup::getCache($this->token, $this->aid);
        if (empty($g_id) && $groupList) {
            $g_id = $groupList->first()->id;
        }
        $myItemID = VoteItems::findOpenid($this->aid, $g_id, $fansInfo['openid'])->value('id');

        return \view('web.vote.index', [
            'sliderImg' => $this->getSliderImg(),
            'fansInfo' => $fansInfo,
            'g_id' => $g_id,
            'qrCode' => $qrCode,
            'myItemID' => $myItemID,
            'groupData' => $this->getGroupData($g_id),
            'groupList' => $groupList,
            'urlArr' => $this->initUrl($g_id),
        ]);
    }

    public function ajaxItems(Request $request)
    {
        if (!$request->filled('g_id')) {
            return $this->failed('缺少参数', 400);
        }
        $page = $request->input('page', 1);
        $g_id = $request->input('g_id');
        $searchKey = $request->input('searchKey', '');

        $where = [
            'a_id' => $this->aid,
            'g_id' => $g_id,
            'status' => 1
        ];
        if ($searchKey) {
            if (is_numeric($searchKey)) {
                $where['number'] = $searchKey;
            }
            $items = VoteItems::select('id', 'cover', 'number', 'title')
                ->where($where)->when(!is_numeric($searchKey), function ($query) use ($searchKey) {
                    return $query->where('title', 'like', '%' . $searchKey . '%');
                })->orderBy('number')->paginate(12);

        } else {
            $cacheKey = 'vote:item:a:' . $this->aid . ':g:' . $g_id . ':p:' . $page;
            $items = Cache::remember($cacheKey, 30, function () use ($where) {
                return VoteItems::select('id', 'cover', 'number', 'title')
                    ->where($where)
                    ->orderBy('number')
                    ->paginate(12);
            });

        }
        $list = $this->groupRedisList($g_id, 'vote');
        foreach ($items as $k => $v) {
            $items[$k]->voting_n = Arr::get($list, $v['id'], 0);
            $items[$k]->url = route('Vote::details', Arr::add($this->basisWhere, 't_id', $v['id']));
        }
        return $items;
    }

    public function rankList(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $g_id = $request->input('g_id');
        $groupList = VoteGroup::getCache($this->token, $this->aid);
        if (empty($g_id) && $groupList) {
            $g_id = $groupList->first()->id;
        }
        //查看是否已报名
        $myItemID = VoteItems::findOpenid($this->aid, $g_id, $fansInfo['openid'])->value('id');

        $allItem = VoteItems::allItems($this->aid, $g_id, ['id', 'cover', 'number', 'title']);
        $now = $allItem->pluck('id')->toArray();
        //获取排行榜信息
        $voteList = $this->groupRedisList($g_id, 'vote');
        $voteRank = $this->filter($voteList, $now);
        $viewList = $this->groupRedisList($g_id, 'view');
        $rankList = [];
        foreach ($voteRank as $k => $v) {
            $first = $allItem->where('id', $k)->first();
            $rankList[] = [
                'id' => $k,
                'number' => $first['number'],
                'rank' => $v['rank'],
                'cover' => $first['cover'],
                'title' => $first['title'],
                'views' => Arr::get($viewList, $k, 0),
                'votes' => $v['votes'],
                'url' => route('Vote::details', Arr::add($this->basisWhere, 't_id', $first['id'])),
            ];
        }
        return \view('web.vote.rank', [
            'sliderImg' => $this->getSliderImg(),
            'fansInfo' => $fansInfo,
            'myItemID' => $myItemID,
            'groupData' => $this->getGroupData($g_id, $groupList),
            'groupList' => $groupList,
            'urlArr' => $this->initUrl($g_id),
            'rankList' => $rankList,
        ]);
    }

    public function explain(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $g_id = $request->input('g_id');
        $groupList = VoteGroup::getCache($this->token, $this->aid);
        if (empty($g_id) && $groupList) {
            $g_id = $groupList->first()->id;
        }
        //查看是否已报名
        $myItemID = VoteItems::findOpenid($this->aid, $g_id, $fansInfo['openid'])->value('id');
        return \view('web.vote.explain', [
            'sliderImg' => $this->getSliderImg(),
            'fansInfo' => $fansInfo,
            'myItemID' => $myItemID,
            'groupData' => $this->getGroupData($g_id, $groupList),
            'urlArr' => $this->initUrl($g_id),
        ]);
    }

    public function signUp(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $reader = [];
        if ($this->config['reader_sw'] == 1) {
            $reader = Reader::checkBind($fansInfo['openid'], $this->token)->first(['rdid']);
            if (empty($reader)) {
                $bindUrl = str_replace('{token}', $this->token, config('vueRoute.bindReader'));
                return redirect($bindUrl);
            }
        }

        $g_id = $request->input('g_id');
        $groupList = VoteGroup::getCache($this->token, $this->aid);

        if (empty($g_id) && $groupList) {
            $g_id = $groupList->first()->id;
        }
        $groupInfo = $groupList->where('id', $g_id)->first();
        $fields = $groupInfo->fields;
        //查看是否已报名
        $myItemID = VoteItems::findOpenid($this->aid, $g_id, $fansInfo['openid'])->value('id');

        //如果已报名，自动跳转到报名的的详情里。
        if ($request->isMethod('post')) {
            if ($myItemID) {
                return back()->withErrors('你已报过名,无法重复报名!');
            }
            $messages = ['required' => '缺少必填信息,无法提交!'];
            $rules = [
                'title' => 'required',
                'phone' => 'required',
                'cover' => 'required',
                'info' => 'required',
            ];
            foreach ($fields as $k => $v) {
                if ($v['required_sw'] == 1) {
                    $rules['fields' . $v['id']] = 'required';
                }
            }
            $request->validate($rules, $messages);
            if ($this->config['s_time'] > date('Y-m-d H:i:s') || $this->config['e_time'] < date('Y-m-d H:i:s')) {
                return back()->withErrors('抱歉当前时间无法进行报名!');
            }
            $number = VoteItems::where(['a_id' => $this->aid, 'g_id' => $g_id])
                ->orderBy('number', 'Desc')->value('number');
            $number++;
            $create = [
                'a_id' => $this->aid,
                'g_id' => $g_id,
                'openid' => $fansInfo['openid'],
                'title' => $request->input('title'),
                'phone' => $request->input('phone'),
                'info' => $request->input('info'),
                'view_n' => 0,
                'voting_n' => 0,
                'ranking' => 0,
                'number' => $number,
                'status' => 0
            ];
            $fileName = 'voteImage/' . $this->token . '/' . $this->aid . '/' . uniqid() . '.jpeg';
            $cover = array_filter($request->input('cover'));
            OssUpPeopleImage::dispatch($this->token, $cover[0], $fileName);
            $create['cover'] = $fileName;
            $contents = [];
            foreach ($fields as $k => $v) {
                if ($request->filled('fields' . $v['id'])) {
                    $fieldsData = $request->input('fields' . $v['id']);
                    switch ($v['type']) {
                        case 1:
                            $contents[$v['id']] = $fieldsData;
                            break;
                        case 2:
                            $contents[$v['id']] = Arr::where($fieldsData, function ($value) {
                                return !is_null($value);
                            });
                            break;
                        case 4:
                            $fieldsData = Arr::where($fieldsData, function ($value) {
                                return !is_null($value);
                            });
                            $imgArr = [];
                            foreach ($fieldsData as $key => $val) {
                                $fileName = 'voteImage/' . $this->token . '/' . $this->aid . '/' . uniqid() . '.jpeg';
                                $imgArr[] = $fileName;
                                OssUpPeopleImage::dispatch($this->token, $val, $fileName);
                            }
                            $contents[$v['id']] = $imgArr;
                            break;
                        default:
                            $contents[$v['id']] = $fieldsData;
                    }
                }
            }
            $create['content'] = $contents;
            //判断是否绑定读者，绑定附带读者证
            $create['rdid'] = Arr::get($reader, 'rdid');
            //判断审核类型
            if ($this->config['audit_sw'] != 1) {
                //不需要审核，直接插入数据到redis 列表 rands  views。
                $create['status'] = 1;
            }
            $status = VoteItems::create($create);
            if ($status) {
                $id = $status['id'];
                $this->addRedisList($g_id, 'view', $id);
                $this->addRedisList($g_id, 'vote', $id);
                return redirect(route('Vote::details', Arr::add($this->basisWhere, 't_id', $id)));
            }
            return back()->withErrors('系统繁忙请稍后再试!');
        }

        return \view('web.vote.signup', [
            'fansInfo' => $fansInfo,
            'myItemID' => $myItemID,
            'groupInfo' => $groupInfo,
            'fields' => $fields,
            'urlArr' => $this->initUrl($g_id),
        ]);
    }

    public function details(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $fansInfo['subscribe'] = 1;
        if ($this->config['sub_sw'] == 1) {
            $fansInfo['subscribe'] = $webOAuthService->checkSubscribe($fansInfo['openid']);
        }
        $qrCode = Wxuser::getCache($this->token)->qr_code;
        $t_id = $request->input('t_id');
        $details = VoteItems::where(['a_id' => $this->aid, 'id' => $t_id])->first();
        if (empty($details)) abort(404);
        $g_id = $details['g_id'];
        //增加访问次数
        $this->redis->zincrby($this->cacheKey($g_id, 'view'), 1, $t_id);

        $voteList = $this->groupRedisList($g_id, 'vote');

        $now = VoteItems::allItems($this->aid, $g_id, ['id', 'cover', 'number', 'title'])->pluck('id')->toArray();
        $voteRank = $this->filter($voteList, $now);

        $details->voting_n = Arr::get($voteList, $t_id);
        $details->view_n = $this->findRedis($g_id, 'view', $t_id);
        $details->ranking = Arr::get($voteRank, $t_id);

        $showFields = [];
        $myFieldsVal = $details->content;
        if (!empty($myFieldsVal)) {
            $voteGroup = VoteGroup::find($g_id);
            $fields = $voteGroup->fields->keyBy('id');
            foreach ($myFieldsVal as $k => $v) {
                $field = $fields->get($k);
                if ($field && $field->type === 0 && $field->show_sw == 1) {
                    $showFields[] = [
                        'title' => $field->name,
                        'value' => $v
                    ];
                }
            }
        }

        return \view('web.vote.details', [
            'fansInfo' => $fansInfo,
            'details' => $details,
            'qrCode' => $qrCode,
            'showFields' => $showFields,
            'voteData' => $this->getUserVote($g_id, $t_id, $fansInfo['openid']),
            'urlArr' => $this->initUrl($details['g_id']),
        ]);
    }

    public function ajaxVote(Request $request, WebOAuthService $webOAuthService)
    {
        $g_id = $request->input('g_id');
        $t_id = $request->input('t_id');
        $fansInfo = $webOAuthService->checkOauth();

        if (!$request->filled(['a_id', 'g_id', 't_id'])) {
            return $this->message('非法访问', false);
        }
        if ($this->config['status'] != 1) {
            return $this->message('抱歉,活动已关闭,无法投票!', false);
        }
        //判断投票时间
        if ($this->config['s_date'] > date('Y-m-d H:i:s') || $this->config['e_date'] < date('Y-m-d H:i:s')) {
            return $this->message('抱歉,不是投票有效时间,投票失败!', false);
        }
        //查看当前人的ip是否存在内名单
        $ip = $request->getClientIp();
        if ($this->config['rules_ip'] == 1) {
            $checkIp = ip2long($ip);
            $exists = VoteBlacklist::where(['token' => $this->token, 'ip' => $checkIp])->exists();
            if ($exists) {
                return $this->message('抱歉,黑名单用户无法进行投票!', false);
            }
            $exists = VoteBlacklist::where(['token' => $this->token, 'openid' => $fansInfo['openid']])->exists();
            if ($exists) {
                return $this->message('抱歉,黑名单用户无法进行投票!', false);
            }
        }
        $where = [
            'a_id' => $this->aid,
            'g_id' => $g_id,
            'id' => $t_id,
        ];
        $item = VoteItems::where($where)->first(['openid', 'title', 'lockstatus', 'number', 'id', 'lockinfo', 'status']);
        if (empty($item)) {
            return $this->message('非法操作!', false);
        }
        //判断是否通过审核
        if ($item->status != 1) {
            return $this->message('抱歉，作品尚未通过审核，不能投票!', false);
        }
        //判断是否已锁定
        if ($item->lockstatus == 1) {
            $unit_title = $this->config['unit_title'] ?: '作品';
            return $this->message('抱歉,该' . $unit_title . '已被锁定,无法投票!', false);
        }
        $voteData = $this->getUserVote($g_id, $t_id, $fansInfo['openid']);
        if ($voteData['currentNumber'] == 0) {
            return $this->message('暂无可投票数！', false);
        }
        if ($voteData['allowNumber'] == 0) {
            return $this->message('抱歉，当前已达到投票上限！', false);
        }
        $create = [
            'a_id' => $this->aid,
            'g_id' => $g_id,
            't_id' => $t_id,
            'openid' => $fansInfo['openid'],
            'ip' => $ip,
        ];
        $status = VoteRecord::create($create);
        if ($status) {
            $votes = $this->redis->zincrby($this->cacheKey($g_id), 1, $t_id);
            //被投票通知是否开启
            if ($this->config['notice_sw'] == 1 && $votes % 20 == 0) {
                $text = "好友" . $fansInfo['nickname'] . "刚为你投下神圣的一票\n\n 当前您的票数为:" . $votes;
                $message = new Text($text);
                $app = Wechatapp::initialize($this->token);
                $app->customer_service->message($message)->to($item['openid'])->send();
            }
            //刷票警告开关是否开启
            if ($this->config['warning_sw'] == 1) {
                $warning_rule = $this->config['warning_rule'];
                $lock_rule = $this->config['lock_rule'];
                $where = [
                    'a_id' => $this->aid,
                    'g_id' => $g_id,
                    't_id' => $t_id,
                ];
                $query = VoteRecord::where($where);
                //进行警告的规则
                if ($warning_rule['min'] > 0 && $warning_rule['number'] > 0) {
                    $time = now()->subMinute($warning_rule['min'])->toDateTimeString();
                    $count = $query->where('created_at', '>', $time)->count();
                    //发送警告
                    if ($count >= $warning_rule['number']) {
                        $text = "由于你的参赛作品在" . $warning_rule['min'] . "分钟内,所获得的票数达到" . $count . "票,存在异常," .
                            "在此警告,请文明竞赛!";
                        if ($item['openid']) {
                            $message = new Text($text);
                            $app = Wechatapp::initialize($this->token);
                            $app->customer_service->message($message)->to($item['openid'])->send();
                        }
                    }
                }
                //进行锁定的规则
                if ($lock_rule['min'] > 0 && $lock_rule['number'] > 0) {
                    $time = now()->subMinute($lock_rule['min'])->toDateTimeString();
                    $count = $query->where('created_at', '>', $time)->count();
                    //进行锁定
                    if ($count >= $lock_rule['number']) {
                        $text = "由于你的参赛作品在" . $warning_rule['min'] . "分钟内,所获得的票数达到" . $count . "票,存在异常," .
                            "已被锁定!";
                        $item->lockstatus = 1;
                        $item->lockinfo = $text;
                        $item->save();
                        if ($item['openid']) {
                            $message = new Text($text);
                            $app = Wechatapp::initialize($this->token);
                            $app->customer_service->message($message)->to($item['openid'])->send();
                        }
                    }
                }
            }
            return $this->message('投票成功,等待刷新!', true);
        }
        return $this->message('投票失败,请稍后再试!', true);
    }

}
