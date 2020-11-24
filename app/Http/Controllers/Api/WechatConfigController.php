<?php

namespace App\Http\Controllers\Api;

use App\Models\Wechat\Bindweb;
use App\Models\Wechat\Imagewechat;
use App\Models\Wechat\IndexMenu;
use App\Models\Wechat\QrCodeConfig;
use App\Models\Wechat\QrCodeList;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Replycontent;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

class WechatConfigController extends BaseController
{

    public function index(Request $request)
    {
        if (!$request->filled(['token', 'time', 'sign'])) return $this->failed('lack of parameter', 400);
        $token = $request->input('token');
        $time = $request->input('time');
        $sign = $request->input('sign');
        if (md5($token . $time . config('envCommon.ENCRYPT_STR')) !== $sign) {
            return $this->failed('invalid sign.', 400);
        }
        $wxuser = Wxuser::getCache($token);
        if (empty($wxuser)) return $this->failed('invalid token.', 400);
        $response = $wxuser->only([
            'appid', 'wxname', 'headerpic', 'status', 'qr_code', 'libcode', 'type', 'lat', 'lng',
            'glc', 'activity_sw', 'guesslike_sw', 'newbook_sw', 'yujie_sw', 'yuyue_sw', 'template_id',
            'auth_type', 'is_cluster'
        ]);
        return $this->success($response, true);
    }

    public function jssdkConfig(Request $request)
    {
        $token = $request->input('token');
        $messages = [
            'required' => '缺少 :attribute 参数.',
            'url' => '非法的 :attribute 参数.',
        ];
        $validator = Validator::make($request->input(), [
            'targetUrl' => 'required|url',
        ], $messages);
        if ($validator->fails()) {
            return $this->message($validator->errors()->first('targetUrl'), false);
        }
        $app = Wechatapp::initialize($token);

        $app->jssdk->setUrl($request->input('targetUrl'));

        return $this->success($app->jssdk->buildConfig([], $debug = false, $beta = false, $json = false), true);
    }

    public function getBindWebConfig(Request $request)
    {
        $response = Bindweb::whereToken($request->input('token'))->whereStatus(1)
            ->first(['title', 'content', 'uname', 'uremark', 'pname', 'premark']);
        $response = ($response) ?: [
            'title' => '读者账号绑定',
            'content' => '',
            'uname' => '读者账号',
            'uremark' => '请输入读者账号',
            'pname' => '读者密码',
            'premark' => '请输入读者密码'
        ];
        return $this->success($response, true);
    }

    public function getLunboMenu(Request $request)
    {
        $token = $request->input('token');
        $openid = $request->input('openid');
        $imageList = Imagewechat::vueCache($token);
        $menuList = IndexMenu::vueCache($token);
        $where = [
            'token' => $token,
            'openid' => $openid
        ];
        $created_at = '';
        if ($openid) {
            $created_at = DB::table('w_first_time')->where($where)->value('created_at');
            if ($created_at === null) {
                $created_at = date('Y-m-d H:i:s');
                $where['created_at'] = $created_at;
                DB::table('w_first_time')->insert($where);
            }
        }
        $response = [
            'imageList' => $imageList,
            'menuList' => $menuList,
            'first_time' => $created_at
        ];
        return $this->success($response, true);
    }

    public function getSeoQrCode(Request $request)
    {
        $token = $request->user()->token;

        $reader = $this->firstBind($request);
        if ($reader == false) {
            return $this->failed('尚未绑定读者证!', 401);
        }

        $wxuser = $this->getWxuserCache($token);
        if ($wxuser['type'] != 1) {
            return $this->failed('抱歉，需要服务号类型的公众号才能使用此功能!', 400);
        }

        $qrConfig = QrCodeConfig::whereToken($token)->first();
        if (empty($qrConfig) || $qrConfig['status'] == 0) {
            return $this->failed('抱歉，此功能尚未开启!!', 400);
        }

        $qrTask = $qrConfig->hasOneTask;

        if (!empty($qrTask) && Carbon::now()->between(Carbon::parse($qrTask['s_time']), Carbon::parse($qrTask['e_time']))) {
            return $this->failed('抱歉，当前活动时间已结束!!', 400);
        }
        //判断是否已经生成过了 t_id  token rdid  status
        $where = [
            'token' => $token,
            'rdid' => $reader['rdid'],
            'type' => $qrConfig['type'],
            't_id' => $qrConfig['t_id']
        ];
        $first = QrCodeList::where($where)->first(['expire_at', 'status', 'ticket', 'url', 'type']);
        if (!empty($first)) {
            if ($first['status'] != 1 || (Carbon::now()->gt($first['expire_at']) && $first['type'] !== 1)) {
                return $this->failed('抱歉，此二维码已经失效了!!', 400);
            }
            return $this->success($first);
        }

        $app = Wechatapp::initialize($token);
        $qrData = [
            'rdid' => $reader['rdid'],
            't_id' => $qrConfig['t_id'],
            'type' => 'task'
        ];
        $s = Carbon::now()->addDays($qrConfig['days'])->diffInSeconds();
        $expire_at = Carbon::now()->addSecond($s)->toDateTimeString();

        if ($qrConfig['type'] === 1) {
            //永久二维码
            $response = $app->qrcode->forever(json_encode($qrData));
            $qrData += [
                'type' => 1
            ];
        } else {
            //临时二维码  type=0
            $response = $app->qrcode->temporary(json_encode($qrData), $s);
            $qrData += [
                'expire_at' => $expire_at,
                'type' => 0
            ];
        }

        if (!empty($response['errcode'])) {
            return $this->failed($response['errmsg'], 400);
        }
        $qrData += [
            'token' => $token,
            'ticket' => $response['ticket'],
            'url' => $response['url'],
            'status' => 1,
        ];
        QrCodeList::create($qrData);
        return $this->success(array_only($qrData, ['status', 'ticket', 'url', 'expire_at']));
    }

    public function getImgContent(Request $request, Replycontent $model)
    {
        $id = $request->route('id');
        $token = $request->user()->token;

        $where = ['token' => $token, 'id' => $id, 'type' => 1];
        $content = $model->where($where)->first([
            'content', 'created_at', 'title', 'description', 'content', 'image'
        ]);
        if (empty($content)) {
            return $this->internalError('抱歉,内容不存在');
        }
        unset($where['id']);
//        // 获取 “上一篇” 的 ID
        $previousId = $model->where($where)->where('id', '<', $id)->whereNull('url')->max('id');
//        // 同理，获取 “下一篇” 的 ID
        $nextId = $model->where($where)->where('id', '>', $id)->whereNull('url')->min('id');

        $cacheKey = 'wechat:replycontent_' . $id;

        $content->views = Redis::connection('default')->incr($cacheKey);
        $content->previousId = $previousId;
        $content->nextId = $nextId;
        return $this->success($content);
    }

    public function smallShortcut(Request $request)
    {
        if ($request->input('sign') == 'uwei2019') {
            $token = $request->user()->token;
            $openid = $request->user()->openid;
            $wxuser = Wxuser::getCache($token);
            $wxData = [
                'opac' => $wxuser['opacurl'],
                'openlib_url' => $wxuser['openlib_url'],
                'glc' => $wxuser['glc'],
                'libcode' => $wxuser['libcode'],
                'wxname' => $wxuser['wxname'],
            ];
            $request->user()->wxData = $wxData;
            $reader = Reader::checkBind($openid, $token)->first(['name', 'rdid']);
            $request->user()->reader = $reader;
            return $request->user();

        }
        return '这是个小黑屋';
    }

}
