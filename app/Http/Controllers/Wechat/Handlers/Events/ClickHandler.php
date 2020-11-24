<?php


namespace App\Http\Controllers\Wechat\Handlers\Events;

use App\Models\Wechat\Replycontent;
use Illuminate\Support\Facades\Cache;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use Illuminate\Support\Facades\Redis;
use App\Models\Wechat\Authcontent;

class ClickHandler
{
    public static function handle($data = null)
    {
        $token = request()->route('token');
        $fansContent = $data['EventKey'];

        $textListKey = 'keys:' . $token . ':' . $data['FromUserName'];

        $contentList = Replycontent::search($fansContent)->where('token', $token)->get();
        $redis = Redis::connection('default');

        //(优先  图文回复) <= 取代 (order排序) type: 0文本,1图文  优先: 完全匹配  模糊匹配  matchtype: 1完全匹配 0模糊匹配
        $hybridData = [];  //混合数据
        $contentList->sortByDesc('order')->each(function ($item) use (&$hybridData, $fansContent, $redis) {
            $cacheKey = 'wechat:replycontent_' . $item['id'];
            //初始化文本数据
            $data = [
                'title' => $item['title'],
                'content' => $item['content'],
            ];
            //图文类型的数据
            if ($item['type'] == 1) {
                $data = [
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'url' => $item['url'],
                    'image' => $item['image'],
                    'id' => $item['id'],
                ];
            }
            if ($item['matchtype'] == 1 && $item['keyword'] == $fansContent) {
                $hybridData[] = $data;
                $redis->incr($cacheKey);
            }
            if ($item['matchtype'] != 1) {
                $hybridData[] = $data;
                $redis->incr($cacheKey);
            }
        });

        $allData = $hybridData;
        //一条的时候默认回复
        if (count($allData) == 1) {
            if (count($allData[0]) == 5) {
                //图文需要判断链接问题
                $item = $allData[0];
                if (empty($item['url'])) {
                    $detailsUrl = config('vueRoute.showImgContent');
                    $detailsUrl = str_replace('{token}', $token, $detailsUrl);
                    $item['url'] = $detailsUrl . $item['id'];
                }
                return new News([new NewsItem($item)]);
            }
            return $allData[0]['content'];
        }
        if ($allData) {
            $text = "回复序号获取相关内容(5分钟内有效):";
            foreach ($allData as $k => $v) {
                if (is_array($v)) {
                    $text .= "\n【 " . $k . " 】" . $v['title'];
                } else {
                    $text .= "\n【 " . $k . " 】" . $v;
                }
            }
            unset($k, $v);
            Cache::put($textListKey, $allData, 5);
            return $text;
        }

        $Authcontent = Authcontent::where(['token' => $token, 'status' => 1])->value('content');
        if (!empty($Authcontent)) return $Authcontent;
    }
}
