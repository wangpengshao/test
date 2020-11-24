<?php

namespace App\Admin\Controllers\Wechat\Suggestions;

use App\Admin\Controllers\CustomView\ChatMessage;
use App\Admin\Controllers\CustomView\suggestionsDetails;
use App\Http\Controllers\Controller;
use App\Models\Suggestions\SuggestionsList;
use App\Models\Suggestions\SuggestionsMessages;
use App\Models\Wechat\Fans;
use App\Models\Wxuser;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;

class SuggestionsDetailController extends Controller
{
    use HasResourceActions;

    /**
     * time  2019.9.17.
     *
     * @content  显示留言及回复信息页面
     *
     * @author  wsp
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('留言详情');
            $content->description('....');
            $content->row(function ($row) {
                $row->column(4, new suggestionsDetails($this->getSuggestionsInfo()));
                $row->column(4, new ChatMessage($this->getSuggestionsInfo(), $this->getHistory(), $this->getAdminInfo()));

            });

        });
    }

    public function getSuggestionsInfo()
    {
        $id = \request()->input('m_id');
        $token = \request()->session()->get('wxtoken');
        $where = [
            'id' => $id,
            'token' => $token
        ];
        $Suggestions = SuggestionsList::where($where)->first([
            'rdid',
            'openid',
            'title',
            'tel',
            'email',
            'info',
            'img',
            'created_at',
            'other'
        ]);
//        dd($Suggestions->other);
        $fans_where = [
            'openid' => $Suggestions['openid']
        ];
        $fans = Fans::where($fans_where)->first([
            'nickname',
            'headimgurl',
        ]);
        $Suggestions['nickname'] = $fans['nickname'];
        $Suggestions['headimgurl'] = $fans['headimgurl'];
        return $Suggestions;
    }

    public function getHistory()
    {
        $m_id = \request()->input('m_id');
        $token = \request()->session()->get('wxtoken');
        $where = [
            'm_id' => $m_id,
            'token' => $token
        ];
        $MessagesContent = SuggestionsMessages::where($where)->orderBy('created_at', 'Desc')->limit(20)->get();
        //判断是否存在未阅读的数据
        $notRead = $MessagesContent->first(function ($value, $key) {
            if ($value->r_id == 1 && $value->is_reading != 1) {
                return true;
            }
        });
        if ($notRead) {
            SuggestionsMessages::where($where)->where('r_id', 1)->update(['is_reading' => 1]);
        }
        $MessagesContent = $MessagesContent->sortBy('created_at');
        return $MessagesContent;
    }

    // 获取管理员信息
    function getAdminInfo()
    {
        $token = \request()->session()->get('wxtoken');
        $adminInfo = Wxuser::where('token', $token)->first(['wxname']);
        return $adminInfo;
    }

    function saveAdminContent(Request $request)
    {
        $data = $request->input('data');
        $mid = $request->route('mid'); // 关联留言
        $sid = $request->route('sid'); // 关联留言类型
        $token = $request->session()->get('wxtoken');
        $create = [
            'token' => $token,
            'a_reply' => $data,
            'm_id' => $mid,
            'is_reading' => 0,
            'r_id' => 2,
            's_id' => $sid,
        ];
        SuggestionsMessages::create($create);
        $re = ['status' => true, 'message' => '发送成功'];
        return $re;
    }

}
