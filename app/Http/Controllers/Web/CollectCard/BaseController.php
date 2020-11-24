<?php

namespace App\Http\Controllers\Web\CollectCard;

use App\Http\Controllers\Controller;
use App\Models\CollectCard\CollectCard;
use App\Models\CollectCard\CollectLog;
use App\Models\CollectCard\CollectTask;
use App\Models\CollectCard\CollectTaskLog;
use App\Models\CollectCard\CollectUsers;
use App\Models\CollectCard\HtmlConfig;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    public $allConfig;
    public $config;
    public $token;
    public $aid;
    public $basisWhere;

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
    }

    public function checkUser(Request $request, $fansInfo)
    {
        $isNewDay = 0;
        $sessionKey = $this->sessionName('user');
        $userSession = $request->session()->get($sessionKey);
        if (!$userSession) {
            $user = CollectUsers::where('openid', $fansInfo['openid'])->where($this->basisWhere)->first();
            if (empty($user)) {
                $params = $this->basisWhere;
                if ($request->filled(['user_id'])) {
                    $params['user_id'] = $request->input('user_id');
                }
                if ($request->filled(['user_id', 'card_id', 'sign'])) {
                    $params['user_id'] = $request->input('user_id');
                    $params['card_id'] = $request->input('card_id');
                    $params['sign'] = $request->input('sign');
                }
                $url = route('CollectCard::channel', $params);
                header("Location: " . $url);
                exit();
            }
            if ($user->last_at < date('Y-m-d')) {
                $isNewDay = 1;
            }
            $user->last_at = date('Y-m-d H:i:s');
            $user->save();
            $userSession = $user;
            $request->session()->put($sessionKey, $userSession);
            $request->session()->save();
        }
        //如果最后登录的时间不是今天的话,每天登录活动送卡
        if ($userSession->last_at < date('Y-m-d') || $isNewDay == 1) {
            $request->session()->push($this->sessionName('event'), ['origin_type' => 1, 'origin_id' => 2]);
            $userSession->last_at = date('Y-m-d H:i:s');
            $userSession->save();
        }
        return $userSession;
    }

    public function initData()
    {
        $collectCard = CollectCard::getCache($this->token, $this->aid);
        if (empty($collectCard)) abort(404);
        $this->allConfig = $collectCard;
        $this->config = $collectCard->only(
            ['start_at', 'end_at', 'title', 'description', 'giving_sw', 'subscribe_sw', 'share_title', 'share_img',
                'share_desc', 'reader_sw', 'status', 'type', 'sub_text']
        );
    }

    public function getHtmlConf()
    {
        $htmlConfig = HtmlConfig::getCache($this->token, $this->aid);
        if (!$htmlConfig) abort(404);
        return $htmlConfig;
    }

    public function getCardList()
    {
        return $this->allConfig->hasManyCard;
    }

    public function getShowTask()
    {
        return CollectTask::getAllCache($this->token, $this->aid)->where('is_show', 1);
    }

    public function getTask($origin_type = '', $origin_id = '')
    {
        $task = CollectTask::getAllCache($this->token, $this->aid);
        if ($origin_type) {
            $task = $task->where('origin_type', $origin_type);
        }
        if ($origin_id) {
            $task = $task->where('origin_id', $origin_id);
        }
        return $task;
    }

    public function createTaskLog($user, $origin_type, $origin_id, $c_id, $rdid = '')
    {
        $create = [
            'user_id' => $user['id'],
            'origin_type' => $origin_type,
            'origin_id' => $origin_id,
            'a_id' => $this->aid,
            'token' => $this->token,
            'isValid' => 1,
            'c_id' => $c_id
        ];
        $createLog = CollectLog::create($create);
        $firstCard = $createLog->hasOneCard;
        $firstCard->number--;
        $firstCard->get_number++;
        $firstCard->save();
        CollectTaskLog::create([
            'user_id' => $user['id'],
            'token' => $this->token,
            'a_id' => $this->aid,
            'rdid' => $rdid,
            't_id' => $createLog['id']
        ]);
        return $createLog;
    }

    //检查是否集齐并更新
    public function checkUpCollect(&$user, $cardList, $groupFirstId = null, $request = null)
    {
        if (empty($groupFirstId)) {
            $groupFirstId = CollectLog::groupFirstId($user['id'], $this->basisWhere);
        }
        if ($user['collect_all'] != 1 && $this->config['end_at'] > date('Y-m-d H:i:s')) {
            $requireCard = $cardList->whereStrict('type', 0)->pluck('id');
            $collect_all = 1;
            $upLog_id = [];
            foreach ($requireCard as $k => $v) {
                if (!array_get($groupFirstId, $v)) {
                    $collect_all = 0;
                    break;
                }
                $upLog_id[] = array_get($groupFirstId, $v);
            }
            unset($k, $v);
            //进行更新  用户状态 和 卡片状态
            if ($collect_all == 1 && count($upLog_id) > 0) {
                $user->collect_all = 1;
                $user->ok_at = date('Y-m-d H:i:s');
                $user->save();
                if ($request) {
                    $request->session()->put($this->sessionName('user'), $user);
                    $request->session()->save();
                }
                CollectLog::whereIn('id', $upLog_id)->update(['is_expend' => 1]);
            }
        }
    }

    //************ 公共方法 *************//
    public function initSetting($cardList, $type = '', $exclusion = [])
    {
        $card = [];
        foreach ($cardList as $value) {
            if ($value['number'] > 0) {
                $data = [
                    'id' => $value['id'],
                    'title' => $value['type'],
                    'weight' => $value['prob']
                ];
                switch ($type) {
                    case 'first':
                        if ($value['first_sw']) {
                            $card[] = $data;
                        }
                        break;
                    case 'normal':
                        if ($value['p_number'] == 0) {
                            $card[] = $data;
                            break;
                        }
                        if ($value['p_number'] > array_get($exclusion, $value['id'])) {
                            $card[] = $data;
                            break;
                        }
                        break;
                    default:
                        $card[] = $data;
                }
            }
        }
        unset($value);
        if (count($card) == 0) {
            array_push($card, [
                'id' => 0,
                'title' => '抱歉,没有符合可抽奖品了!',
                'weight' => 100
            ]);
        }
        return $card;
    }

    public function initCardIndex($card, $myCard)
    {
        $index = 0;
        $cardIndex = [];
        $topList = [];
        foreach ($card as $k => $v) {
            $initData = [
                'text' => $v['text'],
                'image' => $v['image'],
                'type' => $v['type'],
                'is_have' => 0,
                'is_expend' => 0,
                'log_id' => $k,
            ];
            $indexArr = [];
            if (array_get($myCard, $v['id'])) {
                foreach ($myCard[$v['id']] as $value) {
                    $initData['log_id'] = $value['id'];
                    $initData['is_have'] = 1;
                    $initData['is_expend'] = $value['is_expend'];
                    $topList[] = $initData;
                    $indexArr[] = $index;
                    $index++;
                }
                unset($value);
            } else {
                $topList[] = $initData;
                $indexArr[] = $index;
                $index++;
            }
            $cardIndex[] = $indexArr;
        }
        unset($k, $v);
        return ['cardIndex' => $cardIndex, 'topList' => $topList];
    }

    public function sessionName($str)
    {
        return 'collectCard:' . $str . ':' . $this->token . ':' . $this->aid;
    }

    public function cacheName($str)
    {
        return 'collectCard:' . $str . ':' . $this->token . ':' . $this->aid;
    }

}
