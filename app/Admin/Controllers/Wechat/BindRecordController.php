<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\BindLog;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;

class BindRecordController extends Controller
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

            $content->header('绑定记录');
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
        $token = \request()->session()->get('wxtoken');
        $grid = new Grid(new BindLog());
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->group('rdid', '读者证', function ($group) {
                    $group->equal('等于');
                    $group->like('包含');
                });
                $filter->equal('openid', 'openid');
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->between('created_at', '操作时间')->datetime();
            });
        });

        $grid->model()->where('token', $token)->with('fansInfo')->orderBy('id', 'desc');
        $grid->column('rdid', '证号');
        $grid->column('fansInfo.nickname', '微信昵称');
        $grid->column('fansInfo.headimgurl', '头像')->image('', 48, 48);

        $grid->openid()->badge('danger');

        $grid->column('type', '绑定状态')->display(function ($type) {
            switch ($type) {
                case 0:
                    $text = "<span class='badge '>解绑</span>";
                    break;
                case 1:
                    $text = "<span  class='badge  bg-green'>绑定</span>";
                    break;
                default:
                    $text = "<span class='badge bg-yellow'>未知</span>";
            }
            return $text;
        });

        $grid->created_at('操作时间');

        return $grid;
    }

}

