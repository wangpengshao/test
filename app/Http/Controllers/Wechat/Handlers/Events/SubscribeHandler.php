<?php

namespace App\Http\Controllers\Wechat\Handlers\Events;

use App\Jobs\EsAddRecords;
use App\Models\Wechat\Replycontent;
use App\Models\Wechat\Replysub;
use App\Services\ScanCodeTool;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class SubscribeHandler
{

    public static function handle($data = null)
    {
        $token = \request()->route('token');
        $openid = $data['FromUserName'];
        $textListKey = 'keys:' . $token . ':' . $data['FromUserName'];
        $replyData = Cache::remember('sub:reply:' . $token, 30, function () use ($token) {
            return Replysub::whereToken($token)->first();
        });

        //带参数二维码
        if (isset($data['Ticket']) && isset($data['EventKey'])) {
            $eventKey = json_decode(str_replace('qrscene_', '', $data['EventKey']), true);
            $type = Arr::get($eventKey, 'type');
            switch ($type) {
                case 'task':
                    //推广二维码  => 任务类型
                    $ScanCodeTool = new ScanCodeTool();
                    $ScanCodeTool->ScanSubTask($replyData, $eventKey, $token, $openid);
                    break;
                case 'seo':
                    //粉丝分组二维码 || 统计二维码
                    $ScanCodeTool = new ScanCodeTool();
                    $ScanCodeTool->ScanSubSeo($replyData, $eventKey, $token, $openid);
                    break;
            }
        }

        //进行记录   关注事件
        $builderData = [
            'token' => $token,
            'openid' => $data['FromUserName'],
            'type' => 1,
            'event_str' => '关注',
            'created_at' => date('Y-m-d H:i:s', $data['CreateTime'])
        ];
        EsAddRecords::dispatch('event', $builderData);

        if (empty($replyData)) return false;
        //判断是否开启关键字
        if ($replyData->type != 1) {
            return (empty($replyData->content)) ? false : $replyData->content;
        }

        $fansContent = $replyData->keyword;
        $contentList = Replycontent::search($fansContent)->where('token', $token)->get();

        //(优先  图文回复) <= 取代 (order排序) type: 0文本,1图文  优先: 完全匹配  模糊匹配  matchtype: 1完全匹配 0模糊匹配
        $hybridData = [];  //混合数据
        $contentList->sortByDesc('order')->each(function ($item) use (&$hybridData, $fansContent) {
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
            }
            if ($item['matchtype'] != 1) {
                $hybridData[] = $data;
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
            $text = "输入序号获取相关内容:";
            foreach ($allData as $k => $v) {
                if (is_array($v)) {
                    $text .= "\n【 " . $k . " 】" . $v['title'];
                } else {
                    $text .= "\n【 " . $k . " 】" . $v;
                }
            }
            unset($k, $v);
            Cache::put($textListKey, $allData, 2);
            return $text;
        }
        return false;

    }

}
