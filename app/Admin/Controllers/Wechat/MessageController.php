<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\Fanscontent;

use Carbon\Carbon;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;

class MessageController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('粉丝消息列表');
            $content->description('description');

            $content->body($this->grid());
        });
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $redis = Redis::connection();
        $token = \request()->session()->get('wxtoken');

        $grid = new Grid(new Fanscontent);
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->equal('openid', 'openid');
        });
        $grid->disableExport();

        $grid->model()->where('token', '=', $token)->orderBy('created_at', 'DESC');

        $grid->column('hasOneWechatinfo.headimgurl', '头像')->display(function ($value) {
            return "<img class='img-circle' style='width: 48px;height: 48px;' src='{$value}' alt='User Avatar'>";
        });

        $grid->column('微信昵称')->display(function () {
            $nickname = $this->hasOneWechatinfo->nickname;
            $subscribe = (int)$this->hasOneWechatinfo->subscribe;
            switch ($subscribe) {
                case 0:
                    $text = "<span class='badge '>未关注</span>";
                    break;
                case 1:
                    $text = "<span  class='badge  bg-green'>关注中</span>";
                    break;
                case 2:
                    $text = "<span class='badge bg-blue'>页面访问</span>";
                    break;
                default:
                    $text = "<span class='badge bg-yellow'>未知</span>";
            }
            return $nickname . $text;
        })->style('max-width:150px;word-break:break-all;');

        $grid->openid()->badge('danger');

        $grid->type('消息类型')->using([
            '0' => '<span class="badge ">文字</span>',
            '1' => '<span class="badge bg-blue">语音</span>',
            '2' => '<span class="badge bg-green">图片</span>',
            '3' => '<span class="badge bg-orange">视频</span>',
        ]);

        $grid->column('content', '内容')->display(function ($content) {
            $type = (int)$this->type;
            if ($type === 0 || $type === 1) {
                return Str::limit($content, 60);
            }
        })->style('max-width:150px;word-break:break-all;');

        $grid->created_at('留言时间')->display(function ($time) {
            return Carbon::parse($time)->diffForHumans();
        });

        $grid->column('消息中心')->display(function () use ($redis) {
            $routerURL = route('chatroom.index', $this->openid);
            return ($redis->zscore('fans:' . $this->token, $this->openid)) ?
                '<a title="发送消息" data-toggle="tooltip" href="' . $routerURL . '"><i class="fa fa-commenting"></i></a>' :
                '<a title="查看消息" data-toggle="tooltip" href="' . $routerURL . '"><i class="fa fa-list-alt text-success"></i></a>';
        });
        return $grid;
    }

    public function del(Request $request)
    {
        $idArr = explode(',', $request->route('id'));
        $flight = Fanscontent::find($idArr);
        $flight->each(function ($model) {
            $model->delete();
        });
        $re = ['status' => true, 'message' => '删除成功！'];
        return $re;
    }


}
