<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\TransactType;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class TransactTypeController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('类型列表')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('详情')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
     * @return Content
     */
    public function edit($id, Content $content)
    {
        $exists = TransactType::where(['token' => session('wxtoken'), 'id' => $id])->exists();
        if ($exists == false) {
            return $content->withError('非法访问,请返回!');
        }
        return $content
            ->header('编辑')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('新建类型')
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
        $grid = new Grid(new TransactType);
        $grid->disableFilter();
        $grid->model()->where('token', session('wxtoken'));
        $grid->title('读者类型名称');
        $grid->value('读者类型编码');
        $grid->is_pay('是否需要押金')->switch();
        $grid->money('押金金额');
        $grid->min_age('最小年龄');
        $grid->max_age('最大年龄');
        $grid->column('order', '排序')->sortable()->help('数字越大越靠前');
        $grid->prompt('提示')->limit(30);
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(TransactType::findOrFail($id));
        $show->id('Id');
//        $show->type('Type');
//        $show->money('Money');
//        $show->token('Token');
//        $show->title('Title');
//        $show->value('Value');
        $show->created_at('创建时间');
        $show->updated_at('最后编辑时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TransactType);
        $form->text('title', '读者类型名称')->rules('required');
        $form->text('value', '读者类型编码')->rules('required');
        $form->number('order', '排序')->default(0)->help('数字越大越靠前');
        $form->textarea('prompt', '提示');
        $form->switch('is_pay', '押金')->help('是否需要缴纳押金');
        $form->currency('money', '金额')->default(0.00);
        $form->number('min_age', '最小年龄限制')->default(0);
        $form->number('max_age', '最大年龄限制')->default(0)->help('如不做年龄段限制的话，请填0');
        $form->hidden('token')->default(session('wxtoken'));
        $form->switch('is_check', '审核')->help('该项决定办证是否需要审核,实名办证为免审,实名办证可忽略该选项！');
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
        return $form;
    }
}
