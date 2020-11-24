<?php

namespace App\Http\Controllers\Web\CollectCard;

use App\Api\Helpers\ApiResponse;
use App\Models\CollectCard\CollectLog;
use App\Models\CollectCard\CollectUsers;
use App\Models\Wechat\Fans;
use App\Models\Wechat\Wechatapp;
use App\Services\LotteryService;
use App\Services\WebOAuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Vinkla\Hashids\Facades\Hashids;

class ShareController extends BaseController
{
    use ApiResponse;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        View::share('config', $this->config);
        View::share('indexUrl', route('CollectCard::index', $this->basisWhere));
    }

    //页面 ===> 分享说明
    public function share(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $user = $this->checkUser($request, $fansInfo);
        $shareUser = CollectUsers::where($this->basisWhere)->where('parent_id', $user['id'])->with('hasOneFansInfo')->get();
        $shareCount = $shareUser->count();
        $task = $this->getTask(1, 10)->first();          //拉新任务处理
        $showTask = [];
        if ($task && $sub_data = array_filter($task['sub_data'])) {
            $sub_data = array_sort($sub_data);
            $myTaskLog = CollectLog::getTaskLog($this->basisWhere, $user['id'], 1, 10)
                ->where('giver', 0)->with('hasOneCard')->get(['c_id', 'id']);
            $logKey = 0;
            foreach ($sub_data as $k => $v) {
                $card = ['is_get' => 0, 'requireNum' => $v, 'image' => ''];
                if ($shareCount >= $v) {
                    $card['is_get'] = 1;
                    if (array_get($myTaskLog, $logKey)) {
                        $card['image'] = $myTaskLog[$logKey]->hasOneCard->image;
                    }
                }
                $showTask[] = $card;
                $logKey++;
            }
        }
        return view('web.collectCard.share', [
            'htmlConfig' => $this->getHtmlConf(),
            'shareUser' => $shareUser,
            'showTask' => $showTask,
            'shareUrl' => route('CollectCard::index', $this->basisWhere + ['user_id' => Hashids::encode($user['id'])]),
            'getUrl' => route('CollectCard::getShareTaskCard', $this->basisWhere),
            'app' => Wechatapp::initialize($this->token)
        ]);
    }

    public function getShareTaskCard(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $user = $this->checkUser($request, $fansInfo);
        $shareCount = CollectUsers::where($this->basisWhere)->where('parent_id', $user['id'])->count();
        $task = $this->getTask(1, 10)->first();          //拉新任务处理
        $is_valid = 0;
        $can_number = 0;
        $my_number = CollectLog::getTaskLog($this->basisWhere, $user['id'], 1, 10)->where('giver', 0)->count();
        if ($task && $sub_data = array_filter($task['sub_data'])) {
            $sub_data = array_sort($sub_data);
            foreach ($sub_data as $k => $v) {
                if ($shareCount >= $v) {
                    $can_number++;
                }
            }
            if ($can_number > $my_number) {
                $is_valid = 1;
            }
        }
        if ($is_valid == 1) {
            $cardList = $this->getCardList();
            if ($my_number == 0 && $task['first_cid']) {
                $c_id = $task['first_cid'];
            } else {
                $c_id = '';
                $myCount = CollectLog::where($this->basisWhere)->where('user_id', $user['id'])->groupBy('c_id')
                    ->select(DB::raw("count(1) as count"), 'c_id')->pluck('count', 'c_id')->toArray();
                $result = LotteryService::make()->go($this->initSetting($cardList, 'normal', $myCount));
                if ($result['id']) {
                    $c_id = $result['id'];
                }
            }
            if ($c_id) {
                $createLog = $this->createTaskLog($user, 1, 10, $c_id);
                $card = $cardList->where('id', $c_id)->first();
                $response = [
                    'cardText' => $card['text'],
                    'cardImage' => $card['image'],
                    'taskText' => $task['get_tip'],
                    'cardUrl' => route('CollectCard::myCard', $this->basisWhere) . '#' . $createLog['id'],
                ];
                /* 判断是否集齐并更新 &$user */
                $this->checkUpCollect($user, $cardList);

                return $this->success($response, true);
            }
        }
        return $this->success(['message' => '系统繁忙，请稍后再试!'], false);
    }

    //页面&&操作 ===> 分享页面（赠送卡片）
    public function shareGiveCard(Request $request, WebOAuthService $webOAuthService)
    {
        if (!$request->filled(['user_id', 'card_id', 'sign'])) {
            abort(404);
        }
        $user_id = Hashids::decode($request->input('user_id'));
        $card_id = Hashids::decode($request->input('card_id'));
        $sign = $request->input('sign');
        if (empty($user_id) || empty($card_id) ||
            $sign != md5($user_id[0] . config('envCommon.MENU_ENCRYPT_STR') . $card_id[0])) {
            abort(404);
        }
        $giveUser = CollectUsers::where('id', $user_id[0])->first();
        $giveUserInfo = Fans::where('token', $this->token)->where('openid', $giveUser['openid'])->first(['nickname']);
        $giveCard_id = $card_id[0];
        $fansInfo = $webOAuthService->checkOauth();

        $user = $this->checkUser($request, $fansInfo);
        //查询卡片是否已被领取
        $giveCard = CollectLog::where($this->basisWhere)->where([
            'user_id' => $giveUser['id'],
            'id' => $giveCard_id,
            'isValid' => 1,
            'is_expend' => 0
        ])->first();

        if (empty($giveCard)) {
            $request->session()->flash('alertInfo', '来晚了，卡片已被领走了!');  //闪存
            return redirect(route('CollectCard::showAlert', $this->basisWhere));
        }
        $card = $giveCard->hasOneCard;
        $isFriend = ($user['id'] == $giveUser['id']) ? 0 : 1;
        $app = Wechatapp::initialize($this->token);
        return view('web.collectCard.shareGiveCard', [
            'card' => $card,
            'app' => $app,
            'giveUserInfo' => $giveUserInfo,
            'isFriend' => $isFriend,
            'giveCard_id' => $giveCard_id,
            'giveUser_id' => $giveUser['id']
        ]);
    }

    //操作 ===> 领取卡片
    public function getGiveCard(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $user = $this->checkUser($request, $fansInfo);
        if (!$request->filled(['giveCard_id', 'giveUser_id'])) {
            return $this->message('缺少必填参数', false);
        }
        $giveCard_id = $request->input('giveCard_id');
        $giveUser_id = $request->input('giveUser_id');
        $giveCard = CollectLog::where($this->basisWhere)
            ->where(['user_id' => $giveUser_id, 'id' => $giveCard_id, 'isValid' => 1, 'is_expend' => 0])->first();
        if (empty($giveCard)) {
            return $this->message('手慢了，卡片已被别人领走咯!', false);
        }
        $create = $giveCard->only(['token', 'a_id', 'c_id', 'isValid', 'origin_type', 'origin_id']);
        $create['user_id'] = $user['id'];
        $create['giver'] = $giveUser_id;
        $status = CollectLog::create($create);
        if ($status) {
            $giveCard->isValid = 0;
            $giveCard->save();
            return $this->message('领取成功,正在为你跳转首页!', true);
        }
        return $this->message('领取失败,请稍后再试!', false);
    }


}
