<?php

namespace App\Http\Controllers\Web\Custom;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\SafeguardComments;
use App\Models\Wechat\SafeguardLike;
use App\Models\Wechat\Wechatapp;
use App\Services\WechatOAuth;
use Illuminate\Http\Request;

class SafeguardController extends Controller
{
    use ApiResponse;

    //入口首页
    public function index(Request $request)
    {
        $token = $request->input('token');
        if (empty($token)) abort(500);
        $fansInfo = $this->getFansInfo($request);
        $myLike = SafeguardLike::my($token, $fansInfo['openid']);
        $app = Wechatapp::initialize('18c6684c');

        return view('web.safeguard.index', [
            'myLike' => $myLike['like'],
            'ajaxUrl' => route('epidemicPrevention::ajax'),
            'saveCommentsUrl' => route('epidemicPrevention::ajax', $request->input()),
            'saveLikeUrl' => route('epidemicPrevention::like', $request->input()),
            'app' => $app
        ]);
    }

    public function ajaxComments(Request $request)
    {
        if ($request->isMethod('post')) {
            $content = $request->input('content');
            $fansInfo = $this->getFansInfo($request);
            $create = [
                'openid' => $fansInfo['openid'],
                'token' => $request->input('token'),
                'nickname' => $fansInfo['nickname'],
                'headimgurl' => $fansInfo['headimgurl'],
                'content' => $content,
                'like_n' => 0,
                'status' => 0
            ];
            $status = SafeguardComments::create($create);
            if ($status) {
                return $this->success($status, true);
            }
            return $this->message('评论失败，请稍后再试!');
        }
        $paginate = SafeguardComments::where('status', 1)->orderBy('created_at', 'desc')->paginate(10);
        return $paginate;
    }

    public function saveLike(Request $request)
    {
        $token = $request->input('token');
        $is_like = $request->input('is_like');
        $id = $request->input('id');
        if (empty($token)) abort(500);

        $fansInfo = $this->getFansInfo($request);
        $my = SafeguardLike::my($token, $fansInfo['openid']);
        $myLike = $my['like'];

        if ($is_like == "true") {
            if (!in_array($id, $myLike)) {
                array_push($myLike, $id);
                $my->like = array_filter($myLike);
                $my->save();
                SafeguardComments::changeLike($id, 1);
            }
            return $this->message('点赞成功', true);
        }
        foreach ($myLike as $k => $v) {
            if ($v === $id) {
                unset($myLike[$k]);
            }
        }
        $my->like = $myLike;
        $my->save();
        SafeguardComments::changeLike($id, -1);
        return $this->message('取消成功', true);
    }

    private function getFansInfo(Request $request)
    {
        $type = $request->input('type', 'uWei2020');
        $token = $request->input('token');
        if ($type == 'uWei2020') {
            $WechatOAuth = WechatOAuth::make($token);
            return $WechatOAuth->webOAuth($request);
        }
        $userKey = 'safeguard:user:' . $token;
        $fansInfo = $request->session()->get($userKey);
        if ($fansInfo == null) {
            $sign = $request->input('sign');
            $openid = $request->input('openid');
            $nickname = $request->input('nickname');
            $headimgurl = $request->input('headimgurl');

            if (!$request->filled(['sign', 'openid', 'nickname', 'headimgurl'])
                || $sign != md5($openid . 'godBless')
            ) abort(500);
            return [
                'openid' => $openid,
                'nickname' => $nickname,
                'headimgurl' => $headimgurl,
            ];
        }

    }
}

