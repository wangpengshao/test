<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\IdCard\Region;
use App\Models\Wechat\Certificate;
use App\Models\Wechat\TransactType;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class TransactCardLv1Controller extends Controller
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
            $content->header('普通办证配置');
            $content->description('编辑');
            $content->row(function (Row $row) {

                $row->column(8, function (Column $column) {
                    $token = request()->session()->get('wxtoken');
                    $data = Certificate::where(['token' => $token, 'type' => 0])->first();

                    $form = new \Encore\Admin\Widgets\Form($data);
                    $url = ($data) ? route('wechat.certificateLv1.up', $data->id) : route('wechat.certificateLv1.add');
                    $form->action($url);

                    $states = [
                        'on' => ['value' => 1, 'text' => '启用', 'color' => 'success'],
                        'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
                    ];
                    $form->switch('status', '状态')->states($states);

                    $op = TransactType::where('token', $token)->pluck('title', 'id');
                    if (count($op) == 0) {
                        return admin_warning('提示', '请先前往办证类型添加数据才能实名办证设置！');
                    }
                    $form->checkbox('options', '读者类型')->options($op)->stacked();

                    $form->radio('rdid_type', '生成证号方式')
                        ->options(['0' => '自动生成', '1' => '身份证号码(作证号)'])
                        ->stacked()
                        ->help('自动生成:请务必在Interlib后台设置好当前读者类型自动生成证号的规则!');

                    $form->checkbox('imgData', '收集相片类型')
                        ->options(config('addReaderImgOp'))->help('如需上传单张图片至业务系统，需勾选个人照，需新版openlib支持!');

                    $form->multipleSelect('region', '身份证归属地')->options(function ($code) {
                        if (is_array($code) && count($code) > 0) {
                            return Region::whereIn('code', $code)->pluck('name', 'code');
                        }
                    })->ajax('/admin/wechat/certificateLog/api/getIdCardRegion')->placeholder('可输入地方名或者身份证前6位')
                        ->help('身份证号归属地限制,可多选,如不限制请留空!');

                    $form->multipleSelect('phone_region', '手机号码归属地')->options(function ($code) {
                        if (is_array($code) && count($code) > 0) {
                            return Region::whereIn('code', $code)->pluck('name', 'code');
                        }
                    })->ajax('/admin/wechat/certificateLog/api/getIdCardRegion?type=province')->placeholder('可输入地方名')
                        ->help('手机号码归属地限制,可多选,如不限制请留空！');

                    $form->switch('sendCode', '手机短信验证')->states($states)->help('启用短信验证务必配置好短信平台账号跟密码,且收集信息类型勾选手机！');

                    $form->checkbox('data', '收集信息类型')->options(config('addReaderOp'))->stacked();

                    $form->text('agreementTitle', '协议名称')->placeholder('xxxxxx 微信线上实名办证须知协议');
                    $form->editor('agreement', '协议内容');

                    $form->hidden('id');
                    $form->hidden('type')->default(0);
                    $form->hidden('token')->default($token);
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
        return Admin::form(Certificate::class, function (Form $form) {
            $form->switch('status');
            $form->checkbox('options');
            $form->checkbox('data');
            $form->checkbox('imgData');

            $form->hidden('id');
            $form->hidden('type');
            $form->hidden('token');
            $form->radio('rdid_type');
            $form->text('agreementTitle');
            $form->editor('agreement');
            $form->multipleSelect('region');
            $form->multipleSelect('phone_region');
            $form->switch('sendCode');
            $form->saved(function () {
                return back()->with(admin_toastr('保存成功!', 'success'));
            });

        });
    }

    public function getIdCardRegion(Request $request)
    {
        $q = $request->get('q');
        $type = $request->get('type');

        $model = Region::when(is_numeric($q), function ($model) use ($q) {
            return $model->where('code', 'like', "%$q%");
        }, function ($model) use ($q) {
            return $model->where('name', 'like', "%$q%");
        });
        if ($type == 'province') {
            $model->where('parent_id', '<', 36);
        }
        return $model->paginate(null, ['code as id', 'name as text']);
    }


}
