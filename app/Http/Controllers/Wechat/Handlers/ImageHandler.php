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
class ImageHandler implements EventHandlerInterface
{

    public function handle($data = null)
    {
        $token = request()->route('token');
        $fansContent = $data['PicUrl'];
        $create = [
            'type' => 2,
            'openid' => $data['FromUserName'],
            'token' => $token,
            'content' => $fansContent,
            'mediaId' => $data['MediaId']
        ];
        Fanscontent::create($create);
        /* 保存素材(考虑队列) start */
        $app = Wechatapp::initialize($token);
        $stream = $app->media->get($data['MediaId']);

        if ($stream instanceof \EasyWeChat\Kernel\Http\StreamResponse) {
            $stream->save('temporaryMaterial/' . $token.'/Image');
        }
        /* 保存素材 end */
    }

}
