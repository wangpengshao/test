<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\QrTask;
use App\Http\Controllers\Controller;
use App\Models\Wxuser;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class QrTaskController extends Controller
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
        $type = Wxuser::whereToken(session('wxtoken'))->value('type');
        if ($type != 1) {
            return $content->withWarning('提示', '抱歉，此功能需要公众号类型为服务号才能使用..');
        }
        return $content
            ->header('任务管理')
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
            ->header('任务管理')
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
        return $content
            ->header('Edit')
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
        $grid = new Grid(new QrTask);

        $grid->filter(function ($filter) {
            $filter->expand();
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('title', '标题');
//            $filter->in('r_id','任务')->multipleSelect(  QrTask::where(
//                ['token' => session('wxtoken'), 'status' => 1]
//            )->pluck('title','id')->prepend('空',0));
        });

        $grid->model()->where('token', session('wxtoken'));
        $grid->model()->orderBy('id', 'desc');
        $grid->title('任务标题');
//        $grid->status('状态')->switch();
//        $grid->is_bind('绑定读者')->switch();
        $grid->is_inform('通知')->switch();

        $grid->integral('奖励积分');

        $grid->number('达标数量');

        $grid->k_days('保持天数');
        $grid->s_time('开始时间');
        $grid->e_time('结束时间');
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
        $show = new Show(QrTask::findOrFail($id));

//        $show->id('Id');
        $show->title('任务标题');
//        $show->token('Token');
//        $show->status('Status');
//        $show->is_bind('Is bind');
//        $show->integral('Integral');
//        $show->k_days('K days');
//        $show->s_time('开始时间');
//        $show->e_time('结束时间');
//        $show->is_inform('Is inform');
        $show->updated_at('最后编辑');
        $show->created_at('创建时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new QrTask);
        $form->hidden('token')->default(session('wxtoken'));
        $form->text('title', '标题')->rules('required')->placeholder('必填');
        $form->textarea('describe', '描述');
        $form->text('keyword', '关键字')->help('不填的话以推广设置里的关键字为准');
        $form->number('integral', '积分')->help('邀请成功一个奖励积分!');
        $form->number('number', '达标邀请数');
        $form->number('d_integral', '达标奖励积分')->help('达标完成一次性奖励积分!');
        $form->number('k_days', '天数')->help('扫码关注之后需保持关注状态指定的天数才算有效成绩');
        $form->dateTimeRange('s_time', 'e_time', '任务时间')->rules('required')->placeholder('必填');

        $form->switch('is_inform', '是否提示')->help('粉丝扫码成功，是否提醒二维码所属的用户');

        $form->templateData('te1_id', '模版ID(邀请成功)')->setJsonColumn('te1_da')->help('微信后台申请的模版消息ID')->placeholder('必填');
        $form->embeds('te1_da', '内容:', function ($form) {
        });

        $form->templateData('te2_id', '模版ID(达标提示)')->setJsonColumn('te2_da')->help('微信后台申请的模版消息ID')->placeholder('必填');
        $form->embeds('te2_da', '内容:', function ($form) {
        });
        return $form;
    }


    public function update($id)
    {
        return $this->form()->update($id);
    }

}
