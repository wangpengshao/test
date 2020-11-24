<?php

namespace App\Http\Controllers\Web\CollectCard;

use App\Api\Helpers\ApiResponse;
use App\Models\CollectCard\CollectLog;
use App\Models\CollectCard\CollectRedPack;
use App\Models\CollectCard\CollectReward;
use App\Models\CollectCard\SelfService;
use App\Models\Wechat\IndexMenu;
use App\Models\Wechat\Wechatapp;
use App\Services\MenuService;
use App\Services\WebOAuthService;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Vinkla\Hashids\Facades\Hashids;

class IndexController extends BaseController
{
    use ApiResponse;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        View::share('config', $this->config);
        View::share('basisWhere', $this->basisWhere);
        View::share('indexUrl', route('CollectCard::index', $this->basisWhere));
    }

    //页面 ===> 首页
    public function index(Request $request, WebOAuthService $webOAuthService)
    {
        if ($this->config['start_at'] > date('Y-m-d H:i:s')) {
            $request->session()->flash('alertInfo', '集卡活动还未开始哦,请到' . $this->config['start_at'] . '之后再来!');
            return redirect(route('CollectCard::showAlert', $this->basisWhere));
        }
        /*session start*/
        $fansInfo = $webOAuthService->checkOauth();
        $urlSession = $request->session()->pull($this->sessionName('url'));
        if ($urlSession) {    //判断是否存在重定向地址
            return redirect($urlSession);
        }
        $user = $this->checkUser($request, $fansInfo);
        if ($request->session()->has($this->sessionName('event'))) {  //判断是否存在还没处理事件
            return redirect(route('CollectCard::channel', $this->basisWhere));
        }
        /* init start */
        $cardList = $this->getCardList();

        $groupCount = CollectLog::groupCount($user['id'], $this->basisWhere);
        $groupFirstId = CollectLog::groupFirstId($user['id'], $this->basisWhere);
        /* 判断是否集齐并更新 &$user */
        $this->checkUpCollect($user, $cardList, $groupFirstId);
//        dd($user);
//        if ($user['collect_all'] != 1 && $this->config['end_at'] > date('Y-m-d H:i:s')) {
//            $requireCard = $cardList->whereStrict('type', 0)->pluck('id');
//            $collect_all = 1;
//            $upLog_id = [];
//            foreach ($requireCard as $k => $v) {
//                if (!array_get($groupFirstId, $v)) {
//                    $collect_all = 0;
//                    break;
//                }
//                $upLog_id[] = array_get($groupFirstId, $v);
//            }
//            unset($k, $v);
//            //进行更新  用户状态 和 卡片状态
//            if ($collect_all == 1 && count($upLog_id) > 0) {
//                $user->collect_all = 1;
//                $user->ok_at = date('Y-m-d H:i:s');
//                $user->save();
//                CollectLog::whereIn('id', $upLog_id)->update(['is_expend' => 1]);
//            }
//        }
        /* 领取奖励展示 */
        $collectReward = [];
        $get_redpage = 0;
        if ($user['collect_all'] == 1) {
            $collectReward = CollectReward::getReward($user['id'], $this->basisWhere)->first();
            if ($collectReward) {
                if ($collectReward['type'] == 1 || $collectReward['type'] == 2) {
                    if (!$collectReward['redpack_id']) {
                        $money = $collectReward->hasOnePrize->money;
                    } else {
                        $money = CollectRedPack::where([
                            'id' => $collectReward['redpack_id'],
//                            'status' => 0,
//                            'isValid' => 1
                        ])->value('money');
                    }
                    if ($collectReward['status'] !== 1 && $collectReward['is_get'] == 1) {
                        $get_redpage = 1;
                    }
                    $collectReward->money = $money;
                } else {
                    $prize = $collectReward->hasOnePrize;
                    $collectReward->prize_image = $prize['image'];
                    $collectReward->prize_title = $prize['title'];
                }
            }
        }
        return view('web.collectCard.index', [
            'card' => $cardList,
            'get_redpage' => $get_redpage,
            'myCount' => $groupCount,
            'listFirstId' => $groupFirstId,
            'htmlConfig' => $this->getHtmlConf(),
            'collectReward' => $collectReward,
            'user' => $user,
            'app' => Wechatapp::initialize($this->token),
            'shareUrl' => route('CollectCard::index', $this->basisWhere + ['user_id' => Hashids::encode($user['id'])]),
        ]);
    }

    //页面 ===> 我的卡包
    public function myCard(Request $request, WebOAuthService $webOAuthService)
    {
        if ($this->config['start_at'] > date('Y-m-d H:i:s')) {
            $request->session()->flash('alertInfo', '集卡活动还未开始哦,请到' . $this->config['start_at'] . '之后再来!');
            return redirect(route('CollectCard::showAlert', $this->basisWhere));
        }
        $fansInfo = $webOAuthService->checkOauth();
        $user = $this->checkUser($request, $fansInfo, $this->basisWhere);

        $cardList = $this->getCardList();
        //判断卡片是否集齐
        $this->checkUpCollect($user, $cardList);

        $myCard = CollectLog::getMyLog($user['id'], $this->basisWhere)->get(['id', 'created_at', 'c_id', 'is_expend'])
            ->groupBy('c_id')->toArray();
        //计算卡的位置
        $cardIndexData = $this->initCardIndex($cardList, $myCard);
        $topList = $cardIndexData['topList'];
        foreach ($topList as $k => $v) {
            if ($v['is_expend'] != 1) {
                $params = [
                    'token' => $this->token,
                    'a_id' => $this->aid,
                    'user_id' => Hashids::encode($user['id']),
                    'card_id' => Hashids::encode($v['log_id']),
                    'sign' => md5($user['id'] . config('envCommon.MENU_ENCRYPT_STR') . $v['log_id']),
                ];
                $topList[$k]['shareUrl'] = route('CollectCard::shareGiveCard', $params);
            }
        }
        return view('web.collectCard.myCard', [
            'card' => $cardList,
            'myCard' => $myCard,
            'cardIndex' => $cardIndexData['cardIndex'],
            'topList' => $topList,
        ]);
    }

    //页面 ===> 活动规则
    public function rule(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $this->checkUser($request, $fansInfo, $this->basisWhere);
        return view('web.collectCard.rule');
    }

    //页面 ===> 体验说明
    public function strategy(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $user = $this->checkUser($request, $fansInfo, $this->basisWhere);
        $myLog = CollectLog::getMyAllLog($user['id'], $this->basisWhere, 'DESC')->where('giver', 0)
            ->get(['created_at', 'origin_type', 'origin_id', 'id', 'c_id']);
        $taskList = $this->getShowTask();
        $menu_id = $taskList->pluck('menu_id')->unique()->reject(function ($menu_id) {
            return empty($menu_id);
        })->toArray();

        $menu = collect();
        if ($menu_id) {
            $menu = IndexMenu::where('token', $this->token)->whereIn('id', $menu_id)->get();
        }
        $menuService = MenuService::make('self', $this->token);
        $taskList->each(function ($item) use ($menu, $menuService, $fansInfo) {
            $url = 'javascript:void(0)';
            if ($item['menu_id'] && $menuData = $menu->firstWhere('id', $item['menu_id'])) {
                $url = $menuService->returnUrl($menuData, $fansInfo);
            }
            $item->url = $url;
        });
        $isOkNumber = 0;
        $nperList = $taskList->whereStrict('type', 0);
        $daysList = $taskList->where('type', 1);

        $haveCardList = collect();
        $daysList->map(function ($item) use ($myLog, &$isOkNumber, &$haveCardList) {
            $item->is_ok = 0;
            $whereLog = $myLog->where('origin_type', $item['origin_type']);
            if ($item['origin_type'] == 1) {
                $whereLog = $whereLog->where('origin_id', $item['origin_id']);
            } else {
                $whereLog = $whereLog->where('origin_id', $item['id']);
            }
            if (!$whereLog->isEmpty()) {
                $haveCardList->push($whereLog);
            }
            $whereLog = $whereLog->sortByDesc('created_at')->first();
            if ($whereLog && $whereLog['created_at'] > date('Y-m-d')) {
                $isOkNumber++;
                $item->is_ok = 1;
            }
        });

        $nperList->map(function ($item) use ($myLog, &$isOkNumber, &$haveCardList) {
            $item->is_ok = 0;
            $whereLog = $myLog->where('origin_type', $item['origin_type']);
            if ($item['origin_type'] == 1) {
                $whereLog = $whereLog->where('origin_id', $item['origin_id']);
            } else {
                $whereLog = $whereLog->where('origin_id', $item['id']);
            }
            $isEmpty = $whereLog->isEmpty();
            if (!$isEmpty) {
                $haveCardList->push($whereLog);
                $isOkNumber++;
                $item->is_ok = 1;
            }
        });

        if (!$haveCardList->isEmpty()) {
            $haveCardList = $haveCardList->flatten()->sortBy('created_at');
        }

        return view('web.collectCard.strategy', [
            'htmlConfig' => $this->getHtmlConf(),
            'taskList' => $taskList,
            'nperList' => $nperList,
            'daysList' => $daysList,
            'haveCardList' => $haveCardList,
            'isOkNumber' => $isOkNumber,
            'card' => $this->getCardList()->pluck('image', 'id')
        ]);
    }

    //页面 ===> 首次参与说明
    public function firstTime(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $user = $this->checkUser($request, $fansInfo, $this->basisWhere);
        $collectLog = CollectLog::where($this->basisWhere)
            ->where([
                'user_id' => $user['id'],
                'origin_type' => 1,
                'origin_id' => 4
            ])->first();
        $firstCard = $collectLog->hasOneCard;
        return view('web.collectCard.firstTime', [
            'card' => $this->getCardList(),
            'htmlConf' => $this->getHtmlConf(),
            'collectLog' => $collectLog,
            'firstCard' => $firstCard
        ]);

    }

    public function selfService(Request $request, WebOAuthService $webOAuthService)
    {
        if ($request->isMethod('post')) {
            $list = SelfService::where('token', $this->token)->where('status', 1)->get();
            if ($list->isEmpty()) {
                return $this->message('暂无机器数据', false);
            }
            //单起点 多终点
            $form = $request->input('latitude') . ',' . $request->input('longitude');
            $to = [];
            foreach ($list as $k => $v) {
                $to[] = $v['lat'] . ',' . $v['lng'];
            }
            $to = implode(';', $to);
            $url = 'https://apis.map.qq.com/ws/distance/v1/?';
            $params = http_build_query([
                'mode' => 'walking',
                'from' => $form,
                'to' => $to,
                'key' => config('envCommon.TENCENT_MAP_KEY')
            ]);
            $http = new Client();
            $response = $http->get($url . $params);
            $response = json_decode((string)$response->getBody(), true);
            if ($response['status'] !== 0) {
                return $this->message($response['message'], false);
            }
            $elements = $response['result']['elements'];
            $data = [];
            foreach ($elements as $k => $v) {
                $data[$v['to']['lat'] . ',' . $v['to']['lng']] = $v['distance'];
            }
            foreach ($list as $k => $v) {
                $list[$k]['distance'] = 0;
                if (array_get($data, $v['lat'] . ',' . $v['lng'])) {
                    $list[$k]['distance'] = array_get($data, $v['lat'] . ',' . $v['lng']);
                }
            }
            return $this->success($list, true);
        }
        $fansInfo = $webOAuthService->checkOauth();
        $user = $this->checkUser($request, $fansInfo, $this->basisWhere);
        $app = Wechatapp::initialize($this->token);
        return \view('web.collectCard.selfService', ['app' => $app]);
    }


    //页面 ===> 提示中转
    public function showAlert()
    {
        return view('web.collectCard.showAlert');
    }


}
