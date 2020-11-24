<?php

namespace App\Http\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Wechat\Handlers\AllHandler;
use App\Http\Controllers\Wechat\Handlers\EventHandler;
use App\Http\Controllers\Wechat\Handlers\ImageHandler;
use App\Http\Controllers\Wechat\Handlers\TextHandler;
use App\Http\Controllers\Wechat\Handlers\VideoHandler;
use App\Http\Controllers\Wechat\Handlers\VoiceHandler;
use App\Models\Wechat\Wechatapp;
use Illuminate\Http\Request;
use EasyWeChat\Kernel\Messages\Message;

/**
 * Class WeChatController
 *
 * @package App\Http\Controllers\Wechat
 */
class WeChatController extends Controller
{
//    /**
//     * @param \Illuminate\Http\Request $request
//     *        const TEXT = 2;
//     *        //        const IMAGE = 4;
//     *        const VOICE = 8;
//     *        const VIDEO = 16;
//     *        const SHORT_VIDEO = 32;
//     *        const LOCATION = 64;
//     *        const LINK = 128;
//     *        const DEVICE_EVENT = 256;
//     *        const DEVICE_TEXT = 512;
//     *        const FILE = 1024;
//     *        const TEXT_CARD = 2048;
//     *        const TRANSFER = 4096;
//     *        const EVENT = 1048576;
//     *        const MINIPROGRAM_PAGE = 2097152;
//     *        const ALL = 1049598;
//     *
//     * @return mixed
//     */
    public function index(Request $request)
    {
        $app = Wechatapp::initialize($request->route('token'));

        $app->server->push(AllHandler::class, Message::ALL);               // 全部，粉丝记录
        // 同时处理多种类型的处理器
        $app->server->push(TextHandler::class, Message::TEXT);             // 文本消息
        $app->server->push(ImageHandler::class, Message::IMAGE);           // 图片消息
        $app->server->push(VoiceHandler::class, Message::VOICE);           // 语音消息
        $app->server->push(VideoHandler::class, Message::VIDEO);           // 视频消息

        $app->server->push(EventHandler::class, Message::EVENT);           // 事件消息

        // 在 laravel 中：
        return $app->server->serve();
    }


}
