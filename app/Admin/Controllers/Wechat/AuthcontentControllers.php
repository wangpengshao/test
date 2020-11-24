<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\Authcontent;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Routing\Controller;

class AuthcontentControllers extends Controller
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
            $content->header('回答不上来自动回复');
            $content->description('编辑');
            $content->row(function (Row $row) {

                $row->column(8, function (Column $column) {

                    $data = Authcontent::whereToken(session('wxtoken'))->first();

                    $form = new \Encore\Admin\Widgets\Form($data);
                    $url = ($data) ? route('wechat.passiveReply.auth.up', $data->id) : route('wechat.passiveReply.auth.add');
                    $form->action($url);
                    $states = [
                        'on' => ['value' => 1, 'text' => '使用', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
                    ];
                    $form->textarea('content', '内容')->rules('required');
                    $form->switch('status', '开关')->states($states);
//                    $form->text('keyword', '关键字');
                    $form->hidden('id');
                    $form->hidden('token')->default(session('wxtoken'));
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
        return Admin::form(Authcontent::class, function (Form $form) {
            $form->textarea('content')->rules(function () {
                if (request()->input('status') == 'on') {
                    return 'required';
                }
            }, ['required' => '不能为空']);
            $form->hidden('id');
            $form->hidden('token');
            $form->hidden('user_id');
            $form->switch('status');

//            $form->saved(function (){
//                Cache::forget('Subscribe_' . session('wxtoken'));
//            });
        });
    }


}
