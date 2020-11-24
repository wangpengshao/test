<?php

namespace App\Http\Controllers\Wechat\Handlers;

use App\Models\Wechat\Fans;
use App\Models\Wechat\Wechatapp;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

/**
 * Class TextHandler
 *
 * @package App\Http\Controllers\Wechat\Handlers
 */
class AllHandler implements EventHandlerInterface
{

    public function handle($data = null)
    {
        // 模版事件 => 群发模版优先返回响应
        if (Arr::get($data, 'MsgType') === 'event' && Arr::get($data, 'Event') === 'TEMPLATESENDJOBFINISH') {
            return false;
        }
        $token = request()->route('token');
        $openid = Arr::get($data, 'FromUserName');
        if ($openid) {
            $cacheKey = 'fans:' . $token . ':' . $openid;
            if (!Cache::has($cacheKey)) {
                $app = Wechatapp::initialize($token);
                $fansInfo = $app->user->get($openid);

                //access_token 兼容失效 强制重新 获取 access_token
                if (Arr::has($fansInfo, 'errcode')) {
                    $app->access_token->getToken(true);
                    $fansInfo = $app->user->get($openid);
                    //还存在异常排除access_token配置问题,有可能token配置错误,抛出错误提示
                    if (Arr::has($fansInfo, 'errcode')) {
                        return '配置错了,请重新检查微信公众号服务配置!';
                    }
                }
                //新增或更新 fans信息
                $user = Arr::only($fansInfo, config('fansParams'));
                $user['updated_at'] = date('Y-m-d H:i:s');
                $user['token'] = $token;
                Fans::updateOrCreate(['token' => $token, 'openid' => $openid], $user);
                //假设 subscribe 存在时，数据为可靠的
                if ($fansInfo['subscribe'] === 1) {
                    Cache::put($cacheKey, 1, 720);
                }
            }
            $this->interactiveRecord($data, $token, $openid);
        }
    }

    protected function interactiveRecord($data, $token, $openid)
    {
        //************用户主动发送消息的事件**************//
        $sendMes = ['text', 'image', 'voice', 'video', 'shortvideo', 'location', 'link'];
        if (in_array($data['MsgType'], $sendMes)) {
            return $this->saveFansTime($token, $openid);
        }
        //************   用户主动操作事件  **************//
        $event = ['VIEW', 'CLICK', 'subscribe', 'SCAN', 'scancode_push', 'scancode_waitmsg'];
        if ($data['MsgType'] == 'event' && in_array($data['Event'], $event)) {
            return $this->saveFansTime($token, $openid);
        }
    }

    protected function saveFansTime($token, $openid)
    {
        $redis = Redis::connection();
        $redis->zadd('fans:' . $token, time(), $openid);
    }

}
