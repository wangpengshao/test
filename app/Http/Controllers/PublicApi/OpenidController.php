<?php

namespace App\Http\Controllers\PublicApi;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Jobs\GroupSendImgMes;
use App\Jobs\GroupSendMes;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use Carbon\Carbon;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class OpenidController extends Controller
{
    use ApiResponse;

    public function getOpenidBase(Request $request)
    {
        if (!$request->filled(['code'])) return $this->message('lack of parameter', false);
        $wxuser = Wxuser::getConfig($request->input('token'));
        $http = new Client();
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $params = http_build_query([
            'appid' => $wxuser['app_id'],
            'secret' => $wxuser['secret'],
            'code' => $request->input('code'),
            'grant_type' => 'authorization_code'
        ]);

        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);

        if (!empty($response['errcode'])) {
            return $this->message($response['errmsg'], false);
        }
        return $this->success(['openid' => $response['openid']], true);
    }

    public function getOpenidInfo(Request $request)
    {
        if (!$request->filled(['code'])) return $this->message('lack of parameter', false);
        $codeKey = 'codeInfo.' . $request->input('token') . '.' . $request->input('code');
        if (Cache::has($codeKey)) {
            return $this->success(Cache::get($codeKey), true);
        }
        $wxuser = Wxuser::getConfig($request->input('token'));
        $http = new Client();
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?';
        $params = http_build_query([
            'appid' => $wxuser['app_id'],
            'secret' => $wxuser['secret'],
            'code' => $request->input('code'),
            'grant_type' => 'authorization_code'
        ]);
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        if (!empty($response['errcode'])) {
            return $this->message($response['errmsg'], false);
        }
        $url = 'https://api.weixin.qq.com/sns/userinfo?';
        $params = http_build_query([
            'access_token' => $response['access_token'],
            'openid' => $response['openid'],
            'lang' => 'zh_CN'
        ]);
        $response = $http->get($url . $params);
        $response = json_decode((string)$response->getBody(), true);
        if (!empty($response['errcode'])) {
            return $this->message($response['errmsg'], false);
        }
        Cache::put($codeKey, $response, 5);
        return $this->success($response, true);
    }

    public function getFansInfo(Request $request)
    {
        if (!$request->filled(['openid'])) return $this->message('lack of parameter', false);

        $openid = explode(',', $request->input('openid'));
        $openid = array_unique($openid);
        $app = Wechatapp::initialize($request->input('token'));
        if (count($openid) == 1) {
            $response = $app->user->get($openid[0]);
        } else {
            $response = $app->user->select($openid);
        }
        if (!empty($response['errcode'])) {
            return $this->message($response['errmsg'], false);
        }
        return $this->success($response['user_info_list'], true);
    }

    public function openidGetReader(Request $request)
    {
        if (!$request->filled(['openid'])) return $this->message('lack of parameter', false);
        $openid = explode(',', $request->input('openid'));
        $openid = array_unique($openid);
        $data = Reader::where(['token' => $request->input('token'), 'is_bind' => 1])->whereIn('openid', $openid)
            ->get(['rdid', 'created_at', 'openid', 'name']);

        if ($data->isEmpty()) {
            return $this->message('抱歉，当前用户尚未绑定读者证!', false);
        }
        return $this->success($data, true);
    }

    public function readerGetOpenid(Request $request)
    {
        if (!$request->filled(['rdid'])) return $this->message('lack of parameter', false);

        $rdid = explode(',', $request->input('rdid'));
        $rdid = array_unique($rdid);
        $data = Reader::where(['token' => $request->input('token'), 'is_bind' => 1])->whereIn('rdid', $rdid)
            ->get(['rdid', 'created_at', 'openid', 'name']);

        if ($data->isEmpty()) {
            return $this->message('抱歉，当前读者尚未绑定微信!', false);
        }
        return $this->success($data, true);
    }

    public function sendTemplate(Request $request)
    {
        if ($request->header('Content-Type') != 'application/json'
            || !$request->filled(['datalist', 'template_id', 'datalist.0.rdid'])
        ) return $this->message('lack of parameter', false);

        $token = $request->input('token');
        $template_id = $request->input('template_id');
        $list = $request->input('datalist');

        $readers = [];
        foreach ($list as $k => $v) {
            $readers[] = array_get($v, 'rdid');
        }
        unset($k, $v);
        $readers = Reader::where(['token' => $token, 'is_bind' => 1])->whereIn('rdid', $readers)
            ->pluck('openid', 'rdid')->toArray();

//        if (count($readers) == 0) return $this->message('当前读者未在微信进行绑定!', false);

        $app = Wechatapp::initialize($token);
        $response = [];
        foreach ($list as $k => $v) {
            $response[] = [
                'rdid' => $v['rdid'],
                'status' => 0,
                'meg' => 'found no binding relationship'
            ];
            $openid = array_get($readers, $v['rdid']);
            if ($openid) {
                $sendData = [
                    'touser' => $openid,
                    'template_id' => $template_id,
                    'url' => $v['url'] ?: '',
                    'data' => $v['keyValArr']
                ];
                $sendStatus = $app->template_message->send($sendData);
                if (!empty($sendStatus['errcode'])) {
                    $response[$k]['meg'] = $sendStatus['errmsg'];
                } else {
                    $response[$k]['status'] = 1;
                    $response[$k]['meg'] = 'ok';
                }
            }
        }
        return $this->success($response, true);
    }

    public function sendTemplateForOpenid(Request $request)
    {
        if ($request->header('Content-Type') != 'application/json'
            || !$request->filled(['datalist', 'template_id', 'datalist.0.openid'])
        ) return $this->message('lack of parameter', false);

        $token = $request->input('token');
        $template_id = $request->input('template_id');
        $list = $request->input('datalist');

        $app = Wechatapp::initialize($token);
        $response = [];
        foreach ($list as $k => $v) {
            $response[] = [
                'openid' => $v['openid'],
                'status' => 0,
                'meg' => 'Send failed'
            ];

            $sendData = [
                'touser' => $v['openid'],
                'template_id' => $template_id,
                'url' => $v['url'] ?: '',
                'data' => $v['keyValArr']
            ];
            $sendStatus = $app->template_message->send($sendData);
            if (!empty($sendStatus['errcode'])) {
                $response[$k]['meg'] = $sendStatus['errmsg'];
            } else {
                $response[$k]['status'] = 1;
                $response[$k]['meg'] = 'ok';
            }

        }
        return $this->success($response, true);
    }

    public function sendMessage(Request $request)
    {
        if ($request->header('Content-Type') != 'application/json'
            || !$request->filled(['datalist', 'datalist.0.rdid', 'datalist.0.text'])
        ) return $this->message('lack of parameter', false);
        $token = $request->input('token');
        $list = $request->input('datalist');

        $readers = [];
        foreach ($list as $k => $v) {
            $readers[] = array_get($v, 'rdid');
        }
        unset($k, $v);
        $readers = Reader::where(['token' => $token, 'is_bind' => 1])->whereIn('rdid', $readers)
            ->pluck('openid', 'rdid')->toArray();

        if (count($readers) == 0) return $this->message('当前读者未在微信进行绑定!', false);

        $app = Wechatapp::initialize($token);
        $response = [];
        foreach ($list as $k => $v) {
            $response[] = [
                'rdid' => $v['rdid'],
                'status' => 0,
                'meg' => 'ok'
            ];
            $openid = array_get($readers, $v['rdid']);
            if ($openid) {
                $message = new Text($v['text']);
                $sendStatus = $app->customer_service->message($message)->to($openid)->send();
                if (!empty($sendStatus['errcode'])) {
                    $response[$k]['meg'] = $sendStatus['errmsg'];
                } else {
                    $response[$k]['status'] = 1;
                }
            }
        }
        return $this->success($response, true);
    }

    public function sendImgMes(Request $request)
    {
        if ($request->header('Content-Type') != 'application/json'
            || !$request->filled(['datalist', 'datalist.0.rdid', 'datalist.0.title'])
        ) return $this->message('lack of parameter', false);
        $token = $request->input('token');
        $list = $request->input('datalist');
        $readers = [];
        foreach ($list as $k => $v) {
            $readers[] = array_get($v, 'rdid');
        }
        unset($k, $v);
        $readers = Reader::where(['token' => $token, 'is_bind' => 1])->whereIn('rdid', $readers)
            ->pluck('openid', 'rdid')->toArray();

        if (count($readers) == 0) return $this->message('当前读者未在微信进行绑定!', false);

        $app = Wechatapp::initialize($token);
        $response = [];
        foreach ($list as $k => $v) {
            $response[] = [
                'rdid' => $v['rdid'],
                'status' => 0,
                'meg' => 'ok'
            ];
            $openid = array_get($readers, $v['rdid']);
            if ($openid) {
                $items = [
                    new NewsItem([
                        'title' => $v['title'],
                        'description' => $v['description'],
                        'url' => $v['url'],
                        'image' => $v['image'],
                    ]),
                ];
                $news = new News($items);
                $sendStatus = $app->customer_service->message($news)->to($openid)->send();
                if (!empty($sendStatus['errcode'])) {
                    $response[$k]['meg'] = $sendStatus['errmsg'];
                } else {
                    $response[$k]['status'] = 1;
                }
            }
        }
        return $this->success($response, true);
    }

    public function groupSendMessage(Request $request)
    {
        if (!$request->filled(['text'])) return $this->message('lack of parameter', false);
        $token = $request->input('token');
        $minTime = Carbon::now()->subDay(2)->addHour(2)->timestamp;
        $maxTime = time();
        $redis = Redis::connection();
        $openid = $redis->zrevrangebyscore('fans:' . $token, $maxTime, $minTime);
        $total = count($openid);
        if ($total == 0) {
            return $this->success(['total' => $total, 'message' => '发送失败,无可发送目标' . $total], false);
        }
        GroupSendMes::dispatch($token, $openid, $request->input('text'));
        return $this->success(['total' => $total, 'message' => '请求成功,已进入发送队列,发送数量为 ' . $total], true);
    }

    public function groupSendImgMes(Request $request)
    {
        if (!$request->filled(['title', 'url'])) return $this->message('lack of parameter', false);
        $token = $request->input('token');
        $minTime = Carbon::now()->subDay(2)->addHour(2)->timestamp;
        $maxTime = time();
        $redis = Redis::connection();
        $openid = $redis->zrevrangebyscore('fans:' . $token, $maxTime, $minTime);
        $total = count($openid);
        if ($total == 0) {
            return $this->success(['total' => $total, 'message' => '发送失败,无可发送目标' . $total], false);
        }
        $item = [
            'title' => $request->input('title'),
            'url' => $request->input('url'),
            'description' => $request->input('description'),
            'image' => $request->input('image')
        ];

        GroupSendImgMes::dispatch($token, $openid, $item);
        return $this->success(['total' => $total, 'message' => '请求成功,已进入发送队列,发送数量为 ' . $total], true);
    }

    public function getActiveUser(Request $request)
    {
        $token = $request->input('token');
        $minTime = Carbon::now()->subDay(2)->addHour(1)->timestamp;
        $maxTime = time();
        $redis = Redis::connection();
        $openid = $redis->zrevrangebyscore('fans:' . $token, $maxTime, $minTime, 'WITHSCORES');
        return $this->success(['total' => count($openid), 'list' => $openid], true);
    }

}
