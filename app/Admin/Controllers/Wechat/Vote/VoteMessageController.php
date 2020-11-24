<?php

namespace App\Admin\Controllers\Wechat\Vote;

use App\Admin\Extensions\Tools\BackButton;
use App\Models\Vote\VoteMessage;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class VoteMessageController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '留言';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $g_id = request()->input('g_id');
        $t_id = request()->input('t_id');
        $grid = new Grid(new VoteMessage);
        $where['g_id'] = $g_id;
        if($t_id){
            $where['t_id'] = $t_id;
        }
        $grid->model()->where($where)->with('fans')->orderBy('create_at','desc');
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->equal('t_id', '作品ID');
        });
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/wechat/vote/group'), '返回分组'));
        });
        $grid->column('t_id', __('作品ID'));
        $grid->column('nickname', __('昵称'))->display(function (){
            return $this->fans->nickname;
        });
        $grid->column('headimgurl', __('头像'))->display(function (){
            return $this->fans->headimgurl;
        })->image('', '45');
        $grid->column('openid', __('Openid'));
        $grid->column('content', __('留言'));
        $grid->column('status', __('审核状态'))->switch();
        $grid->column('create_at', __('发布时间'));

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
        $show = new Show(VoteMessage::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('a_id', __('A id'));
        $show->field('g_id', __('G id'));
        $show->field('t_id', __('T id'));
        $show->field('f_id', __('F id'));
        $show->field('openid', __('Openid'));
        $show->field('content', __('Content'));
        $show->field('status', __('Status'));
        $show->field('create_at', __('Create at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new VoteMessage);

        $form->number('a_id', __('A id'));
        $form->number('g_id', __('G id'));
        $form->number('t_id', __('T id'));
        $form->number('f_id', __('F id'));
        $form->text('openid', __('Openid'));
        $form->text('content', __('Content'));
        $form->switch('status', __('Status'));

        return $form;
    }
}
