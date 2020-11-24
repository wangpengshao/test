<?php

namespace App\Admin\Controllers\Wechat;

use App\Admin\Extensions\ExcelExporter\ReaderExporter;
use App\Models\Wechat\Reader;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;

class ReaderController extends Controller
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

            $content->header('绑定读者列表');
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

        $grid = new Grid(new Reader());

        $grid->exporter(new ReaderExporter());
        $grid->disableCreateButton();
        $grid->disableExport(false);
        $grid->expandFilter();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->group('rdid', '读者证', function ($group) {
                    $group->equal('等于');
                    $group->like('包含');
                });
                $filter->equal('name', '姓名');
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('openid', 'openid');
                $filter->between('updated_at', '绑定时间')->datetime();
            });
        });

        $grid->model()->where('token', $token);
        $grid->model()->where('is_bind', 1);

        $grid->column('rdid', '证号');
        $grid->column('name', '真实姓名');

        $grid->column('微信昵称')->display(function () {
            $fansInfo = $this->hasOneWechatinfo;
            if ($fansInfo) {
                $nickname = $fansInfo->nickname;
                $subscribe = (int)$fansInfo->subscribe;
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
            }
            return '';
        })->style('max-width:150px;word-break:break-all;');

        $grid->column('hasOneWechatinfo.headimgurl', '头像')->image('', 48, 48);
        $grid->column('hasOneWechatinfo.sex', '性别')->using(['2' => '女', '1' => '男']);

        $grid->openid()->badge('danger');

        $grid->updated_at('绑定时间');

        $grid->column('消息中心')->display(function () use ($redis) {
            $routerURL = route('chatroom.index', $this->openid);
            return ($redis->zscore('fans:' . $this->token, $this->openid)) ?
                '<a title="发送消息" data-toggle="tooltip" href="' . $routerURL . '"><i class="fa fa-commenting"></i></a>' :
                '<a title="查看消息" data-toggle="tooltip" href="' . $routerURL . '"><i class="fa fa-list-alt text-success"></i></a>';
        });
        return $grid;
    }

    protected function form()
    {
        $form = new Form(new Reader);
        $form->model()->where('token', session('wxtoken'));
        $form->hidden('id');
        return $form;
    }


}
