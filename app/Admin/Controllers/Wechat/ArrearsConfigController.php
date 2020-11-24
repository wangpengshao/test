<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\ArrearsConfig;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Routing\Controller;

class ArrearsConfigController extends Controller
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
            $content->header('欠款支付基本设置');
            $content->description('编辑');
            $content->row(function (Row $row) {

                $row->column(8, function (Column $column) {
                    $data = ArrearsConfig::where('token',\request()->session()->get('wxtoken'))->first();
                    $form = new \Encore\Admin\Widgets\Form($data);
                    $url = ($data) ? route('wechat.arrears.up', $data->id) : route('wechat.arrears.add');
                    $form->action($url);
                    $states = [
                        'on' => ['value' => 1, 'text' => '启用', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
                    ];
                    $form->switch('pay_sw', '功能开关')->states($states);
                    $form->switch('df_sw', '代付开关')->states($states);
                    //$form->radio('payment_type', '支付选择')->options([0=>'公众号支付', 1=>'工行聚合支付'])->stacked();
                    $form->hidden('id');
                    $form->hidden('token')->default(\request()->session()->get('wxtoken'));
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
        return Admin::form(ArrearsConfig::class, function (Form $form) {
            $form->hidden('id');
            $form->hidden('token');
            $form->switch('pay_sw', '功能开关');
            $form->switch('df_sw', '代付开关');
            $form->switch('payment_type', '支付选择');
            $form->saved(function () {
                return back()->with(admin_toastr('保存成功!', 'success'));
            });

        });
    }



}
