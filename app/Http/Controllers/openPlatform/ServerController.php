<?php

namespace App\Http\Controllers\openPlatform;

use App\Http\Controllers\Controller;
use EasyWeChat\Factory;
use EasyWeChat\OpenPlatform\Server\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Cache\Simple\RedisCache;


/**
 * Class WeChatController
 *
 * @package App\Http\Controllers\Wechat
 */
class ServerController extends Controller
{
    protected $config = [
        'app_id' => 'wx3c9b4e3029011f4c',
        'secret' => 'fb0e7e923ecb28fe497f9e2d3e859c63',
        'token' => 'JvvrAl17qatMPKF8cnnn',
        'aes_key' => 'QvMFsdvYlldvlXJIeQAxfjvtLqUmtInkdaPwekuqwyL',
        'log' => ['level' => 'error']
    ];

    public function authorization(Request $request)
    {
//        Log::info('authorization' . $request->getContent());

        $openPlatform = Factory::openPlatform($this->config);

        $predis = app('redis')->connection()->client(); // connection($name), $name 默认为 `default`
        $cache = new RedisCache($predis);
        $openPlatform->rebind('cache', $cache);

        $server = $openPlatform->server;
        return $server->serve();

    }

    public function callback(Request $request)
    {
//        Log::info('callback' . $request->getContent());

        $openPlatform = Factory::openPlatform($this->config);

        $predis = app('redis')->connection()->client(); // connection($name), $name 默认为 `default`
        $cache = new RedisCache($predis);
        $openPlatform->rebind('cache', $cache);

        $server = $openPlatform->server;

        // 处理授权成功事件
        $server->push(function ($message) {
            // ...
        }, Guard::EVENT_AUTHORIZED);

        // 处理授权更新事件
        $server->push(function ($message) {
            // ...
        }, Guard::EVENT_UPDATE_AUTHORIZED);

        // 处理授权取消事件
        $server->push(function ($message) {
            // ...
        }, Guard::EVENT_UNAUTHORIZED);


        $response = $server->serve();
        return $response;
    }

    public function auth()
    {
        $openPlatform = Factory::openPlatform($this->config);

        $predis = app('redis')->connection()->client(); // connection($name), $name 默认为 `default`
        $cache = new RedisCache($predis);
        $openPlatform->rebind('cache', $cache);

//        $response = $openPlatform->getMobilePreAuthorizationUrl('http://vue.s1.natapp.cc');
        $response2 = $openPlatform->getPreAuthorizationUrl('https://uwei.dataesb.com/api/openPlatform/authCallback');
//        dd('<a href="' . $response2 . '">pc</a>');
//       return '<a href="' . $response2 . '">pc</a>';
        echo '<a href="' . $response2 . '">pc</a>';
    }

}
