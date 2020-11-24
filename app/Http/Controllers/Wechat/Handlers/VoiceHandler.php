<?php

namespace App\Http\Controllers\Wechat\Handlers;

use App\Models\Wechat\Authcontent;
use App\Models\Wechat\Fanscontent;
use App\Models\Wechat\Replycontent;
use App\Models\Wechat\Wechatapp;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use FFMpeg\FFMpeg;
use FFMpeg\Format\Audio\Wav;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class VoiceHandler implements EventHandlerInterface
{
    public function handle($data = null)
    {
        $token = request()->route('token');

        $fansContent = $data['Recognition'];

        $textListKey = 'keys:' . $token . ':' . $data['FromUserName'];

        if (is_numeric($fansContent)) {
            $textList = Cache::get($textListKey);
            $items = Arr::get($textList, $fansContent);
            if ($items) {
                if (count($items) == 5) {
                    if (empty($items['url'])) {
                        $detailsUrl = config('vueRoute.showImgContent');
                        $detailsUrl = str_replace('{token}', $token, $detailsUrl);
                        $items['url'] = $detailsUrl . $items['id'];
                    }
                    return new News([new NewsItem($items)]);
                }
                return $items['content'];
            }
        }

        $create = [
            'type' => 1,
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

            $armFolder = 'temporaryMaterial/' . $token . '/Voice';
            $stream->save($armFolder);
            $armFile = $armFolder . '/' . $data['MediaId'] . '.amr';

            $ffmpeg = FFMpeg::create(array(
                'ffmpeg.binaries' => config('envCommon.FFMPEG_PATH') . '/ffmpeg',
                'ffprobe.binaries' => config('envCommon.FFMPEG_PATH') . '/ffprobe',
                'timeout' => 3600, // The timeout for the underlying process
                'ffmpeg.threads' => 12,   // The number of threads that FFMpeg should use
            ));

            $newFile = $armFolder . '/' . $data['MediaId'] . '.wav';
            $audio = $ffmpeg->open($armFile);

            $format = new Wav();

            $format->setAudioChannels(1);
            $audio->filters()->resample('16000');
            $audio->save($format, $newFile);
            unlink($armFile);
        }
        /* 保存素材 end */
        if (!empty($fansContent)) {
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
}
