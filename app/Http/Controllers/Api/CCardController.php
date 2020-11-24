<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\CollectCard\CollectCard;
use App\Models\CollectCard\CollectTask;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;


/**
 * Class CCardController  集卡Api
 * @package App\Http\Controllers\Api
 */
class CCardController extends Controller
{
    use  ApiResponse;

//    protected $origin_type = [
//        '-4' => '每日登录送卡',
////        '-3' => '别人赠送',
////        '-2' => '首次参与',
//        '-1' => '邀请关注奖励',
//        '1' => '云书馆-你选我买',
//        '2' => '云书馆-办证',
//        '3' => '云书馆-查看电子资源',
//        '4' => '借还机-办证',
//        '5' => '借还机-借书',
//        '6' => '借还机-还书',
//        '7' => '借还机-交逾期费',
//        '8' => 'Interlib-网上办证',
//        '9' => 'Interlib-续借',
//        '10' => 'Interlib-预约图书',
//        '11' => 'Interlib-查看电子书',
//        '12' => '活动平台-活动报名',
//        '13' => '活动平台-活动签到',
//        '14' => 'SSO-查看数字资源',
//    ];

    /**
     * @param Request $request
     * @return mixed
     */
    public function getSerial(Request $request)
    {
        /************************** 条件判断  start **************************/
        if (!$request->filled(['time', 'sign', 'rdid', 'origin_id', 'a_id'])) {
            return $this->message('lack of parameter', false);
        }
        [
            'time' => $time,
            'sign' => $sign,
            'rdid' => $rdid,
            'origin_id' => $origin_id,
            'a_id' => $a_id,
            'token' => $token
        ] = $request->input();
//        验签
        if ($sign != md5($rdid . $time . config('envCommon.MENU_ENCRYPT_STR'))) {
            return $this->message('sign is invalid', false);
        }
        $s = time() - $time;
        if ($s > 300 || $s < -300) {
            return $this->message('link is invalid', false);
        }
        //判断来源是否有效
        $collectCard = CollectCard::getCache($token, $a_id);
        if (empty($collectCard)) {
            return $this->message('activity does not exist', false);
        }
        //判断活动的状态是否正常
        $date = date('Y-m-d H:i:s');
        if ($collectCard['start_at'] > $date || $collectCard['end_at'] < $date || $collectCard['status'] != 1) {
            return $this->message('activity is invalid', false);
        }
        //查看任务id是否正常
        $collectTask = CollectTask::where('token', $token)->where('id', $origin_id)->first();
        $origin_type = 2;       //默认第三方系统的type
        if ($collectTask['status'] != 1 || $collectTask['origin_type'] != $origin_type) {
            return $this->message('task is invalid', false);
        }
        $uuid = (string)Str::uuid();
        $uuidCache = [
            'rdid' => $rdid,
            'origin_id' => $origin_id,
            'a_id' => $a_id,
            'token' => $token
        ];
        $uuidCacheKey = 'CCard' . ':' . $uuid;
        Cache::put($uuidCacheKey, $uuidCache, 30);
        $url = route('CollectCard::checkSerial', [
            'a_id' => $a_id,
            'token' => $token,
            'serial' => $uuid,
            'sign' => md5(config('envCommon.MENU_ENCRYPT_STR') . $uuid)
        ]);
        //更新 短链接功能全部由图书馆微消息中心服务号代转  注:接口调用次数上限
//        $app = Wechatapp::initialize($token);
        $app = Wechatapp::initialize('542ef3edc367');
        $shortUrl = $app->url->shorten($url);
        $response = [
            'url' => $url,
            'shortUrl' => array_get($shortUrl, 'short_url')
        ];
        return $this->success($response, true);
        //是服务号还是订阅号，生成可关注的二维码。
        /************************** 条件判断  end **************************/
    }

}
