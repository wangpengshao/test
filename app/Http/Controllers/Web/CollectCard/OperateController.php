<?php

namespace App\Http\Controllers\Web\CollectCard;

use App\Api\Helpers\ApiResponse;
use App\Models\CollectCard\CollectLog;
use App\Models\CollectCard\CollectPrize;
use App\Models\CollectCard\CollectRedPack;
use App\Models\CollectCard\CollectReward;
use App\Models\CollectCard\CollectTask;
use App\Models\CollectCard\CollectUsers;
use App\Models\Wechat\Fans;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use App\Services\LotteryService;
use App\Services\PayHelper;
use App\Services\RandRedPackage;
use App\Services\WebOAuthService;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Vinkla\Hashids\Facades\Hashids;

class OperateController extends BaseController
{
    use ApiResponse;

    public function __construct(Request $request)
    {
        parent::__construct($request);
        View::share('indexUrl', route('CollectCard::index', $this->basisWhere));
    }

    //操作 ===> 内部领取卡片
    public function channel(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        if ($this->config['subscribe_sw'] == 1) {
            $subscribe = $webOAuthService->checkSubscribe($fansInfo['openid']);
            if ($subscribe != 1) {
                $request->session()->put($this->sessionName('url'), $request->fullUrl());
                return view('web.collectCard.showQrCode', [
                    'qrcode' => Wxuser::getCache($this->token)->qr_code,
                    'config' => $this->config
                ]);
            }
        }
        /* init start */
        $cardList = $this->getCardList();
        $logList = collect();
        $LotteryService = LotteryService::make();

        $user = CollectUsers::where($this->basisWhere)->where('openid', $fansInfo['openid'])->first();
        if (empty($user)) {      //新参与活动用户
            $createUser = [
                'origin_type' => 1,
                'openid' => $fansInfo['openid'],
                'origin_id' => 0,
                'a_id' => $this->aid,
                'token' => $this->token,
                'last_at' => date('Y-m-d H:i:s')
            ];
            if ($request->filled(['user_id'])) {
                $parent_id = array_get(Hashids::decode($request->input('user_id')), 0); //邀请者ID
                if ($parent_id) {
                    $createUser['parent_id'] = $parent_id;          //判断邀请活动奖励
                    $this->sendSubTip($parent_id);                  //达标即给邀请者发送通知
                }
            }
            $user = CollectUsers::create($createUser);
            //查看是否有新用户必送卡片任务  1-4
            $firstTask = $this->getTask(1, 4)->first();
            if ($firstTask) {
                if ($firstTask['first_cid']) {
                    $c_id = $firstTask['first_cid'];
                } else {
                    $result = $LotteryService->go($this->initSetting($cardList, 'first'));
                    $c_id = $result['id'];
                }
                if ($c_id) {
                    $createLog = $this->createTaskLog($user, 1, 4, $c_id);
                    $createLog->get_tip = $firstTask['get_tip'];
                    $logList->push($createLog);
                }
            }
            //查看是否有绑定读者证任务  1-1
            $bindTask = $this->getTask(1, 1)->first();
            if ($bindTask) {
                $reader = Reader::checkBind($fansInfo['openid'], $this->token)->first(['rdid']);
                if ($reader) {
                    if ($bindTask['first_cid']) {
                        $c_id = $bindTask['first_cid'];
                    } else {
                        $result = $LotteryService->go($this->initSetting($cardList, 'normal'));
                        $c_id = $result['id'];
                    }
                    if ($c_id) {
                        $createLog = $this->createTaskLog($user, 1, 1, $c_id);
                        $createLog->get_tip = $bindTask['get_tip'];
                        $logList->push($createLog);
                    }
                }
            }
        }
        //session event 集合处理
        $eventSession = $request->session()->pull($this->sessionName('event'));
        $eventCache = Cache::pull('event:' . $this->token . ':' . $fansInfo['openid']);
        if ($eventCache && $eventCache['a_id'] == $this->aid) {
            $eventSession = ($eventSession) ?: [];
            array_push($eventSession, $eventCache);
        }
        if (!empty($eventSession)) {
            foreach ($eventSession as $k => $v) {
                $collectTask = $this->getTask($v['origin_type'], $v['origin_id'])->first();
                $checkStatus = $this->checkTaskValid($user, $collectTask);
                if ($checkStatus['is_valid'] == 1) {
                    if ($checkStatus['count'] == 0 && $collectTask['first_cid']) {
                        $c_id = $collectTask['first_cid'];
                    } else {
                        $c_id = '';
                        $result = $LotteryService->go($this->initTaskSetting($collectTask));
                        if ($result['id'] == 1) {
                            $myCount = CollectLog::where($this->basisWhere)->where('user_id', $user['id'])->groupBy('c_id')
                                ->select(DB::raw("count(1) as count"), 'c_id')->pluck('count', 'c_id')->toArray();
                            $result = $LotteryService->go($this->initSetting($cardList, 'normal', $myCount));
                            if ($result['id']) {
                                $c_id = $result['id'];
                            }
                        }
                    }
                    if ($c_id) {
                        $createLog = $this->createTaskLog($user, $v['origin_type'], $v['origin_id'], $c_id);
                        $createLog->get_tip = $collectTask['get_tip'];
                        $logList->push($createLog);
                    }
                }
            }
        }
        //朋友赠送
        if ($request->filled(['user_id', 'card_id', 'sign'])) {
            $user_id = array_get(Hashids::decode($request->input('user_id')), 0);
            $card_id = array_get(Hashids::decode($request->input('card_id')), 0);
            $sign = $request->input('sign');
            if ($user_id && $card_id && $sign == md5($user_id . config('envCommon.MENU_ENCRYPT_STR') . $card_id)) {
                $giveOpenid = CollectUsers::where('id', $user_id)->value('openid');
                $nickname = Fans::where(['token' => $this->token, 'openid' => $giveOpenid])->value('nickname');
                $giveCard = CollectLog::where(['user_id' => $user_id, 'id' => $card_id, 'isValid' => 1])->first();
                if ($giveCard) {
                    $giveCard->nickname = $nickname;
                    $logList->push($giveCard);
                }
            }
        }

        if ($logList->isEmpty()) {
            $request->session()->flash('alertInfo', '没有抽到卡哦,下次再接再厉！');  //闪存
            return redirect(route('CollectCard::showAlert', $this->basisWhere));
        }

        $this->checkUpCollect($user, $cardList, '', $request);

        return view('web.collectCard.channel', [
            'config' => $this->config,
            'card' => $this->getCardList(),
            'user' => $user,
            'logList' => $logList,
            'basisWhere' => $this->basisWhere,
        ]);
    }

    //操作 ===> 外部领取卡片
    public function checkSerial(Request $request, WebOAuthService $webOAuthService)
    {
        $uuid = $request->input('serial');
        $sign = $request->input('sign');
        if (!$uuid || !$sign || $sign != md5(config('envCommon.MENU_ENCRYPT_STR') . $uuid)) abort(404);
        $fansInfo = $webOAuthService->checkOauth();
        if ($this->config['subscribe_sw'] == 1) {
            $subscribe = $webOAuthService->checkSubscribe($fansInfo['openid']);
            if ($subscribe != 1) {
                $request->session()->put($this->sessionName('url'), $request->fullUrl());
                return view('web.collectCard.showQrCode', [
                    'qrcode' => Wxuser::getCache($this->token)->qr_code,
                    'config' => $this->config
                ]);
            }
        }
        $showAlertUrl = route('CollectCard::showAlert', $this->basisWhere);

        $uuidCache = Cache::get('CCard' . ':' . $uuid);
        if (empty($uuidCache)) {
            $request->session()->flash('alertInfo', '抱歉,当前二维码已失效');
            return redirect($showAlertUrl);
        }
        $date = date('Y-m-d H:i:s');
        if ($this->config['start_at'] > $date || $this->config['end_at'] < $date || $this->config['status'] != 1) {
            $request->session()->flash('alertInfo', '抱歉,当前时间不是活动有效时间!');
            return redirect($showAlertUrl);
        }
        $reader = Reader::checkBind($fansInfo['openid'], $this->token)->first(['rdid']);
        if (empty($reader)) {
            $cacheKey = 'fEvent:' . $this->token . ':' . $fansInfo['openid'];
            $cache = [
                'typeName' => 'collectCard',
                'typeData' => [
                    'url' => $request->fullUrl()
                ],
            ];
            Cache::put($cacheKey, $cache, 20);
            $bindUrl = str_replace('{token}', $this->token, config('vueRoute.bindReader'));
            return redirect($bindUrl . '?rdid=' . $uuidCache['rdid']);

        } elseif ($reader['rdid'] != $uuidCache['rdid']) {
            $request->session()->flash('alertInfo', '抱歉,当前绑定读者与二维码的读者不匹配,无法获取卡片');
            return redirect($showAlertUrl);
        }
        $collectTask = $this->getTask()->where('id', $uuidCache['origin_id'])->first();
        if (empty($collectTask) || $collectTask['status'] != 1) {
            $request->session()->flash('alertInfo', '抱歉,当前任务已关闭');
            return redirect($showAlertUrl);
        }
        /*init start */
        Cache::forget('CCard' . ':' . $uuid);
        $logList = collect();
        $cardList = $this->getCardList();
        $origin_type = 2;                                   //默认第三方系统的type
        $origin_id = $uuidCache['origin_id'];
        $LotteryService = LotteryService::make();

        $user = CollectUsers::where($this->basisWhere)->where('openid', $fansInfo['openid'])->first();
        if (empty($user)) {         //新用户
            $create = [
                'origin_type' => $origin_type,
                'openid' => $fansInfo['openid'],
                'origin_id' => $origin_id,
                'a_id' => $this->aid,
                'token' => $this->token,
                'last_at' => $date
            ];
            $user = CollectUsers::create($create);
            //查看是否有新用户必送卡片任务
            $firstTask = $this->getTask(1, 4)->first();
            if ($firstTask) {
                if ($firstTask['first_cid']) {
                    $c_id = $firstTask['first_cid'];
                } else {
                    $result = $LotteryService->go($this->initSetting($cardList, 'first'));
                    $c_id = $result['id'];
                }
                $createLog = $this->createTaskLog($user, 1, 4, $c_id, $reader['rdid']);
                $createLog->get_tip = $firstTask['get_tip'];
                $logList->push($createLog);
            }

            //查看是否有绑定读者证任务  1-1
            $bindTask = $this->getTask(1, 1)->first();
            if ($bindTask) {
                $reader = Reader::checkBind($fansInfo['openid'], $this->token)->first(['rdid']);
                if ($reader) {
                    if ($bindTask['first_cid']) {
                        $c_id = $bindTask['first_cid'];
                    } else {
                        $result = $LotteryService->go($this->initSetting($cardList, 'normal'));
                        $c_id = $result['id'];
                    }
                    if ($c_id) {
                        $createLog = $this->createTaskLog($user, 1, 1, $c_id);
                        $createLog->get_tip = $bindTask['get_tip'];
                        $logList->push($createLog);
                    }
                }
            }
        }
        //进入任务逻辑
        $checkStatus = $this->checkTaskValid($user, $collectTask);
        if ($checkStatus['is_valid'] == 1) {
            if ($checkStatus['count'] == 0 && $collectTask['first_cid']) {
                $c_id = $collectTask['first_cid'];
            } else {
                $c_id = '';
                $result = $LotteryService->go($this->initTaskSetting($collectTask));
                if ($result['id'] == 1) {
                    $myCount = CollectLog::where($this->basisWhere)->where('user_id', $user['id'])->groupBy('c_id')
                        ->select(DB::raw("count(1) as count"), 'c_id')->pluck('count', 'c_id')->toArray();
                    $result = $LotteryService->go($this->initSetting($cardList, 'normal', $myCount));
                    if ($result['id']) {
                        $c_id = $result['id'];
                    }
                }
            }
            if ($c_id) {
                $createLog = $this->createTaskLog($user, $origin_type, $origin_id, $c_id, $reader['rdid']);
                $createLog->get_tip = $collectTask['get_tip'];
                $logList->push($createLog);
                if ($createLog) {
                    $this->checkUpCollect($user, $cardList, '', $request);
//                    if ($user['collect_all'] != 1) {
//                        $groupFirstId = CollectLog::groupFirstId($user['id'], $this->basisWhere);
//                        $requireCard = $cardList->whereStrict('type', 0)->pluck('id');
//                        $collect_all = 1;
//                        $upLog_id = [];
//                        foreach ($requireCard as $k => $v) {
//                            if (!array_get($groupFirstId, $v)) {
//                                $collect_all = 0;
//                                break;
//                            }
//                            $upLog_id[] = array_get($groupFirstId, $v);
//                        }
//                        unset($k, $v);
//                        //进行更新  用户状态 和 卡片状态
//                        if ($collect_all == 1 && count($upLog_id) > 0) {
//                            $user->collect_all = 1;
//                            $user->ok_at = $date;
//                            $user->save();
//                            $request->session()->put($this->sessionName('user'), $user);
//                            $request->session()->save();
//                            CollectLog::whereIn('id', $upLog_id)->update(['is_expend' => 1]);
//                        }
//                    }
                }
            }
        }

        if ($logList->isEmpty()) {
            $request->session()->flash('alertInfo', '没有抽到卡哦,下次再接再厉！');  //闪存
            return redirect($showAlertUrl);
        }


        return view('web.collectCard.checkSerial', [
            'config' => $this->config,
            'card' => $cardList,
            'logList' => $logList,
            'basisWhere' => $this->basisWhere,
        ]);
    }

    //操作 ===> 兑换卡片
    public function exchangeCard(Request $request, WebOAuthService $webOAuthService)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $user = $this->checkUser($request, $fansInfo, $this->basisWhere);
        if (!$request->filled(['ccard_id', 'c_id'])) {
            return $this->message('缺少必填参数', false);
        }
        $log_id = $request->input('ccard_id');
        $c_id = $request->input('c_id');
        //判断是否是自己的万能卡
        $first = CollectLog::where(['id' => $log_id, 'user_id' => $user['id'], 'isValid' => 1])->first();
        if (empty($first)) {
            return $this->message('非法访问!', false);
        }
        $create = array_except($first->toArray(), ['id', 'created_at']);
        $create['c_id'] = $c_id;
        $create = CollectLog::create($create);
        if ($create) {
            $first->isValid = 0;
            $first->save();
            return $this->success([
                'id' => $create['id'],
                'message' => '兑换成功'
            ], true);
        }
        return $this->message('系统繁忙,兑换失败!', false);
    }

    //操作 ===> 进行领奖
    public function getAward(Request $request, WebOAuthService $webOAuthService)
    {
//        if ($this->token !== '18c6684c') {
//            return $this->message('抱歉，服务暂时关闭!', false);
//        }
        $fansInfo = $webOAuthService->checkOauth();
        $user = $this->checkUser($request, $fansInfo);
        $collectReward = [];
        if ($user['collect_all'] == 1) {
            $collectReward = CollectReward::getReward($user['id'], $this->basisWhere)->first();
            //判断是哪种类型的集卡活动
            if ($this->config['type'] == 1 && empty($collectReward)) {
                if ($this->config['end_at'] < date('Y-m-d H:i:s')) {
                    return $this->message('抱歉，活动已结束!', false);
                }
                //即时开奖  即时抽奖  库存大于0
                $collectPrize = CollectPrize::where('a_id', $this->aid)->where('inventory', '>', '0')->get();
                $createReward = [
                    'token' => $this->token,
                    'a_id' => $this->aid,
                    'type' => 0,
                    'user_id' => $user['id'],
                    'prize_id' => 0,
                    'is_get' => 0,
                    'redpack_id' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                if (!$collectPrize->isEmpty()) {
                    //拼手气红包进行拆分 start
                    $type2 = $collectPrize->whereStrict('type', 2);
                    $firstRedType = $type2->firstWhere('money', '>', 0);
                    if ($firstRedType) {
                        //暂时只支持第一个拼手气红包
                        $redRewardCount = CollectReward::where($this->basisWhere)->where('prize_id', $firstRedType['id'])->count();
                        if ($redRewardCount > 0) {
                            $redPageCount = CollectRedPack::where('p_id', $firstRedType['id'])->where('isValid', 1)->count();
                            if ($redRewardCount >= $redPageCount) {
                                $firstRedType = null;
                            }
                        } else {
                            //尚未开始发 判断红包是否裂变了
                            $is_rand = CollectRedPack::where('p_id', $firstRedType['id'])->where('isValid', 1)->exists();
                            if (!$is_rand) {
                                $randRedPackage = RandRedPackage::setOptions($firstRedType['money'], $firstRedType['pack_n'], $firstRedType['min_n'], $firstRedType['max_n'])->create();
                                $newRedPackage = [];
                                foreach ($randRedPackage as $k => $v) {
                                    $newRedPackage[] = [
                                        'p_id' => $firstRedType['id'],
                                        'money' => $v,
                                        'status' => 0,
                                        'isValid' => 1,
                                        'created_at' => date('Y-m-d H:i:s'),
                                    ];
                                }
                                CollectRedPack::insert($newRedPackage);
                                unset($newRedPackage, $randRedPackage);
                            }
                        }
                    }
                    //拼手气红包进行拆分 end
                    //开始抽奖  start
                    $settings = [];
                    foreach ($collectPrize as $k => $v) {
                        $settings[] = [
                            'id' => $v['id'],
                            'title' => $v['title'],
                            'weight' => $v['weight'],
                        ];
                    }
                    $result = LotteryService::make()->go($settings);
                    $findPrize = $collectPrize->firstWhere('id', $result['id']);
                    $createReward['prize_id'] = $findPrize['id'];
                    // 普通红包!
                    if ($findPrize['type'] == 1) {
                        $createRedPack = [
                            'p_id' => $findPrize['id'],
                            'money' => $findPrize['money'],
                            'status' => 0,
                            'isValid' => 1,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        $createRedPack = CollectRedPack::create($createRedPack);
                        $createReward['redpack_id'] = $createRedPack['id'];
                        $createReward['type'] = 1;
                        $findPrize->inventory--;
                        $findPrize->save();

                    } elseif ($findPrize['type'] == 2) {
                        $randRedPackage = CollectRedPack::where('p_id', $findPrize['id'])->where('isValid', 1)->inRandomOrder()
                            ->first(['id']);
                        $createReward['redpack_id'] = $randRedPackage['id'];
                        $createReward['type'] = 2;
                    } else {
                        $findPrize->inventory--;
                        $findPrize->save();
                    }
                    //开始抽奖  end
                }
                $collectReward = CollectReward::create($createReward);
            }
        }
        if (empty($collectReward)) {
            return $this->message('抱歉，您尚未符合领取要求!', false);
        }
        if ($collectReward['is_get'] == 1) {
            return $this->message('抱歉，您已经领取过了!', false);
        }
        //进行领取
        $showData = [
            'is_winning' => 0,
            'type' => 0,
            'image' => '',
            'title' => '',
            'money' => 0,
        ];
        if (array_get($collectReward, 'prize_id')) {
            $showData['is_winning'] = 1;
            //是红包
            if ($collectReward['type'] == 1 || $collectReward['type'] == 2) {
                if (!$collectReward['redpack_id']) {
                    $money = $collectReward->hasOnePrize->money;
                } else {
                    $money = CollectRedPack::where([
                        'id' => $collectReward['redpack_id'],
                        'status' => 0,
                        'isValid' => 1
                    ])->value('money');
                }
                //进行发红包
                $showData['type'] = 1;
                $showData['money'] = $money;
            } else {
                //进行发奖
                $prize = $collectReward->hasOnePrize;
                $showData['image'] = $prize['image'];
                $showData['title'] = $prize['title'];
            }
        }
        $collectReward->is_get = 1;
        $collectReward->get_at = date('Y-m-d H:i:s');
        $collectReward->save();
        return $this->success($showData, true);
    }

    public function initRedPage(Request $request, WebOAuthService $webOAuthService, PayHelper $payHelper)
    {
        $fansInfo = $webOAuthService->checkOauth();
        $user = $this->checkUser($request, $fansInfo, $this->basisWhere);
        $collectReward = CollectReward::getReward($user['id'], $this->basisWhere)->first();

        if (empty($collectReward) || $collectReward['type'] == 0 || $collectReward['is_get'] != 1)
            return $this->message('非法访问!', false);
        if ($collectReward['status'] == 1) return $this->message('抱歉，您已经领取过奖励了!', false);

        $reader = Reader::checkBind($fansInfo['openid'], $this->token)->first(['rdid']);
        if (empty($reader)) {
            $bindUrl = str_replace('{token}', $this->token, config('vueRoute.bindReader'));
            return $this->success(['url' => $bindUrl, 'message' => '绑定读者证才可领取奖励,正在为你跳转!'], true);
        }

        $money = 0;
        if ($collectReward['type'] == 1) {
            $prize = $collectReward->hasOnePrize;
            if ($prize && $prize['type'] == 1 && $prize['money'] > 0) {
                $money = $prize['money'];
            }
        }
        if ($collectReward['type'] == 2 && $collectReward['redpack_id']) {
            $redpackMoney = CollectRedPack::where([
                'id' => $collectReward['redpack_id'],
                'isValid' => 1,
                'status' => 0
            ])->value('money');
            if ($redpackMoney) {
                $money = $redpackMoney;
            }
        }

        if ($money > 0) {
            $code = (string)Str::uuid();
            Cache::put('checkOpenid:' . $code, [
                'openid' => $fansInfo['openid'],
                'redirect' => route('CollectCard::index', $this->basisWhere),
                'id' => $collectReward['id'],
                'table' => 'w_collect_card_u_reward',
                'pageTable' => 'w_collect_card_redpack',
                'token' => $this->token
            ], 10);
            $redpackData = [
                'mch_billno' => $payHelper->redPageNember(),
                'send_name' => Wxuser::getCache($this->token)->wxname,
                're_openid' => $fansInfo['openid'],
                'total_num' => 1,  //固定为1，可不传
                'total_amount' => $money * 100,  //单位为分，不小于100
                'wishing' => '生活所感去读书,读书所感去生活',
                'client_ip' => '',  //可不传，不传则由 SDK 取当前客户端 IP
                'act_name' => str_limit($this->config['title'], 20, ''),
                'remark' => '集卡活动获得奖励',
            ];
            Cache::put('redPage:' . $this->token . ':' . $fansInfo['openid'], $redpackData, 20);

            $url = route('RedPage::unifiedGet', [
                'wmCode' => $code,
                'oldOpenid' => $fansInfo['openid'],
                'token' => '542ef3edc367',
            ]);
            return $this->success(['url' => $url, 'message' => '等待跳转领取红包'], true);
        }
        return $this->message('抱歉，您已经领取过奖励了!', false);
    }

    //***********************  public  ***********************//
    public function checkTaskValid($user, $collectTask)
    {
        $origin_type = $collectTask['origin_type'];
        $origin_id = $collectTask['origin_id'];
        if ($origin_type != 1) {
            $origin_id = $collectTask['id'];
        }
        $is_valid = 1;
        $myTaskLog = CollectLog::getTaskLog($this->basisWhere, $user['id'], $origin_type, $origin_id)
            ->where('giver', 0)->count();
        if ($collectTask['max_n'] > 0 && $myTaskLog >= $collectTask['max_n']) {
            $is_valid = 0;
        }
        if ($collectTask['type'] == 1 && $collectTask['day_n'] > 0 && $is_valid == 1) {
            $myDayCount = CollectLog::getTaskLog($this->basisWhere, $user['id'], $origin_type, $origin_id)
                ->where('giver', 0)->whereDay('created_at', date('d'))->count();
            if ($myDayCount >= $collectTask['day_n']) {
                $is_valid = 0;
            }
        }
        return ['is_valid' => $is_valid, 'count' => $myTaskLog];
    }


    public function initTaskSetting($collectTask)
    {
        return [
            ['id' => 1, 'title' => '可抽', 'weight' => $collectTask['weight']],
            ['id' => -1, 'title' => '不可抽', 'weight' => round(100 - $collectTask['weight'], 2)],
        ];
    }

    /* 发送邀请达标通知 */
    public function sendSubTip($parent_id)
    {
        $sub_data = CollectTask::FindOrigin($this->basisWhere, 1, 10)->value('sub_data');
        $openid = CollectUsers::where($this->basisWhere)->where('id', $parent_id)->value('openid');
        $sub_data = array_filter($sub_data);
        if ($sub_data && $openid) {
            $sub_count = CollectUsers::where($this->basisWhere)->where('parent_id', $parent_id)->count();
            $sub_count++;
            $sub_task_ok = 0;
            $ok_number = 0;
            foreach ($sub_data as $k => $v) {
                if ($v == $sub_count) {
                    $sub_task_ok = 1;
                    $ok_number = $sub_count;
                }
            }
            if ($sub_task_ok) {  //发送通知
                $app = Wechatapp::initialize($this->token);
                $url = route('CollectCard::share', $this->basisWhere);
                $text = "哇~好厉害在你的盛情邀请下，已有 {$ok_number} 位小伙伴参与“{$this->config['title']}” \n\n" .
                    "<a href='" . $url . "'>戳一下这里可以抽卡哦~</a>";
                $message = new Text($text);
                $app->customer_service->message($message)->to($openid)->send();
            }
        }
    }
}
