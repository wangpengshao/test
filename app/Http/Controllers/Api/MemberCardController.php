<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Models\Card\Member;
use App\Models\Card\MemberCardUser;
use Illuminate\Http\Request;
use App\Admin\Extensions\MemberCard;
use App\Http\Controllers\Controller;

class MemberCardController extends Controller
{
    use ApiResponse;

    public function __construct(Request $request)
    {
        $this->middleware('RequiredToken');
    }

    /**
     * 获取卡券
     * @param Request $request
     * @return mixed
     */
    public function getMemberCard(Request $request)
    {
        $card_id = $request->input('card_id');
        if(empty($card_id)){
            return $this->setTypeCode(404)->message('缺少必填参数card_id', false);
        }
        $card = Member::where('card_id', $card_id)->first();
        if(!$card){
            return $this->setTypeCode(404)->message('卡券不存在', false);
        }

        $res = MemberCard::make($request->input('token'))->dispense(['card_id' => $card_id, 'outer_id' => 2]);
        $res = json_decode($res,true)[0];
        $data['cardId'] = $res['cardId'];
        $data['cardExt'] = json_decode($res['cardExt'],true);
        return $this->message($data, true);
    }

    /**
     * 保存用户领卡信息
     * @param Request $request
     * @return mixed
     */
    public function saveMemberCardUser(Request $request)
    {
        if(empty($request->input('card_id')) || empty($request->input('from')) || empty($request->input('openid')) || empty($request->input('code')) || empty($request->input('rdid'))){
            return $this->message('缺少必填参数', false);
        }

        //解码code
        $code = MemberCard::make($request->input('token'))->decryptCode($request->input('code'));
        if($code['errcode']){
            return $this->message($code['errmsg'], false);
        }

        $data = [
            "token" => $request->input('token'),
            "card_id" => $request->input('card_id'),
            "from" => $request->input('from'),
            "openid" => $request->input('openid'),
            "nickName" => $request->input('nickName'),
            "avatarUrl" => $request->input('avatarUrl'),
            "gender" => $request->input('gender'),
            "country" => $request->input('country'),
            "province" => $request->input('province'),
            "city" => $request->input('city'),
            "code" => $code['code'],
            "rdid" => $request->input('rdid')
        ];

        MemberCardUser::create($data);

        return $this->message('保存成功',true);
    }
}
