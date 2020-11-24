<?php

namespace App\Http\Controllers\Web\Deposit\Wap;

use App\Models\Deposit\Deposit;
use App\Models\Deposit\DepositLog;
use App\Models\Deposit\DepositEveryday;
use App\Models\Deposit\DepositUser;
use App\Models\Wxuser;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use GuzzleHttp\Client;

class DepositOrderRefundController extends Controller
{
    public $token;
    public $Wxuser;
    public $User;
    public $openid;
    public $siteUrl;
    public $rdid;
    public function __construct(Request $request)
    {
        $this->token = $request->route('token');
    }
    //预约首页
    public function index(Request $request)
    {
        // 预约可选天数
        $days = 15;
        $deposit = Deposit::where('token',$this->token)->first();
        if(!$deposit['status']){
            exit;
        }
        // 可预约最早时间
        $a = strtotime(date('Y-m-d', strtotime('+' . $deposit['before_time'] . 'day')));
        $initDate = date('Y-m-d', $a);
        // 可预约最晚时间
        $maxDate = date('Y-m-d', strtotime($initDate) + $days * 86400);
        // 节假日
        $holiday = explode(',', $deposit['holiday']);
        // 时间段计算 start，获取分断时间
        $blocktimes = 60 * $deposit['block'];
        $changeData = [];
        for ($i = 0; $i < $days; $i++) {
            $weekIndex = date('w', $a);
            $weekData = explode('-', $deposit['week' . $weekIndex]);
            if ($weekData[0] == 'false') {
                //若设置头为false，则说明是当天是闭馆的
                $changeData[date('Y-m-d', $a)] = 'false';
            } else {
                if (in_array(date('m-d', $a), $holiday)) {
                    $changeData[date('Y-m-d', $a)] = 'false';
                } else {
                    $block = [];
                    // 获取上午的时间段
                    $blocks = floor((strtotime('2018-12-21' . $weekData[2]) - strtotime('2018-12-21' . $weekData[1])) / $blocktimes);
                    for ($j = 0; $j < $blocks; $j++) {
                        // 将上午时间段分割
                        $block[] = date('H:i:s', strtotime('2018-12-21' . $weekData[1]) + $j * $blocktimes);
                    }
                    // 获取下午的时间段
                    $blocks = floor((strtotime('2018-12-21' . $weekData[4]) - strtotime('2018-12-21' . $weekData[3])) / $blocktimes);
                    for ($j = 0; $j < $blocks; $j++) {
                        $block[] = date('H:i:s', strtotime('2018-12-21' . $weekData[3]) + $j * $blocktimes);
                    }
                    // 当天的上下午所有时间段
                    $changeData[date('Y-m-d', $a)] = $block;
                }
            }
            $a += 86400;
        }
        // 时间段计算 end
        $res = array(
            'token'   => $this->token,
            'firstDate'=>$initDate,
            'data'=>$deposit,
            'jsonData'=>$deposit,
            'changeData'=>$changeData
        );
        return view('web.deposit.wap.orderRefund', $res);
    }
    public function depositLog(Request $request)
    {
        $token = $request->route('token');
        $rdid = $request->route('rdid');
        $data = DepositLog::where(['rdid' => $rdid, 'token'=>$token])->orderBy('create_time','desc')->get()->toArray();
        // 将创建时间的时间戳格式化
        foreach ($data as $key => $value) {
            $data[$key]['create_time'] = date('Y-m-n H:i:s',$value['create_time']);
        }
        $result = array(
            'token'=> $token,
            'data' => $data
        );
        return view('web.deposit.wap.depositLog', $result);
    }
}
