<?php

namespace App\Http\Controllers\Web\LuckyDraw;

use App\Api\Helpers\ApiResponse;
use App\Models\LuckyDraw\LuckyDraw01;
use App\Models\LuckyDraw\LuckyDraw01Gather;
use App\Models\LuckyDraw\LuckyDraw01List;
use App\Models\LuckyDraw\LuckyDrawAddress;
use App\Models\LuckyDraw\LuckyPrize;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use App\Services\IntegralService;
use App\Services\LotteryService;
use App\Services\WechatOAuth;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Web01Controller extends BaseController
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('RequiredToken')->only(['index', 'myRecord', 'saveGather', 'toDraw']);
    }

    public function index(Request $request, IntegralService $integralService)
    {
        $token = $request->input('token');
        $id = $request->input('l_id');
        $configure = LuckyDraw01::current($token, $id)->first();
        if ($configure == null) {
            abort(404);
        }
        $WechatOAuth = WechatOAuth::make($token);
        $fansInfo = $WechatOAuth->webOAuth($request);
        $qrCode = Wxuser::getCache($token)->qr_code;
        $bindUrl = config('vueRoute.bindReader');
        $bindUrl = str_replace('{token}', $token, $bindUrl);

        $config = $configure->only([
            'gather', 'is_bind', 'is_subscribe', 'status', 'start_at', 'end_at', 'sub_tip', 'id', 'integral', 'tip',
            'share_img', 'share_title', 'share_desc'
        ]);
        if ($config['is_subscribe'] == 1) {
            $fansInfo['subscribe'] = $WechatOAuth->checkSub($fansInfo['openid']);
        }
        $reader = [];
        if ($config['is_bind'] == 1) {
            $reader = Reader::checkBind($fansInfo['openid'], $token)->first(['name', 'rdid']);
            //判断积分是否足够
            if ($config['integral'] > 0 && $reader) {
                $reader->integral = $integralService->getIntegral($token, $reader['rdid']);
            }
        }
        //收集信息  是否填写过信息！
        $gatherId = '';
        if (Arr::get($config, 'gather.0')) {
            $gatherId = LuckyDraw01Gather::fansFind($token, $id, $fansInfo['openid'])->value('id');
        }
        //奖品缓存
        $prize = $this->showPrize($configure);
        //抽奖记录
        $logList = LuckyDraw01List::userWinning($token, $id)->orderBy('id', 'desc')->limit(20)->get(['text', 'openid']);
        $currentStatus = $this->currentStatus($configure, $reader, $fansInfo, 0);
        $allowNumber = $currentStatus['allowNumber'];

        $app = Wechatapp::initialize($token);
        return view('web.luckyDraw.type01', compact('configure', 'config', 'qrCode', 'fansInfo', 'reader', 'gatherId',
            'prize', 'logList', 'allowNumber', 'token', 'bindUrl', 'app'));
    }


    public function saveGather(Request $request)
    {
        $fansInfo = WechatOAuth::make($request->input('token'))->webOAuth($request);
        $data = $request->input();
        $data['openid'] = $fansInfo['openid'];
        $where = array_only($data, ['openid', 'token', 'l_id']);
        $exists = LuckyDraw01Gather::where($where)->exists();

        if ($exists) {
            return $this->success(['message' => '你已经提交过信息了，不能重复提交'], false);
        }
        $status = LuckyDraw01Gather::create($data);
        return $this->success(['id' => $status['id'], 'message' => '提交成功'], true);
    }

    public function myRecord(Request $request)
    {
        $token = $request->input('token');
        $id = $request->input('l_id');
        $fansInfo = WechatOAuth::make($request->input('token'))->webOAuth($request);
        $configure = LuckyDraw01::current($token, $id)->first();
        if ($configure == null) {
            abort(404);
        }
        // 关联prize表和地址表，根据draw_type字段区分抽奖类型对应的地址信息
        $myList = LuckyDraw01List::with('prize')->with('address')->userWinning($token, $id)
            ->where('openid', $fansInfo['openid'])
            ->get(['id', 'prize_id', 'code', 'status', 'expressNo', 'created_at']);
        foreach ($myList as $k => $v) {
            $myList[$k]['image'] = $this->getTypeImage($v->prize);
            $myList[$k]['qrCodeText'] = json_encode(['token' => $token, 'l_id' => $id, 'code' => $v['code']]);
        }
        unset($k, $v);
        return view('web.luckyDraw.myRecord01', compact('configure', 'myList', 'id'));
    }

    /**
     * time  2019.11.22.
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
            $create['draw_type'] = 1;  // 根据draw_type字段区分抽奖类型对应的地址信息,1代表该地址信息为转盘抽奖
            LuckyDrawAddress::create($create);
            return $this->success(['message' => '提交成功'], true);
        }
        return view('web.luckyDraw.addAddress01', compact('data'));
    }

    public function toDraw(Request $request, IntegralService $integralService)
    {
        $token = $request->input('token');
        $id = $request->input('l_id');
        $gatherId = $request->input('gatherId');
        $fansInfo = WechatOAuth::make($token)->webOAuth($request);
        $configure = LuckyDraw01::current($token, $id)->first();
        if ($configure['status'] != 1) {
            return $this->success(['message' => '抱歉,活动已经关闭了！'], false);
        }
        $time = date('Y-m-d H:i:s');
        if ($time < $configure['start_at'] || $time >= $configure['end_at']) {
            return $this->success(['message' => '抱歉,请在活动允许的时间进行抽奖！'], false);
        }

        $reader = Reader::checkBind($fansInfo['openid'], $token)->first(['name', 'rdid']);
        if ($configure['is_bind'] == 1) {
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
        //判断检查是否允许绑定接口  end
        $currentStatus = $this->currentStatus($configure, $reader, $fansInfo);
        $allowNumber = $currentStatus['allowNumber'];
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
                $luckyPrize = LuckyPrize::where('id', $result['id'])->first(['id', 'type', 'integral', 'money']);
                $luckyPrize->decrement('inventory');
                //积分类奖品增加积分
                if ($luckyPrize['type'] == 1 && Arr::has($reader, 'rdid')) {
                    $status = 1;
                    $integralService->increment($token, $reader['rdid'], $luckyPrize['integral'], '大转盘抽奖奖励');
                }
                //现金红包类奖品队列发红包
            }
            //组装text中奖说明  未做
            $addLog = [
                'rdid' => Arr::get($reader, 'rdid'),
                'text' => $result['title'],
                'openid' => $fansInfo['openid'],
                'nickname' => $fansInfo['nickname'],
                'is_winning' => $is_winning,
                'code' => $code,
                'status' => $status,
                'token' => $token,
                'gather_id' => $gatherId,
                'prize_id' => $result['id'],
                'l_id' => $id,
            ];
            LuckyDraw01List::create($addLog);
            //增加被抽奖次数
            $configure->increment('count');
            $response = [
                'message' => '抽奖完成!',
                'id' => $result['id']
            ];
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
            $gather = LuckyDraw01Gather::find($gatherId);
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


    protected function showPrize($configure)
    {
        $prize = $configure->hasManyPrize()->get(['id', 'title', 'type', 'image'])->toArray();
        $facePNG = asset('wechatWeb/LuckyDraw/common/image/new/face.png');
        $count = count($prize);
        $addNumber = 6 - $count;
        $angle = 60;
        if ($count >= 6) {
            $addNumber = 8 - $count;
            $angle = 45;
        }
        if ($addNumber > 0) {
            $rand = [
                [
                    'id' => 0,
                    'title' => '谢谢参与',
                    'type' => 0,
                    'image' => $facePNG
                ],
                [
                    'id' => 0,
                    'title' => '再接再厉',
                    'type' => 0,
                    'image' => $facePNG
                ],
                [
                    'id' => 0,
                    'title' => '谢谢参与',
                    'type' => 0,
                    'image' => $facePNG
                ],
                [
                    'id' => 0,
                    'title' => '谢谢参与',
                    'type' => 0,
                    'image' => $facePNG
                ]
                ,
                [
                    'id' => 0,
                    'title' => '谢谢参与',
                    'type' => 0,
                    'image' => $facePNG
                ]
            ];

            for ($a = 0; $a < $addNumber; $a++) {
                array_splice($prize, $a * 2, 0, [$rand[$a]]);
            }
        }

        foreach ($prize as $k => $v) {
            array_set($prize[$k], 'angle', $k * $angle);
            $prize[$k]['image'] = $this->getTypeImage($v);
        }
        unset($k, $v);
        return $prize;
    }

    protected function currentStatus($configure, $reader, $fansInfo, $showWinning = 1)
    {
        $allowNumber = 0;
        $noWinning = 0;
        if ($configure['status'] != 1) {  //活动未开启
            return ['allowNumber' => $allowNumber, 'noWinning' => $noWinning];
        }
        $token = $configure['token'];
        $id = $configure['id'];
        //当前抽奖次数
        $allowQuery = LuckyDraw01List::where(['token' => $token, 'l_id' => $id]);
        if ($configure['is_bind'] == 1) {
            if (!empty($reader)) {
                $allowQuery->where('rdid', $reader['rdid']);
            }
        } else {
            $allowQuery->where('openid', $fansInfo['openid']);
        }
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
            $winningQuery = LuckyDraw01List::where(['token' => $token, 'l_id' => $id, 'is_winning' => 1]);
            if ($configure['is_bind'] == 1) {
                if (!empty($reader)) {
                    $winningQuery->where('rdid', $reader['rdid']);
                }
            } else {
                $winningQuery->where('openid', $fansInfo['openid']);
            }
            $allWinning = $winningQuery->count();
            $noWinning = ($allWinning >= $configure['all_winning']) ? 1 : 0;
        }
        return ['allowNumber' => $allowNumber, 'noWinning' => $noWinning];
    }

}
