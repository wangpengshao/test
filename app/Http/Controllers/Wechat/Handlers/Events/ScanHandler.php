<?php


namespace App\Http\Controllers\Wechat\Handlers\Events;

use App\Models\Wechat\Replycontent;
use App\Services\ScanCodeTool;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class ScanHandler
{
    public static function handle($data = null)
    {
        $token = \request()->route('token');
        $openid = $data['FromUserName'];
        $eventKey = json_decode($data['EventKey'], true);

        $textListKey = 'keys:' . $token . ':' . $data['FromUserName'];

        $keyword = '';

        $type = Arr::get($eventKey, 'type');
        switch ($type) {
            case 'task':
                //推广二维码  => 任务类型
                $ScanCodeTool = new ScanCodeTool();
                $ScanCodeTool->qrTask($keyword, $eventKey, $token, $openid);
                break;
            case 'seo':
                //粉丝分组二维码 || 统计二维码
                $ScanCodeTool = new ScanCodeTool();
                $ScanCodeTool->qrSeo($keyword, $eventKey, $token, $openid);
                break;
        }

        // 不存在关键字
        if (empty($keyword)) {
            return false;
        }
        $fansContent = $keyword;

        $contentList = Replycontent::search($fansContent)->where('token', $token)->get();
        $newsImgData = [];
        $contentList->sortByDesc('matchtype')->each(function ($item, $key) use (&$newsImgData, $fansContent) {
            //判断是否完全匹配
            if ($item['type'] == 1) {
                $data = [
                    'title' => $item['title'],
                    'description' => $item['description'],
                    'url' => $item['url'],
                    'image' => $item['image'],
                    'id' => $item['id'],
                ];
                if ($item['matchtype'] == 1 && $item['keyword'] == $fansContent) {
                    $newsImgData[] = $data;
                }
                if ($item['matchtype'] == 0) {
                    $newsImgData[] = $data;
                }
            }

        });
        //后取文本
        $newsTextData = [];
        $contentList->sortByDesc('matchtype')->each(function ($item, $key) use (&$newsTextData, $fansContent) {
            if ($item['type'] == 0) {
                $data = [
                    'title' => $item['title'],
                    'content' => $item['content'],
                ];
                if ($item['matchtype'] == 1 && $item['keyword'] == $fansContent) {
                    $newsTextData[] = $data;
                }
                if ($item['matchtype'] == 0) {
                    $newsTextData[] = $data;
                }
            }
        });

        $allData = array_merge($newsImgData, $newsTextData);
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
            Cache::put($textListKey, $allData, 1);
            return $text;
        }
        return false;
    }


}
