<?php

namespace App\Models\WechatApi;

use App\Models\Wechat\Wechatapp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class TemplateMesList extends Model
{

    public function paginate()
    {
        $response = self::getCache(session('wxtoken'));
        $template_list = Arr::get($response, 'template_list');
        $total = count($template_list);
        $tags = static::hydrate($template_list);
        $paginator = new LengthAwarePaginator($tags, $total, 25);
        $paginator->setPath(url()->current());
        return $paginator;
    }

    static public function getCurrent($token)
    {
        $response = self::getCache($token);
        if (!Arr::has($response, 'template_list')) {
            return [];
        }
        return Arr::where($response['template_list'], function ($value, $key) {
            if ($value['title'] !== '订阅模板消息' && $value['content'] !== '{{content.DATA}}') {
                return true;
            }
        });
    }

    static public function getCache($token, $minute = 30)
    {
        $cacheKey = 'wechat:templateList:' . $token;
        $response = Cache::get($cacheKey);

        if (empty($response)) {
            $app = Wechatapp::initialize($token);
            $response = $app->template_message->getPrivateTemplates();
            Cache::put($cacheKey, $response, $minute);
        }

        return $response;
    }
}
