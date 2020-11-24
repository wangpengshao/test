<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Jobs\OldPlatformTplMsg;
use App\Models\Wechat\TplMsgThird;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MsgSendsController extends Controller
{
    use ApiResponse;

    public function sendTplMsg(Request $request)
    {
        if(!$request->filled(['old_id', 'token', 'appid', 'template_id', 'title', 'content', 'send_type'])){
            return $this->message('缺少必须参数!', 'error');
        }
        $params = $request->input();

        $tpl = TplMsgThird::where(['token'=>$params['token'], 'old_id' => $params['old_id']])->first();
        if($tpl){
            return $this->message('已经加入发送队列了，请勿重复加入', 'error');
        }

        $insertData = [
            'old_id' => $params['old_id'],
            'token' => $params['token'],
            'appid' => $params['appid'],
            'template_id' => $params['template_id'],
            'title' => $params['title'],
            'te1_da' => $params['content'],
            'tpl_content' => $params['template_content'],
            'redirect_type' => empty($params['url']) ? 0 : 1,
            'redirect_url' => $params['url'],
            'send_type' => $params['send_type'],
            'group_tag' => $params['group_tag'],
            'status' => 2
        ];

        $res = TplMsgThird::create($insertData);

        if($res->id){
            OldPlatformTplMsg::dispatch($params['token'], $res->id)->onQueue('disposable');
            return $this->message('已加入发送队列，等待后台发送');
       }

        return $this->message('加入发送队列失败', 'error');
    }
}
