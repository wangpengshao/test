<?php

namespace App\Http\Controllers\Web\IntegralAward;

use App\Api\Helpers\ApiResponse;
use App\Models\IntegralExchange\IntegralExchangePrize;
use App\Models\IntegralExchange\IntegralExchangePrizeList;
use App\Models\IntegralExchange\IntegralHonoreeGather;
use App\Models\Wechat\Reader;
use App\Models\Wxuser;
use App\Http\Controllers\Web\LuckyDraw\BaseController;
use App\Services\IntegralService;
use App\Services\WechatOAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IntegralExchangeController extends BaseController
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware('RequiredToken')->only(['index', 'awardRecord', 'saveGather', 'matchPrize']);
    }

    /**
     * @time   2019/10/18
     * 接收token显示兑奖页面
     * @author wsp@xxx.com wsp
     */
    public function index(Request $request)
    {
        $token = $request->input('token');
        // 兑奖奖品的状态为开启的
        $status = 1;
        $configure = IntegralExchangePrize::where(['token' => $token, 'status' => $status])->get();
        if ($configure == null) {
            abort(404);
        }
        $WechatOAuth = WechatOAuth::make($token);
        $fansInfo = $WechatOAuth->webOAuth($request);
        // 判断用户是否已经绑定，若未绑定，则提示并禁止进入兑奖页面
        $reader = Reader::checkBind($fansInfo['openid'], $token)->first(['name', 'rdid']);
        if (empty($reader)) {
            return view('web.IntegralExchange.error');
        }
        // 判断在线上条件下，兑奖人信息奖表中是否存在相应兑奖人信息，若缺少，则标记
        $gatherId = IntegralHonoreeGather::fansFind($token, $fansInfo['openid'])->value('id');
        return view('web.IntegralExchange.index', compact('configure', 'token', 'gatherId'));
    }

    /**
     * @time   2019/10/18
     * 显示兑奖记录
     * @author wsp@xxx.com wsp
     */
    public function awardRecord(Request $request)
    {
        $token = $request->input('token');

        $fansInfo = WechatOAuth::make($request->input('token'))->webOAuth($request);
        $wxuser = Wxuser::getCache($token);
        if ($wxuser['status'] !== 1) {
            abort('400', '公众号已暂停服务');
        }
        $configure = IntegralExchangePrize::where(['token' => $token, 'reward_way' => 1])->first();
        $where = ['token' => $token, 'openid' => $fansInfo['openid']];
        $myList = IntegralExchangePrizeList::with('prize')->where($where)->get(['id', 'prize_id', 'code', 'status', 'created_at']);
        // 需要在查找出来的奖品信息不为空的情况下遍历
        if (!empty($myList)) {
            foreach ($myList as $k => $v) {
                $myList[$k]['image'] = $this->getTypeImage($v->prize);
                $myList[$k]['qrCodeText'] = json_encode(['token' => $token, 'code' => $v['code']]);
            }
        }
        unset($k, $v);
        return view('web.IntegralExchange.awardRecord', compact('configure', 'myList', 'token'));
    }

    /**
     * @time   2019/10/22
     * 保存兑奖人信息
     * @author wsp@xxx.com wsp
     */
    public function saveGather(Request $request)
    {
        $fansInfo = WechatOAuth::make($request->input('token'))->webOAuth($request);
        $data = $request->input();
        $data['openid'] = $fansInfo['openid'];
        $where = array_only($data, ['openid', 'token']);
        $exists = IntegralHonoreeGather::where($where)->exists();
        if ($exists) {
            return $this->success(['message' => '你已经提交过信息了，不能重复提交'], false);
        }
        $status = IntegralHonoreeGather::create($data);
        return $this->success(['id' => $status['id'], 'message' => '提交成功'], true);
    }

    /**
     * @time   2019/10/18
     * 进行奖品积分兑换
     * @author wsp@xxx.com wsp
     */
    public function matchPrize(Request $request, IntegralService $integralService)
    {
        $token = $request->input('token');
        $id = $request->input('id');
        $fansInfo = WechatOAuth::make($token)->webOAuth($request);
        $configure = IntegralExchangePrize::current($token, $id)->first();
        $time = date('Y-m-d H:i:s');
        if ($time < $configure['start_at']) {
            return $this->success(['message' => '抱歉,请在允许的时间进行兑奖！'], false);
        }
        // 判断奖品库存是否大于0
        if ($configure['inventory'] == 0) {
            return $this->success(['message' => '抱歉,该奖品库存不足！'], false);
        }
        // 获取读者信息
        $reader = Reader::checkBind($fansInfo['openid'], $token)->first(['name', 'rdid']);
        // 判断读者积分是否足够
        if ($configure['integral'] > 0) {
            if ($configure['integral'] > $integralService->getIntegral($token, $reader['rdid'])) {
                return $this->success(['message' => '抱歉,您的积分不足' . $configure['integral'] . ',无法参与兑奖！'], false);
            }
            $integralService->decrement($token, $reader['rdid'], $configure['integral'], '兑奖消耗积分');
        }
        $allowNumber = $this->currentStatus($configure, $reader);
        if ($allowNumber > 0) {
            //减少奖品库存
            $IntegralExchange = IntegralExchangePrize::where('id', $id)->first(['id', 'title', 'integral', 'money']);
            $IntegralExchange->decrement('inventory');
            // 获取奖品说明
            $code = Str::uuid()->getNodeHex();
            // 组装text中奖说明
            $addLog = [
                'rdid' => array_get($reader, 'rdid'),
                'text' => $IntegralExchange['title'],
                'openid' => $fansInfo['openid'],
                'code' => $code,
                'token' => $token,
                'prize_id' => $id
            ];
            IntegralExchangePrizeList::create($addLog);
            return $this->success(['message' => '兑奖完成!'], true);
        }
        return $this->success(['message' => '抱歉,您目前没有兑奖机会！'], false);
    }

    /**
     * @time   2019/10/18
     * 当前个人可兑奖次数
     * @author wsp@xxx.com wsp
     */
    protected function currentStatus($configure, $reader)
    {
        $allowNumber = 0;
        $token = $configure['token'];
        $id = $configure['id'];
        if ($configure['status'] == 1) {
            // 当前兑奖次数
            $allowQuery = IntegralExchangePrizeList::where(['token' => $token, 'prize_id' => $id]);
            $allowQuery->where('rdid', $reader['rdid']);
            // 个人兑奖的全部次数
            $allNumber = $allowQuery->count();
            // 若已兑奖次数不小于可兑奖总次数
            if ($allNumber >= $configure['all_number']) {
                $allowNumber = 0;
                return $allowNumber;
            }
            // 获取当前周几的值
            $currentWeek = date('w');
            // 计算当前周所对应的日期
            $currentWeekFirstDay = '';
            $currentWeekLastDay = '';
            if ($currentWeek == 0) {
                $currentWeekFirstDay = date('Y-m-d', strtotime('-6 day'));
                $currentWeekLastDay = date('Y-m-d');
            } else if ($currentWeek == 1) {
                $currentWeekFirstDay = date('Y-m-d');
                $currentWeekLastDay = date('Y-m-d', strtotime('+6 day'));
            } else if ($currentWeek == 2) {
                $currentWeekFirstDay = date('Y-m-d', strtotime('-1 day'));
                $currentWeekLastDay = date('Y-m-d', strtotime('+5 day'));
            } else if ($currentWeek == 3) {
                $currentWeekFirstDay = date('Y-m-d', strtotime('-2 day'));
                $currentWeekLastDay = date('Y-m-d', strtotime('+4 day'));
            } else if ($currentWeek == 4) {
                $currentWeekFirstDay = date('Y-m-d', strtotime('-3 day'));
                $currentWeekLastDay = date('Y-m-d', strtotime('+3 day'));
            } else if ($currentWeek == 5) {
                $currentWeekFirstDay = date('Y-m-d', strtotime('-4 day'));
                $currentWeekLastDay = date('Y-m-d', strtotime('+2 day'));
            } else if ($currentWeek == 6) {
                $currentWeekFirstDay = date('Y-m-d', strtotime('-5 day'));
                $currentWeekLastDay = date('Y-m-d', strtotime('+1 day'));
            }
            // 根据月，周，日类型进行判断个人剩余可兑奖次数
            switch ($configure['type']) {
                case 1:
                    // 按月数来计算当前月兑奖数
                    // 获取当前年月的值
                    $currentYear = date('Y');
                    $currentMonth = date('m');
                    $monthNumber = $allowQuery->whereYear('created_at', '=', $currentYear)
                        ->whereMonth('created_at', '=', $currentMonth)->count();
                    $allowNumber = $configure['number'] - $monthNumber;
                    break;
                case 2:
                    // 按周数来计算当前周兑奖数
                    $weekNumber = $allowQuery->whereDate('created_at', '>=', $currentWeekFirstDay)
                        ->whereDate('created_at', '<=', $currentWeekLastDay)->count();
                    $allowNumber = $configure['number'] - $weekNumber;
                    break;
                case 3:
                    // 按天数的话需要对比总数
                    $todayNumber = $allowQuery->whereDate('created_at', '>=', date('Y-m-d'))->count();
                    $allowNumber = $configure['number'] - $todayNumber;
                    break;
                // 默认不受月，周，日限制
                default:
                    $allowNumber = ($allNumber >= $configure['number']) ? 0 : $configure['number'] - $allNumber;
            }
        }
        return $allowNumber;
    }

}

