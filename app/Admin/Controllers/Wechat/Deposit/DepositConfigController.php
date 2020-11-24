<?php

namespace App\Admin\Controllers\Wechat\Deposit;

use App\Models\Deposit\Deposit;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use App\Http\Controllers\Controller;

class DepositConfigController extends Controller
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
            $content->header('押金退还');
            $content->description('配置');
            $content->row(function (Row $row) {

                $row->column(20, function (Column $column) {

                    $data = Deposit::whereToken(session('wxtoken'))->first();
                    $form = new \Encore\Admin\Widgets\Form($data);
                    $url = ($data) ? route('deposit.Config.up', $data->id) : route('deposit.Config');
                    $form->action($url);
                    $form->text('total_money', '退款总额')->help('每日准备的押金总额！');
                    $form->text('before_time', '提前天数');
                    $form->text('deposit_grade', '押金档次')->help('可设置为50或100！');
                    $form->text('holiday', '节假日')->help('闭馆日期，默认为空,如有多天闭馆请用逗号隔开，如05-01，05-02');
                    $form->text('block', '时段:(min)');
                    $form->text('notice', '公告');
                    $states = [
                        'on' => ['value' => 1, 'text' => '使用', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
                    ];
                    $form->switch('status', '系统状态')->states($states);
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
        return Admin::form(Deposit::class, function (Form $form) {
            $form->switch('status');
            $form->textarea('total_money')->rules(function () {
                if (request()->input('status') == 'on') {
                    return 'required';
                }
            }, ['required' => '不能为空']);
            $form->text('keyword')->rules(function () {
                if (request()->input('status') == 'on') {
                    return 'required';
                }
            });
            $form->text('holiday');
            $form->text('notice')->rules(function () {
                if (request()->input('status') == 'on') {
                    return 'required';
                }
            }, ['required' => '不能为空']);
        });
    }


}
