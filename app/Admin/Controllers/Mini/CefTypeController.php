<?php

namespace App\Admin\Controllers\Mini;

use App\Admin\Extensions\Tools\BackButton;
use App\Models\IdCard\Region;
use App\Models\Mini\RegistrationType;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class CefTypeController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('读者类型管理')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $request = \request();
        if ($request->filled('miniToken')) {
            $request->session()->put('gridWhere.miniToken', $request->input('miniToken'));
        }
        $miniToken = $request->session()->get('gridWhere.miniToken');
        if (empty($miniToken)) {
            admin_error('提示', '非法访问');
            return redirect()->back();
        }

        $grid = new Grid(new RegistrationType);

        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/miniProgram/certificate/config'), '授权列表'));
        });

        $grid->model()->where('token', $miniToken);
        $grid->id('编号');
//        $grid->token('Token');
        $grid->title('读者类型名称');
        $grid->value('读者类型编码');
//        $grid->is_pay('是否需要押金')->switch();
//        $grid->money('押金金额');
        $grid->prompt('提示')->limit(30);
        $grid->order('排序')->sortable();
//        $grid->min_age('最小年龄');
//        $grid->max_age('最大年龄');
        $grid->status('状态')->switch();

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(RegistrationType::findOrFail($id));

        $show->id('Id');
        $show->token('Token');
        $show->is_pay('Is pay');
        $show->money('Money');
        $show->title('Title');
        $show->value('Value');
        $show->created_at('Created at');
        $show->updated_at('Updated at');
        $show->min_age('Min age');
        $show->max_age('Max age');
        $show->prompt('Prompt');
        $show->is_check('Is check');
        $show->check_tip('Check tip');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RegistrationType);
        $request = request();
        if ($request->filled('miniToken')) {
            $request->session()->put('gridWhere.miniToken', $request->input('miniToken'));
        }
        $miniToken = $request->session()->get('gridWhere.miniToken');
        if (empty($miniToken)) {
            admin_error('提示', '非法访问');
            return redirect()->back();
        }

        $form->tab('基础配置', function ($form) {
            $form->switch('status', '状态');
            $form->text('title', '读者类型名称')->rules('required');
            $form->text('value', '读者类型编码')->rules('required');
            $form->radio('rdid_type', '生成证号方式')->options(['0' => '自动生成', '1' => '身份证号码'])->stacked()
                ->help('自动生成:请务必在Interlib后台设置好当前读者类型自动生成证号的规则!');

            $form->switch('check_repetition','是否查重')->default(1);

            $form->number('order','排序')->default(0);
            $form->textarea('prompt', '提示语');
//            $form->switch('is_check', '审核')->help('该项决定办证是否需要审核！');
//            $form->textarea('check_tip', '审核提示');
        });

        $form->tab('高级配置', function ($form) {
            $form->switch('is_pay', '押金')->help('是否需要缴纳押金');
            $form->currency('money', '押金金额')->default(0.00);
            $form->number('min_age', '最小年龄限制')->default(0);
            $form->number('max_age', '最大年龄限制')->default(0)->help('如不做年龄段限制的话，请填0');

            $states = [
                'on' => ['value' => 1, 'text' => '启用', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
            ];
            $form->checkbox('img_data', '收集相片类型')->options(config('addReaderImgOp'));

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

            $form->switch('send_code', '手机短信验证')->states($states)->help('启用短信验证务必配置好短信平台账号跟密码,且收集信息类型勾选手机！');

            $form->checkbox('data', '收集信息类型')->options(config('addReaderOp'))->stacked();

            $form->text('agreement_title', '协议名称')->placeholder('xxxxxx 微信线上实名办证须知协议');
            $form->editor('agreement', '协议内容');
        });

        $form->tab('密码限制',function ($form){
            $form->embeds('password_limit', '密码限制', function ($form) {
                $form->number('min_length', '最小长度')->default(0)->rules('required');
                $form->number('max_length', '最大长度')->help('填0为不限制,最大跟最小相等时即固定长度')->default(0)->rules('required');
                $options = [
                    1 => '不限制字符类型',
                    2 => '只允许数字',
                    3 => '只允许字母',
                    4 => '数字和字母混合',
                    5 => '数字或字母都可以',
                    6 => '必须数字字母特殊字符',
                ];
                $form->select('type', '类型')->options($options)->default(1);
                $form->text('prompt', '提示');
            });
        });

        $form->saving(function (Form $form) use ($miniToken) {
            if (request()->isMethod('post')) {
                $form->model()->token = $miniToken;
            }
        });

        $form->saved(function (Form $form) {
            Cache::forget('mini:register:' . $form->model()->token . ':c');
        });

        return $form;
    }
}
