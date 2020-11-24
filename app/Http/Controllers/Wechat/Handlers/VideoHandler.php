<?php

namespace App\Http\Controllers\Wechat\Handlers;


use App\Models\Wechat\Fanscontent;
use App\Models\Wechat\Wechatapp;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;

/**
 * Class TextHandler
 *
 * @package App\Http\Controllers\Wechat\Handlers
 */
class VideoHandler implements EventHandlerInterface
{

    public function handle($data = null)
    {
        $token = request()->route('token');
        $fansContent = 'video';
        $create = [
            'type' => 3,
            'openid' => $data['FromUserName'],
            'token' => $token,
            'content' => $fansContent,
            'mediaId' => $data['MediaId'],
            'thumbMediaId'=>$data['ThumbMediaId']
        ];
        Fanscontent::create($create);
        /* 保存素材(考虑队列)  start */
        $app = Wechatapp::initialize($token);
        $stream = $app->media->get($data['MediaId']);

        if ($stream instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $stream->save('temporaryMaterial/' . $token.'/Video');
        }
        /* 保存素材(考虑队列)  end */
        return false;
    }

}
