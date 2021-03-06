<?php

namespace App\Http\Controllers\Web\LuckyDraw;

use App\Api\Helpers\ApiResponse;
use App\Models\LuckyDraw\LuckyDraw03;
use App\Models\LuckyDraw\LuckyDraw03Gather;
use App\Models\LuckyDraw\LuckyDraw03List;
use App\Models\LuckyDraw\LuckyDrawAddress;
use App\Models\LuckyDraw\LuckyPrize;
use App\Models\Wechat\Reader;
use App\Models\Wxuser;
use App\Services\IntegralService;
use App\Services\LotteryService;
use App\Services\WechatOAuth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Web03Controller extends BaseController
{
    use ApiResponse;

    //现金红包奖励队列                未完成
    public function __construct()
    {
        $this->middleware('RequiredToken')->only(['index', 'myRecord', 'saveGather', 'toDraw']);
    }

    public function index(Request $request, IntegralService $integralService)
    {
        $token = $request->input('token');
        $id = $request->input('l_id');
        $configure = LuckyDraw03::current($token, $id)->first();
        if ($configure == null) {
            abort(404);
        }
        $WechatOAuth = WechatOAuth::make($token);
        $fansInfo = $WechatOAuth->webOAuth($request);
        $qrCode = Wxuser::getCache($token)->qr_code;

        $config = $configure->only(['gather', 'is_bind', 'is_subscribe', 'status', 'start_at', 'end_at', 'sub_tip', 'id', 'integral']);

        if ($config['is_subscribe'] == 1) {
            $fansInfo['subscribe'] = $WechatOAuth->checkSub($fansInfo['openid']);
        }

        $reader = [];
        if ($config['is_bind'] == 1) {
            $reader = Reader::checkBind($fansInfo['openid'], $token)->first(['name', 'rdid']);
            //判断积分是否足够
            if ($config['integral'] > 0 && !empty($reader)) {
                $reader->integral = $integralService->getIntegral($token, $reader['rdid']);
            }
        }
        //收集信息  是否填写过信息！
        $gatherId = '';
        if (array_get($config, 'gather.0')) {
            $where = [
                'openid' => $fansInfo['openid'],
                'token' => $token,
                'l_id' => $id
            ];
            $gather = LuckyDraw03Gather::where($where)->first(['id', 'phone', 'idcard', 'name']);
            $gatherId = array_get($gather, 'id', '');
        }
        //组装个人的抽奖记录
        $eggs = $this->showEgg($configure, $fansInfo, $reader);

        $currentStatus = $this->currentStatus($configure, $reader, $fansInfo, 0);
        $allowNumber = $currentStatus['allowNumber'];

        return view('web.luckyDraw.type03', compact('configure', 'config', 'qrCode', 'fansInfo', 'reader', 'gatherId',
            'allowNumber', 'eggs'));
    }


    public function saveGather(Request $request)
    {
        $fansInfo = WechatOAuth::make($request->input('token'))->webOAuth($request);
//        $fansInfo = ['openid' => 'ofgxfuNP2fguUNsaeNdrbCKJvMBE', 'subscribe' => 1];
        $data = $request->input();
        $data['openid'] = $fansInfo['openid'];

        $where = array_only($data, ['openid', 'token', 'l_id']);
        $exists = LuckyDraw03Gather::where($where)->exists();

        if ($exists) {
            return $this->success(['message' => '你已经提交过信息了，不能重复提交'], false);
        }
        $status = LuckyDraw03Gather::create($data);
        return $this->success(['id' => $status['id'], 'message' => '提交成功'], true);
    }

    public function myRecord(Request $request)
    {
        $token = $request->input('token');
        $id = $request->input('l_id');

        $fansInfo = WechatOAuth::make($request->input('token'))->webOAuth($request);
        $wxuser = Wxuser::getCache($token);
        if ($wxuser['status'] !== 1) {
            abort('400', '公众号已暂时服务');
        }
        $configure = LuckyDraw03::current($token, $id)->first();
        $where = ['token' => $token, 'l_id' => $id, 'is_winning' => 1, 'openid' => $fansInfo['openid']];
        $myList = LuckyDraw03List::with('prize')->with('address')->where($where)->get(['id', 'prize_id', 'code', 'status', 'created_at']);
        foreach ($myList as $k => $v) {
            $myList[$k]['image'] = $this->getTypeImage($v->prize);
            $myList[$k]['qrCodeText'] = json_encode(['token' => $token, 'l_id' => $id, 'code' => $v['code']]);
        }
        unset($k, $v);
        return view('web.luckyDraw.myRecord03', compact('configure', 'myList', 'id'));
    }

    /**
     * time  2019.11.25.
     *
     * @content  添加地址信息
     *
     * @author  wsp
     */
    public function addAddress(Request $request)
    {
        $data = $request->input();
        // 缺少相关参数则直接回报404;  l_d参数为返回奖品记录页面时需要
        if (empty($data['l_id']) || empty($data['token']) || empty($data['id'])) {
            abort(404);
        }
        if (!empty($data['name'])) {
            $create['token'] = $data['token'];
            $create['name'] = $data['name'];
            $create['phone'] = $data['phone'];
            $create['address'] = $data['address'];
            $create['p_id'] = $data['p_id'];
            $create['draw_type'] = 3;  // 根据draw_type字段区分抽奖类型对应的地址信息,2代表该地址信息为老虎机抽奖
            LuckyDrawAddress::create($create);
            return $this->success(['message' => '提交成功'], true);
        }
        return view('web.luckyDraw.addAddress03', compact('data'));
    }

    public function toDraw(Request $request, IntegralService $integralService)
    {
        $token = $request->input('token');
        $id = $request->input('l_id');
        $gatherId = $request->input('gatherId');
        $rank = $request->input('rank');
        $fansInfo = WechatOAuth::make($token)->webOAuth($request);
//        $fansInfo = ['openid' => 'ofgxfuNP2fguUNsaeNdrbCKJvMBE', 'subscribe' => 1];

        $configure = LuckyDraw03::current($token, $id)->first();
        if ($configure['status'] != 1) {
            return $this->success(['message' => '抱歉,活动已经关闭了！'], false);
        }
        $time = date('Y-m-d H:i:s');
        if ($time < $configure['start_at'] || $time >= $configure['end_at']) {
            return $this->success(['message' => '抱歉,请在活动允许的时间进行抽奖！'], false);
        }

        $reader = [];
        if ($configure['is_bind'] == 1) {
            $reader = Reader::checkBind($fansInfo['openid'], $token)->first(['name', 'rdid']);
            if (empty($reader)) {
                return $this->success(['message' => '抱歉,尚未绑定读者证无法参与活动！'], false);
            }

            if ($configure['integral'] > 0) {
                if ($configure['integral'] > $integralService->getIntegral($token, $reader['rdid'])) {
                    return $this->success(['message' => '抱歉,您的积分不足' . $configure['integral'] . ',无法参与抽奖！'], false);
                }
                $integralService->decrement($token, $reader['rdid'], $configure['integral'], '抽奖消耗积分');
            }
        }
        //判断检查是否允许绑定接口  start
        $checkApiRequire = $this->checkApiRequire($configure, $fansInfo, $reader, $gatherId);
        if (!empty($checkApiRequire)) {
            return $this->success($checkApiRequire, 'Wait');
        }
        $currentStatus = $this->currentStatus($configure, $reader, $fansInfo);
        $allowNumber = $currentStatus['allowNumber'];
        $luckyPrize = [];
        if ($allowNumber > 0) {
            //组装抽奖数据
            $settings = $this->initSetting($configure, $currentStatus['noWinning']);
            //进行抽奖
            $result = LotteryService::make($name = "instance", $config = array("debug" => false))->go($settings);
            //增加个人抽奖记录
            $is_winning = ($result['id'] === 0) ? 0 : 1;
            $code = ($is_winning) ? Str::uuid()->getNodeHex() : '';
            $status = 0;
            if ($is_winning) {
                //减少奖品库存
                $luckyPrize = LuckyPrize::where('id', $result['id'])->first(['id', 'type', 'integral', 'money', 'image', 'title']);
                $luckyPrize->decrement('inventory');
                //积分类奖品增加积分
                if ($luckyPrize['type'] == 1 && array_get($reader, 'rdid')) {
                    $status = 1;
                    $integralService->increment($token, $reader['rdid'], $luckyPrize['integral'], '砸金蛋抽奖奖励');
                }
                //现金红包类奖品队列发红包
            }
            //组装text中奖说明  未做
            $addLog = [
                'rdid' => array_get($reader, 'rdid'),
                'text' => $result['title'],
                'openid' => $fansInfo['openid'],
                'is_winning' => $is_winning,
                'code' => $code,
                'status' => $status,
                'token' => $token,
                'gather_id' => $gatherId,
                'prize_id' => $result['id'],
                'l_id' => $id,
                'rank' => $rank
            ];
            LuckyDraw03List::create($addLog);

            //增加被抽奖次数
            $configure->increment('count');
            $response = [
                'message' => '抽奖完成!',
                'id' => $result['id']
            ];
            if ($result['id'] !== 0) {
                $response['image'] = $this->getTypeImage($luckyPrize);
                $response['title'] = $luckyPrize['title'];
            }

            return $this->success($response, true);

        }
        return $this->success(['message' => '抱歉,您目前没有抽奖机会！'], false);

    }


    //************ 公共方法 *************//
    protected function checkApiRequire($configure, $fansInfo, $reader, $gatherId)
    {
        $url = $configure['check_url'];
        if (empty($url)) return [];
        $params = [
            'openid' => $fansInfo['openid'],
            'glc' => Wxuser::getCache($configure['token'])['glc']
        ];
        if ($reader) {
            $params['rdid'] = $reader['rdid'];
            $params['name'] = $reader['name'];
        }
        if ($gatherId) {
            $gather = LuckyDraw03Gather::find($gatherId);
            $params['gather_name'] = $gather['name'];
            $params['gather_phone'] = $gather['phone'];
            $params['gather_idcard'] = $gather['idcard'];
        }
        $http = new Client();
        $url .= (Str::contains($url, '?')) ? '&' : '?';
        $url .= http_build_query($params);
        $response = $http->get($url);
        $response = json_decode((string)$response->getBody(), true);
        if ($response['status'] == true) {
            return [];
        }
        return $response['data'];
    }

    protected function initSetting($configure, $noWinning)
    {
        $prize = [];
        if ($noWinning != 1) {
            $prize = $configure->hasManyPrize()
                ->where('inventory', '>', 0)
                ->get(['weight', 'title', 'id'])
                ->toArray();
        }
        array_push($prize, [
            'id' => 0,
            'title' => '抱歉,没有抽中奖品!',
            'weight' => $configure['no_weight']
        ]);
        return $prize;
    }

    protected function currentStatus($configure, $reader, $fansInfo, $showWinning = 1)
    {
        $allowNumber = 0;
        $noWinning = 0;
        $token = $configure['token'];
        $id = $configure['id'];
        if ($configure['status'] == 1) {
            //当前抽奖次数
            $allowQuery = LuckyDraw03List::where(['token' => $token, 'l_id' => $id]);

            $allowQuery->when($configure['is_bind'] == 1, function ($q) use ($reader) {
                return $q->where('rdid', $reader['rdid']);
            }, function ($q) use ($fansInfo) {
                return $q->where('openid', $fansInfo['openid']);
            });
            //个人抽取的全部次数
            $allNumber = $allowQuery->count();

            if ($configure['type'] == 1) {
                //按天数的话需要对比总数
                if ($allNumber < $configure['all_number']) {
                    $todayNumber = $allowQuery->whereDate('created_at', '>=', date('Y-m-d'))->count();
                    $allowNumber = $configure['number'] - $todayNumber;
                }

            } else {
                $allowNumber = ($allNumber >= $configure['number']) ? 0 : $configure['number'] - $allNumber;
            }

            //个人中奖的次数
            if ($configure['all_winning'] > 0 && $showWinning == 1) {
                $winningQuery = LuckyDraw03List::where(['token' => $token, 'l_id' => $id, 'is_winning' => 1]);
                $winningQuery->when($configure['is_bind'] == 1, function ($q) use ($reader) {
                    return $q->where('rdid', $reader['rdid']);
                }, function ($q) use ($fansInfo) {
                    return $q->where('openid', $fansInfo['openid']);
                });
                $allWinning = $winningQuery->count();
                $noWinning = ($allWinning >= $configure['all_winning']) ? 1 : 0;
            }

        }
        return ['allowNumber' => $allowNumber, 'noWinning' => $noWinning];
    }

    protected function showEgg($configure, $fansInfo, $reader)
    {
        $where = ['token' => $configure['token'], 'l_id' => $configure['id']];
        //总共九个蛋
        $range = range(1, 9);
        if ($configure['is_bind'] == 1 && empty($reader)) {
            return $range;
        }
        $listQuery = LuckyDraw03List::with('prize')->where($where);
        $listQuery->when($configure['is_bind'] == 1, function ($q) use ($reader) {
            return $q->where('rdid', $reader['rdid']);
        }, function ($q) use ($fansInfo) {
            return $q->where('openid', $fansInfo['openid']);
        });
        $listQuery->when($configure['type'] == 1, function ($q) {
            return $q->whereDate('created_at', '>=', date('Y-m-d'));
        });
        $list = $listQuery->get(['prize_id', 'id', 'rank', 'is_winning']);
        // 需要在查找出来的奖品信息不为空的情况下遍历
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $range[$v['rank']] = [
                    'prize_id' => $v['prize_id'],
                    'id' => $v['id'],
                    'rank' => $v['rank'],
                    'is_winning' => $v['is_winning'],
                ];

                $image = '';
                if ($v['is_winning'] == 1) {
                    $image = $this->getTypeImage($v->prize);
                }
                $range[$v['rank']]['image'] = $image;
            }
            unset($k, $v);
        }
        return $range;
    }


}
