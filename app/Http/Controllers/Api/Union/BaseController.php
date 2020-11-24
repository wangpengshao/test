<?php

namespace App\Http\Controllers\Api\Union;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Union\UnionReader;
use App\Services\UnionService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class BaseController extends Controller
{
    use ApiResponse;

    public function checkBind(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;

        $reader = UnionReader::where([
            'openid' => $openid,
            'token' => $token,
            'is_bind' => 1
        ])->first(['id', 'rdid', 'name']);

        if ($reader == null) return $this->message('尚未绑定读者证!', false);
        $success = $reader;
        $success['bind_title'] = '你已经绑定联盟证';
        return $this->success($success, true);
    }

    public function pageConfig(Request $request)
    {
        $success = [
            'title' => '泉城图书馆联盟电子证绑定',
            'content' => '',
            'uname' => '证号',
            'uremark' => '请输入证号',
            'pname' => '密码',
            'premark' => '请输入密码',
        ];
        $success['bind_title'] = '您已绑定泉城图书馆联盟电子证';
        $success['options'] = [
            [
                'key' => 'UJN',
                'name' => '济南大学图书馆'
            ]
        ];
        $success['options_key'] = 'libcode';
        return $this->success($success);
    }

    public function bindReader(Request $request)
    {
        if (!$request->filled(['username', 'password', 'token', 'libcode'])) return $this->failed('lack of parameter!', 400);
        $token = $request->input('token');
        $username = $request->input('username');
        $password = $request->input('password');
        $libcode = $request->input('libcode');
        $openid = $request->user()->openid;
        if ($token != $request->user()->token) return $this->failed('token is invalid!', 400);
        //判断是否存在已绑定数据
        if (UnionReader::checkBind($token, $openid)->exists()) {
            return $this->message('抱歉,您已经绑定过读者证了!', false);
        }
        $UnionService = UnionService::make($token);
        $response = $UnionService->confirmclusreader($username, $password, $libcode);
        if ($response['success'] == false) {
            return $this->message(Arr::get($response, 'messagelist.0.message'), false);
        }
        $reader = $response['reader'];
//        $reader = [
//            'rdid' => $username,
//            'rdname' => '测试数据',
//            'rdlib' => $libcode
//        ];
        //新增绑定
        $create = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $username,
            'password' => encrypt($password),
            'is_bind' => 1,
            'name' => $reader['rdname'],
//            'origin_glc' => Arr::get($reader, 'origin_glc'),
            'origin_libcode' => Arr::get($reader, 'rdlib'),
            'is_cluster' => Arr::get($reader, 'is_cluster', 0)
        ];
//        dd($create);
        $createStatus = UnionReader::create($create);
        if ($createStatus == false) {
            return $this->internalError('服务器繁忙,绑定失败!');
        }
        $response = [
            'id' => $createStatus['id'],
            'url' => '',
            'typeData' => '',
            'typeName' => ''
        ];
        return $this->success($response, true);
    }

    public function unBindReader(Request $request)
    {
        //判断是否合法
        $reader = UnionReader::where('id', $request->route('id'))->first(['id', 'token', 'openid']);
        if ($reader == false || $reader->token != $request->user()->token || $reader->openid != $request->user()->openid) {
            return $this->failed('数据不存在!', 400);
        }
        $status = $reader->delete();
        if ($status == false) {
            return $this->internalError('出错了，请稍后再试!');
        }
        return $this->message('操作成功', true);
    }

    public function readerCode(Request $request)
    {
        $token = $request->user()->token;
        $openid = $request->user()->openid;

        $reader = UnionReader::where([
            'openid' => $openid,
            'token' => $token,
            'is_bind' => 1
        ])->first(['id', 'rdid', 'name']);

        if ($reader == null) return $this->message('尚未绑定读者证!', false);
        return $this->success(['qrcode' => $reader['rdid']]);
    }

}
