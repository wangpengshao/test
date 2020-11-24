<?php

namespace App\Http\Controllers\Web\Vote;

use App\Http\Controllers\Controller;
use App\Models\Vote\VoteConfig;
use App\Models\Vote\VoteItems;
use App\Models\Vote\VoteRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class BaseController extends Controller
{
    public $token;
    public $aid;
    public $basisWhere;
    public $config = null;
    public $redis = null;

    public function __construct(Request $request)
    {
        if (basename($_SERVER['SCRIPT_NAME']) == 'artisan') {
            return null;
        }
        $this->middleware('RequiredToken');
        $this->aid = $request->input('a_id');
        $this->token = $request->input('token');
        $this->basisWhere = [
            'a_id' => $request->input('a_id'),
            'token' => $request->input('token')
        ];
        $this->initData();
        $this->redis = Redis::connection();
    }

    public function initData()
    {
        $this->config = VoteConfig::getCache($this->token, $this->aid);
        if (empty($this->config)) abort(404);
    }

    public function getSliderImg()
    {
        /* 轮播图片链接处理 */
        $storage = Storage::disk(config('admin.upload.disk'));
        $sliderImg = $this->config->img;
        foreach ($sliderImg as $k => $v) {
            $sliderImg[$k] = $storage->url($v);
        }
        return $sliderImg;
    }

    public function getGroupData($g_id)
    {
        $view_n = 0;  //分组查看数
        $item_n = 0;  //分组作品数
        $voting_n = 0;   //分组投票数
        if ($g_id) {
            $votes = $this->groupRedisList($g_id, 'vote');
            $voting_n = array_sum($votes);
            $item_n = count($votes);
            $view_n = array_sum($this->groupRedisList($g_id, 'view'));
        }
        return [
            'view_n' => $view_n,
            'item_n' => $item_n,
            'voting_n' => $voting_n,
        ];
    }

    public function addRedisList($g_id, $type = 'vote', $id)
    {
        return $this->redis->zadd($this->cacheKey($g_id, $type), 0, $id);
    }

    public function groupRedisList($g_id, $type = 'vote')
    {
        return $this->redis->zrevrange($this->cacheKey($g_id, $type), 0, -1, 'WITHSCORES');
    }

    public function findRedis($g_id, $type = 'vote', $id)
    {
        return $this->redis->zscore($this->cacheKey($g_id, $type), $id);
    }

//    public function getAllItem($g_id)
//    {
//        $where = [
//            'a_id' => $this->aid,
//            'g_id' => $g_id,
//            'status' => 1
//        ];
//        $cacheKey = 'vote:itemAll:a:' . $this->aid . ':g:' . $g_id;
//        return Cache::remember($cacheKey, 30, function () use ($where) {
//            return VoteItems::select('id', 'cover', 'number', 'title')
//                ->where($where)->get();
//        });
//    }

    public function getUserVote($g_id, $t_id, $openid)
    {
        //规则进行投票限制  1、查看活动类型  2、查看个人投票情况
        $voteData = [
            'all' => 0,      //当前类型全部投票
            'currentNumber' => 0,    //当前可投
            'allowNumber' => 0,     //当前作品可投
            'currentVoteNumber' => 0,   //当前作品已投
        ];
        $voteQuery = VoteRecord::where(['a_id' => $this->aid, 'g_id' => $g_id, 'openid' => $openid]);
        if ($this->config['rules_type'] == 1) {
            $voteQuery->whereDate('created_at', '>=', date('Y-m-d'));
        }
        //个人目前投票的数量
        $voteData['all'] = $voteQuery->count('id');
        //目前可投的票数
        $voteData['currentNumber'] = $this->config['day_n'] - $voteData['all'];
        $voteData['currentVoteNumber'] = $voteQuery->where('t_id', $t_id)->count('id');
        //有可投票再计算是否对目标作品可投
        if ($this->config['unit_n'] > 0) {
            $allowNumber = $this->config['unit_n'] - $voteData['currentVoteNumber'];
            $voteData['allowNumber'] = ($allowNumber < 0) ? 0 : $allowNumber;
        }
        return $voteData;
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

    public function cacheKey($g_id, $type = 'vote')
    {
        return 'voteRank:' . $type . ':a:' . $this->aid . ':g:' . $g_id;
    }

    public function initUrl($g_id)
    {
        return [
            'ajaxItemsUrl' => route('Vote::ajaxItems', Arr::add($this->basisWhere, 'g_id', $g_id)),
            'rankUrl' => route('Vote::rank', Arr::add($this->basisWhere, 'g_id', $g_id)),
            'indexUrl' => route('Vote::index', Arr::add($this->basisWhere, 'g_id', $g_id)),
            'explainUrl' => route('Vote::explain', Arr::add($this->basisWhere, 'g_id', $g_id)),
            'signUpUrl' => route('Vote::signUp', Arr::add($this->basisWhere, 'g_id', $g_id)),
            'detailsUrl' => route('Vote::details', $this->basisWhere),
            'ajaxVoteUrl' => route('Vote::ajaxVote', $this->basisWhere),
        ];
    }

    public function initUrl2($g_id)
    {
        return [
            'ajaxItemsUrl' => route('Vote::ajaxItems2', Arr::add($this->basisWhere, 'g_id', $g_id)),
            'rankUrl' => route('Vote::rank2', Arr::add($this->basisWhere, 'g_id', $g_id)),
            'indexUrl' => route('Vote::index2', Arr::add($this->basisWhere, 'g_id', $g_id)),
            'explainUrl' => route('Vote::explain2', Arr::add($this->basisWhere, 'g_id', $g_id)),
            'signUpUrl' => route('Vote::signUp2', Arr::add($this->basisWhere, 'g_id', $g_id)),
            'detailsUrl' => route('Vote::details2', $this->basisWhere),
            'ajaxVoteUrl' => route('Vote::ajaxVote2', $this->basisWhere),
            'ajaxCommentUrl' => route('Vote::comment2', $this->basisWhere),
            'ajaxGetCommentUrl' => route('Vote::ajaxComment2', $this->basisWhere),
        ];
    }
}
