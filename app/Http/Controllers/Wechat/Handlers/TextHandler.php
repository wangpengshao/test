<?php

namespace App\Http\Controllers\Wechat\Handlers;

use App\Models\Wechat\Authcontent;
use App\Models\Wechat\Fanscontent;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Replycontent;
use App\Models\Wxuser;
use EasyWeChat\Kernel\Contracts\EventHandlerInterface;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

/**
 * Class TextHandler
 *
 * @package App\Http\Controllers\Wechat\Handlers
 */
class TextHandler implements EventHandlerInterface
{

    /**
     * @param null $data
     * @return bool|News|mixed|string
     */
    public function handle($data = null)
    {
        $token = request()->route('token');
        $fansContent = Arr::get($data, 'Content');

        if (empty($fansContent) && $fansContent !== "0") {
            return false;
        }
        //调试获取openid
        if ($fansContent == 'reopenid') {
            return $data['FromUserName'];
        }
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
            'type' => 0,
            'openid' => $data['FromUserName'],
            'token' => $token,
            'content' => $fansContent,
        ];
        Fanscontent::create($create);

        //判断聊天的时候数据不要进入关键字      ===>  未处理

        //参考咨询  留言功能   start
        $leaveWordKey = 'leaveWord:' . $token . ':' . $data['FromUserName'];
        if (Cache::has($leaveWordKey)) {

            $reader = $this->getReader($token, $data['FromUserName']);
            if (!$reader) {
                return $this->goBindText($token);
            }
            $pavilion = Wxuser::where('token', $token)->first(['glc', 'libcode']);

            $cache = Cache::get($leaveWordKey);
            $url = Str::finish($cache, '/') . 'web/api/leavemsg/wx/saveMiniMsg.html?';
            $obj = [
                'content' => (string)$fansContent,
                'rdid' => (string)$reader['rdid'],
                'openid' => (string)$data['FromUserName'],
                'title' => "留言",
                'rdName' => (string)$reader['name']
            ];
            $params = http_build_query([
                'globalLibraryCode' => $pavilion['glc'],
                'orglib' => $pavilion['libcode'],
                'obj' => json_encode($obj)
            ]);
            $http = new Client();
            $response = $http->get($url . $params);
            $response = json_decode((string)$response->getBody(), true);

            if (Arr::get($response, 'result') == 'suc') {
                Cache::forget($leaveWordKey);
                return '好的，您的留言我们已经收到';
            }
            return '网络繁忙，请稍后再试';

        }

        if ($fansContent === '我要留言') {

            $pavilion = Wxuser::where('token', $token)->first(['glc', 'libcode']);
            $url = 'http://rc.interlib.com.cn:82/rc/web/api/libcode/getByCode.html?';

            $params = http_build_query([
                'globalLibraryCode' => $pavilion['glc'],
                'orglib' => $pavilion['libcode'],
            ]);

            $http = new Client();
            $response = $http->get($url . $params);
            $response = json_decode((string)$response->getBody(), true);

            if (empty($response['json']['url']) || $response['json']['version'] !== '2.0') {
                return '暂不支持留言功能';
            }

            $reader = $this->getReader($token, $data['FromUserName']);
            if (!$reader) {
                return $this->goBindText($token);
            }

            Cache::put($leaveWordKey, $response['json']['url'], 2);
            return '请在120s内输入您想要留言的内容';
        }

        //参考咨询  留言功能   end
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
        //多图文失效注释   保留
//        if (count($newsImgData) > 0) {
//            $items = [];
//            foreach ($newsImgData as $k => $v) {
//                $items[] = new NewsItem($v);
//            }
//            unset($k, $v);
//            return new News($items);
//        }

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

        // 参考咨询  知识库 start
        $knowledge = Wxuser::where('token', $token)->first(['knowledge_sw', 'knowledge_url', 'glc', 'libcode']);

        if ($knowledge['knowledge_sw'] == 1 && $knowledge['libcode']) {

            $textListKey = 'kap:' . $token . ':' . $data['FromUserName'];
            if (is_numeric($fansContent)) {   //回复数字
                $textList = Cache::get($textListKey);
                $items = Arr::get($textList, $fansContent);
                if ($items) {
                    if (count($items) == 4) {
                        return new News([new NewsItem($items)]);
                    }
                    return $items['content'];
                }
            }

            $knowledge_url = $knowledge['knowledge_url'] ?? 'http://rc.interlib.com.cn:82/';
            $url = $knowledge_url . 'rc/web/api/kb/wx/getkblist.html?';
            $params = http_build_query([
                'keyContent' => $fansContent,
                'globalLibraryCode' => $knowledge['glc'],
                'orglib' => $knowledge['libcode']
            ]);

            $http = new Client();
            $response = $http->get($url . $params);
            $response = json_decode((string)$response->getBody(), true);

            $list = $response['list'];
            $listCount = count($list);

            if ($listCount > 0) {

                if ($listCount == 1) {
                    if ($list[0]['IsimgText'] == 1) {
                        $items = [
                            'title' => $list[0]['infoTitle'],
                            'description' => $list[0]['infoContent'],
                            'url' => $list[0]['url'],
                            'image' => $list[0]['coverimg'],
                        ];
                        return new News([new NewsItem($items)]);
                    }
                    return $list[0]['infoContent'];
                }

                $allData = [];
                foreach ($list as $k => $v) {
                    if ($v['IsimgText'] == 1) {
                        $allData[] = [
                            'title' => $v['infoTitle'],
                            'description' => $v['infoContent'],
                            'url' => $v['url'],
                            'image' => $v['coverimg'],
                        ];
                        continue;
                    }
                    $allData[] = [
                        'title' => $v['infoTitle'],
                        'content' => $v['infoContent'],
                    ];;
                }

                if ($allData) {
                    $text = "回复序号获取相关内容(5分钟内有效):";
                    foreach ($allData as $k => $v) {
                        $text .= "\n【 " . $k . " 】" . $v['title'];
                    }
                    unset($k, $v);
                    Cache::put($textListKey, $allData, 5);
                    return $text;
                }
            }
        }

        // 参考咨询  知识库 end

        $Authcontent = Authcontent::where(['token' => $token, 'status' => 1])->value('content');
        if (!empty($Authcontent)) return $Authcontent;

    }


    /**
     * @param $token
     * @return string
     */
    protected function goBindText($token)
    {
        $bindUrl = config('vueRoute.bindReader');
        $bindUrl = str_replace('{token}', $token, $bindUrl);
        return '您尚未绑定帐号，<a href="' . $bindUrl . '">点击这里</a>进行绑定';
    }


    /**
     * @param $token
     * @param $openid
     * @return mixed
     */
    protected function getReader($token, $openid)
    {
        $where = ['token' => $token, 'openid' => $openid, 'is_bind' => 1];
        return Reader::where($where)->first(['rdid', 'name']);
    }

}
