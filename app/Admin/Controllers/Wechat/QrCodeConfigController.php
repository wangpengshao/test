<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\QrCodeConfig;
use App\Models\Wechat\QrTask;
use App\Models\Wxuser;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Routing\Controller;

class QrCodeConfigController extends Controller
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
            $content->header('推广二维码');
            $content->description('编辑');

            $type = Wxuser::whereToken(session('wxtoken'))->value('type');
            if ($type != 1) {
                return $content->withWarning('提示', '抱歉，此功能需要公众号类型为服务号才能使用..');
            }

            $content->row(function (Row $row) {

                $row->column(8, function (Column $column) {

                    $data = QrCodeConfig::whereToken(session('wxtoken'))->first();

                    $form = new \Encore\Admin\Widgets\Form($data);
                    $url = ($data) ? route('wechat.qrCode.config.up', $data->id) : route('wechat.qrCode.config.add');

                    $form->action($url);
                    $states = [
                        'on' => ['value' => 1, 'text' => '启用', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '禁用', 'color' => 'danger'],
                    ];
                    $form->radio('type', '类型')->options(['0' => '临时', '1' => '永久'])
                        ->help('由于官方规定，每个公众号永久的二维码的个数为10万个,请谨慎选择!');

                    $form->number('days', '临时天数')->max(30)->min(1)->default(1)->help('官方规定临时二维码最长维持30天');

                    $form->text('keyword', '扫码关键字')->help('不填的话为默认回复');
                    $form->switch('status', '状态')->states($states);

                    $form->select('t_id', '关联任务')->options( QrTask::where('token',session('wxtoken'))
                        ->pluck('title','id')->prepend('暂无',0));

                    $form->hidden('id');
                    $form->hidden('token')->default(session('wxtoken'));
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
        return Admin::form(QrCodeConfig::class, function (Form $form) {
            $form->hidden('id');
            $form->radio('type');
            $form->hidden('token');
            $form->switch('status');
            $form->hidden('days');
            $form->hidden('keyword');
            $form->select('t_id');

        });
    }


}
