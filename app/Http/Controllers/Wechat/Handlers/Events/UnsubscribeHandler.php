<?php


namespace App\Http\Controllers\Wechat\Handlers\Events;

use App\Models\Wechat\Fans;
use App\Models\Wechat\QrCodeLog;
use App\Models\Wechat\ReaderToMany;
use App\Services\EsBuilder;
use Carbon\Carbon;
//use CrCms\ElasticSearch\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class UnsubscribeHandler
{
    public static function handle($data = null)
    {
        $token = \request()->route('token');
        $openid = $data['FromUserName'];
        //活跃粉丝列表移除此 openid
        $cacheKEY = 'fans:' . $token;
        $redis = Redis::connection();
        $redis->zrem($cacheKEY, $openid);
        //删掉粉丝信息更新控制字段
        $cacheKey = 'fans:' . $token . ':' . $openid;
        Cache::forget($cacheKey);
        //更新粉丝状态
        Fans::where(['token' => $token, 'openid' => $openid])->update(['subscribe' => 0]);

        //进行记录   取消关注
        $builderData = [
            'token' => $token,
            'openid' => $openid,
            'type' => 2,
            'event_str' => '取关',
            'created_at' => date('Y-m-d H:i:s', $data['CreateTime'])
        ];
        $yearMonth = date('Ym', $data['CreateTime']);
        EsBuilder::index('wechat_event_' . $yearMonth)->create($builderData);

        //......扫码推广事件
        $cacheKey = 'qrTask_' . $token . '_' . $openid;
        $cacheData = Cache::get($cacheKey);
        if ($cacheData) {
            $rdid = $cacheData['rdid'];
            $log_id = $cacheData['log_id'];
            $c_time = $cacheData['c_time'];
            $integral = $cacheData['integral'];

            //判断保留的天数是否有修改过
            $qrCodeLog = QrCodeLog::with(['hasOneTask' => function ($query) {
                $query->select('id', 'k_days');
            }])->where(['id' => $log_id, 'isValid' => 1])->first();

            if ($qrCodeLog && $qrCodeLog->hasOneTask) {
                //时间计算
                $k_days = $qrCodeLog->hasOneTask->k_days;
                $is_Valid = Carbon::now()->between(Carbon::parse($c_time), Carbon::parse($c_time)->addDay($k_days));
                if ($is_Valid) {
                    $qrCodeLog->isValid = 0;
                    $qrCodeLog->save();
                    ReaderToMany::where(['token' => $token, 'rdid' => $rdid])->decrement('integral', $integral);
                }
            }
        }
        return false;
    }
}
