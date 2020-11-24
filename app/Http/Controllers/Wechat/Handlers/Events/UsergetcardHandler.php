<?php

namespace App\Http\Controllers\Wechat\Handlers\Events;

use App\Models\Card\MemberCardUser;
use App\Models\Wechat\Wechatapp;

class UsergetcardHandler
{
    protected $token;

    protected $openid;

    public static function handle($data = null)
    {
        $obj = new self();
        $obj->token = request()->route('token');
        $obj->openid = $data['FromUserName'];
        $OuterId = $data['OuterId'] ? $data['OuterId'] : 1;
        $model = null;
        switch ($OuterId){ //场景值
            case 1:
                $obj->saveMemberUser($data);
                break;
            case 2:
                $obj->saveMemberUser($data);
                break;
            default:
                return false;
        }

        return true;
    }

    protected function saveMemberUser($data)
    {
        $app = Wechatapp::initialize($this->token);
        $userInfo = $app->user->get($this->openid);

        $model = new MemberCardUser();
        $model->token = $this->token;
        $model->card_id = $data['CardId'];
        $model->from = $data['OuterId'] == 1 ? $data['SourceScene'] : 'miniProgra';
        $model->openid = $this->openid;
        $model->code = $data['UserCardCode'];
        $model->rdid = '';
        $model->nickName = $userInfo['nickname'];
        $model->avatarUrl = $userInfo['headimgurl'];
        $model->gender = $userInfo['sex'];
        $model->country = $userInfo['country'];
        $model->province = $userInfo['province'];
        $model->city = $userInfo['city'];
        $model->save();
    }
}