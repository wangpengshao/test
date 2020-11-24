<?php

namespace App\Admin\Controllers\Wechat\Vote;

use App\Admin\Extensions\ExcelExporter\VoteTopExporter;
use App\Models\Vote\VoteGroup;
use App\Models\Vote\VoteItems;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redis;
use Maatwebsite\Excel\Facades\Excel;

class VoteTopController extends Controller
{
    protected $token;
    protected $g_id;
    protected $a_id;

    public function index(Request $request)
    {
        $this->token = $request->session()->get('wxtoken');
        $this->g_id = $request->input('g_id');

        $where = ['token' => $this->token, 'id' => $this->g_id];
        $group = VoteGroup::where($where)->first();
        if ($group === null) {
            admin_error('警告', '非法访问');
            return back();
        }
        $this->a_id = $group->a_id;

        return Admin::content(function (Content $content) use ($group) {
            $redis = Redis::connection();
            $allItems = VoteItems::AllItems($this->a_id, $this->g_id, ['id', 'cover', 'number', 'title']);
            $now = $allItems->pluck('id')->toArray();
            $voteList = $redis->zrevrange($this->cacheKey(), 0, -1, 'WITHSCORES');
            $voteRank = $this->filter($voteList, $now);
            $viewList = $redis->zrevrange($this->cacheKey('view'), 0, -1, 'WITHSCORES');
            $rankList = [];
            foreach ($voteRank as $k => $v) {
                $first = $allItems->where('id', $k)->first();
                $rankList[] = [
                    'id' => $k,
                    'number' => $first['number'],
                    'rank' => $v['rank'],
                    'cover' => $first['cover'],
                    'title' => $first['title'],
                    'views' => Arr::get($viewList, $k, 0),
                    'votes' => $v['votes'],
                ];
            }
            $info = [
                'number' => count($voteList),
                'vote' => array_sum($voteList),
                'view' => array_sum($viewList),
            ];
            $content->body(view('admin.diy.voteTop', [
                'g_id' => $this->g_id,
                'a_id' => $this->a_id,
                'info' => $info,
                'rankList' => $rankList,
                'groupList' => $this->getGroupList(),
            ]));
        });
    }

    public function topExport(Request $request)
    {
        $this->token = $request->session()->get('wxtoken');
        $this->g_id = $request->input('g_id');

        $where = ['token' => $this->token, 'id' => $this->g_id];
        $group = VoteGroup::where($where)->first();
        if ($group === null) {
            abort(404);
        }
        $this->a_id = $group->a_id;

        $redis = Redis::connection();
        $allItems = VoteItems::AllItems($this->a_id, $this->g_id);
        $now = $allItems->pluck('id')->toArray();

        $voteList = $redis->zrevrange($this->cacheKey(), 0, -1, 'WITHSCORES');
        $voteRank = $this->filter($voteList, $now);
        $viewList = $redis->zrevrange($this->cacheKey('view'), 0, -1, 'WITHSCORES');
        $rankList = [];
        foreach ($voteRank as $k => $v) {
            $first = $allItems->where('id', $k)->first();
            $rankList[] = [
                'number' => $first['number'],
                'title' => $first['title'],
                'rdid' => $first['rdid'],
                'openid' => $first['openid'],
                'phone' => $first['phone'],
                'views' => Arr::get($viewList, $k, 0),
                'votes' => $v['votes'],
                'rank' => $v['rank'],
            ];
        }
        $export = new VoteTopExporter($rankList, ['序号', '名称', '读者证', 'openid', '手机号码', '查看数', '投票数', '名次']);
        $file_name = ($group['title']) ?: '排行榜';
        return Excel::download($export, $file_name . '.xlsx');
    }

    public function getGroupList()
    {
        return VoteGroup::where(['token' => $this->token, 'a_id' => $this->a_id])
            ->orderBy('sort')->pluck('title', 'id');
    }


    public function cacheKey($type = 'vote')
    {
        return 'voteRank:' . $type . ':a:' . $this->a_id . ':g:' . $this->g_id;
    }

    public function filter($list, $keep)
    {
        $list = Arr::only($list, $keep);
        $rank = 1;
        $rankList = [];
        $key = 0;
        $number = 0;
        foreach ($list as $k => $v) {
            if ($key !== 0) {
                if ($v < $number) {
                    $rank++;
                }
            }
            $data = [
                'votes' => $v,
                'rank' => $rank
            ];
            $rankList[$k] = $data;
            $number = $v;
            $key++;
        }
        return $rankList;
    }
}
