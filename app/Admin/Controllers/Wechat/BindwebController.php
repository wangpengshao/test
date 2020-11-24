<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\Bindweb;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class BindwebController extends Controller
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
            $content->header('绑定界面配置');
            $content->description('编辑');
            $content->row(function (Row $row) {

                $row->column(10, function (Column $column) {
                    $token = \request()->session()->get('wxtoken');
                    $data = Bindweb::whereToken($token)->first();
                    $form = new \Encore\Admin\Widgets\Form($data);
                    $url = ($data) ? route('wechat.webconfig.bind.up', $data->id) : route('wechat.webconfig.bind.add');
                    $form->action($url);
                    $states = [
                        'on' => ['value' => 1, 'text' => '启用', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '默认', 'color' => 'danger'],
                    ];
                    $form->switch('status', '状态')->states($states);
                    $form->text('title', '标题');
                    $form->text('uname', '账号名称');
                    $form->text('uremark', '账号备注');
                    $form->text('pname', '密码名称');
                    $form->text('premark', '密码备注');
                    $form->editor('content', '提示语');
                    //拓展
//                    $form->divider('以下内容不受状态控制影响');
                    $form->text('l_title', '左超链文本');
                    $form->url('l_link', '左超链链接');
                    $form->text('r_title', '右超链文本');
                    $form->url('r_link', '右超链文本');

                    $form->hidden('id');
                    $form->hidden('token')->default($token);
                    $form->hidden('user_id')->default(Admin::user()->id);
                    $column->append((new Box(' ', $form))->style('success'));

                });
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        return Admin::form(Bindweb::class, function (Form $form) {
            $form->switch('status');
            $form->editor('content')->rules(function () {
                if (request()->input('status') == 'on') {
                    return 'required';
                }
            }, ['required' => '不能为空']);

            $form->text('title')->rules(function () {
                if (request()->input('status') == 'on') {
                    return 'required';
                }
            }, ['required' => '不能为空']);
            $form->text('uname')->rules(function () {
                if (request()->input('status') == 'on') {
                    return 'required';
                }
            }, ['required' => '不能为空']);
            $form->text('uremark')->rules(function () {
                if (request()->input('status') == 'on') {
                    return 'required';
                }
            }, ['required' => '不能为空']);
            $form->text('pname')->rules(function () {
                if (request()->input('status') == 'on') {
                    return 'required';
                }
            }, ['required' => '不能为空']);
            $form->text('premark')->rules(function () {
                if (request()->input('status') == 'on') {
                    return 'required';
                }
            }, ['required' => '不能为空']);

            $form->text('l_title', '左超链文本');
            $form->url('l_link', '左超链链接');
            $form->text('r_title', '右超链文本');
            $form->url('r_link', '右超链文本');

            $form->hidden('id');
            $form->hidden('token');
            $form->hidden('user_id');

            $form->saved(function (Form $form) {
                $token = $form->model()->token;
                $cacheKey = sprintf(config('cacheKey.vueBindConf'), $token);
                Cache::forget($cacheKey);
            });


        });
    }


}
