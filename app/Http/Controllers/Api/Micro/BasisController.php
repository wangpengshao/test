<?php

namespace App\Http\Controllers\Api\Micro;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Bindweb;
use App\Models\Wechat\Imagewechat;
use App\Models\Wechat\IndexMenu;
use App\Models\Wechat\MenuClassify;
use App\Models\Wechat\OtherConfig;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Replycontent;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;

/**
 * 基础接口
 * Class BasisController
 * @package App\Http\Controllers\Api\Micro
 */
class BasisController extends Controller
{
    use ApiResponse;

    /**
     * Vue 配置 接口
     * @param Request $request
     * @return mixed
     */
    public function vueConfig(Request $request)
    {
        if (!$request->filled(['token', 'time', 'sign'])) {
            return $this->failed('lack of parameter', 400);
        }
        $token = $request->input('token');
        $time = $request->input('time');
        $sign = $request->input('sign');
        if (md5($token . $time . config('envCommon.ENCRYPT_STR')) !== $sign) {
            return $this->failed('invalid sign.', 400);
        }
        $wxuser = Wxuser::getCache($token);
        if (empty($wxuser)) return $this->failed('invalid token.', 400);
        $success = $wxuser->only([
            'appid', 'wxname', 'headerpic', 'status', 'qr_code', 'libcode', 'type', 'lat', 'lng',
            'glc', 'activity_sw', 'guesslike_sw', 'newbook_sw', 'yujie_sw', 'yuyue_sw', 'template_id',
            'auth_type', 'is_cluster'
        ]);
        //Other custom  默认值
        $success['vue_nav_sw'] = 1;
        $customOp = OtherConfig::otherCustom($wxuser['id'])->first();
        if ($customOp) {
            $success['vue_nav_sw'] = $customOp->vue_nav_sw;
        }

        if ($success['is_cluster'] == 1) {
//            $js = $wxuser['opacurl'] . 'media/js/custominfo/libcodeURL.js';
            $success['libcodeJS_url'] = 'https://u.interlib.cn/uploads/libcodeURL.js';
        }
        return $this->success($success, true);
    }

    /**
     * vue 微信调用 jssdk
     * @param Request $request
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function vueJsSdk(Request $request)
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
        $success = $app->jssdk->buildConfig([], $debug = false, $beta = false, $json = false);
        return $this->success($success, true);
    }

    /**
     * 读者绑定页面配置
     * @param Request $request
     * @return mixed
     */
    public function bindPageLayout(Request $request)
    {
        $success = Bindweb::getCache($request->input('token'));
        $success['options'] = [];
        $success['options_key'] = '';
        return $this->success($success, true);
    }

    /**
     * vue 首页轮播图片
     * @param Request $request
     * @return mixed
     */
    public function vueHomePicture(Request $request)
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

    /**
     * 小黑屋 - 程序查看授权所属信息  专用
     * @param Request $request
     * @return mixed|string
     */
    public function smallShortcut(Request $request)
    {
        if ($request->input('sign') == 'uwei2019') {
            $token = $request->user()->token;
            $openid = $request->user()->openid;
            $wxuser = Wxuser::getCache($token);
            $wxData = [
                'opac_url' => $wxuser['opacurl'],
                'openlib_url' => $wxuser['openlib_url'],
                'glc' => $wxuser['glc'],
                'libcode' => $wxuser['libcode'],
                'wxname' => $wxuser['wxname'],
                'token' => $token,
                'auth_type' => $wxuser['auth_type'],
                'is_cluster' => $wxuser['is_cluster'],
            ];
            $request->user()->wxData = $wxData;
            $reader = Reader::checkBind($openid, $token)->first(['name', 'rdid']);
            $request->user()->reader = $reader;
            return $request->user();

        }
        return '这是个小黑屋';
    }

    /**
     * vue 展示 文章详情
     * @param Request      $request
     * @param Replycontent $model
     * @return mixed
     */
    public function getImgContent(Request $request, Replycontent $model)
    {
        $id = $request->route('id');
        $token = $request->user()->token;

        $where = ['token' => $token, 'id' => $id, 'type' => 1];
        $content = $model->where($where)->first([
            'content', 'created_at', 'title', 'description', 'content', 'image'
        ]);
        if (empty($content) || !is_numeric($id)) {
            return $this->internalError('抱歉,内容不存在');
        }
//        unset($where['id']);
//        // 获取 “上一篇” 的 ID
//        $previousId = $model->where($where)->where('id', '<', $id)->whereNull('url')->max('id');
//        // 同理，获取 “下一篇” 的 ID
//        $nextId = $model->where($where)->where('id', '>', $id)->whereNull('url')->min('id');
        $cacheKey = 'wechat:replycontent_' . $id;
        $content->views = Redis::connection('default')->incr($cacheKey);
        $content->previousId = null;
        $content->nextId = null;
        return $this->success($content);
    }

    /**
     * vue 展示 资源类目
     * @param Request $request
     * @return mixed
     */
    public function menuClassify(Request $request)
    {
        $token = $request->input('token');
        $id = $request->input('id');
        $query = MenuClassify::isShow($token);

        $query->when(!empty($id), function ($q) use ($id) {
            return $q->where('id', $id);
        });
        $success = $query->with('menus')->orderBy('order')
            ->get(['id', 'title', 'desc', 'logo']);
        return $this->success($success, true);
    }

    /**
     * 查询密码配置
     * @param Request $request
     * @return mixed
     */
    public function pwdConfig(Request $request)
    {
        $token = $request->input('token');
        // 根据当前token去查询密码的配置项信息
        $wxuser = Wxuser::getCache($token);
        $config = OtherConfig::getPwdConfig($wxuser['id'])->first();
        if (empty($config)) {
            return $this->message('当前馆未配置密码项!', false);
        }
        $success = [
            'check_sw' => $config['pw_check_sw'],
            'min_length' => $config['pw_min_length'],
            'max_length' => $config['pw_max_length'],
            'type' => $config['pw_type'],
            'prompt' => $config['pw_prompt']
        ];
        return $this->success($success, true);
    }

}
