<?php

namespace App\Admin\Controllers\Wechat\Recommend;

use App\Admin\Controllers\CustomView\ChatInfo;
use App\Admin\Controllers\CustomView\MessageDetails;
use App\Http\Controllers\Controller;
use App\Models\Recommend\MessageList;
use App\Models\Recommend\RecommendBooks;
use App\Models\Wechat\Fans;
use App\Models\Wxuser;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;

class MessageDetailController extends Controller
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
                $row->column(4, new MessageDetails($this->getMessageInfo()));
                $row->column(4, new ChatInfo($this->getMessageInfo(), $this->getHistory(), $this->getAdminInfo()));

            });

        });
    }

    public function getMessageInfo()
    {
        $id = \request()->input('m_id');
        $token = \request()->session()->get('wxtoken');
        $where = [
            'm_id' => $id,
            'token' => $token,
            'r_id' => 1
        ];
        $Message = MessageList::where($where)->first([
            'rdid',
            'openid',
            'created_at',
        ]);
        // 获取读者头像等信息
        $fans_where = [
            'openid' => $Message['openid']
        ];
        $fans = Fans::where($fans_where)->first([
            'nickname',
            'headimgurl',
        ]);
        // 获取所评论对象书单的信息
        $book_where = [
            'id' => $id,
            'token' => $token,
        ];
        $book = RecommendBooks::where($book_where)->first([
           'title',
           'image'
        ]);
        $Message['nickname'] = $fans['nickname'];
        $Message['headimgurl'] = $fans['headimgurl'];
        $Message['title'] = $book['title'];
        $Message['image'] = $book['image'];
        return $Message;
    }

    public function getHistory()
    {
        $m_id = \request()->input('m_id');
        $token = \request()->session()->get('wxtoken');
        $where = [
            'm_id' => $m_id,
            'token' => $token
        ];
        $MessagesContent = MessageList::where($where)->orderBy('created_at', 'Desc')->limit(20)->get();
        //判断是否存在未阅读的数据
        $notRead = $MessagesContent->first(function ($value, $key) {
            if ($value->r_id == 1 && $value->is_reading != 1) {
                return true;
            }
        });
        if ($notRead) {
            MessageList::where($where)->where('r_id', 1)->update(['is_reading' => 1]);
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
        $token = $request->session()->get('wxtoken');
        $create = [
            'token' => $token,
            'a_reply' => $data,
            'm_id' => $mid,
            'is_reading' => 0,
            'r_id' => 2,
        ];
        MessageList::create($create);
        $re = ['status' => true, 'message' => '发送成功'];
        return $re;
    }

}
