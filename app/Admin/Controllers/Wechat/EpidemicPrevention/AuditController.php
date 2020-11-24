<?php

namespace App\Admin\Controllers\Wechat\EpidemicPrevention;

use App\Admin\Extensions\ExcelExporter\DepositExporter;
use App\Models\Wechat\SafeguardComments;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class AuditController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '阅读战"疫"';
    protected $description = [
        'index' => '评论列表',
//        'show'   => 'Show',
//        'edit'   => 'Edit',
//        'create' => 'Create',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SafeguardComments);
        $grid->disableCreateButton();
        $grid->disableActions();
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('token', 'token');
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('status', '状态')->radio([
                    '' => '全部',
                    0 => '未审核(隐藏)',
                    1 => '已审核(显示)',
                ]);
            });
        });
        $grid->model()->orderBy('created_at', 'desc');
        $grid->column('token', __('Token'));
        $grid->column('nickname', '微信昵称');
//        $grid->column('openid', __('Openid'));
        $grid->column('headimgurl', '微信头像')->image('', 50, 50);
        $grid->column('content', '内容');
        $grid->column('like_n', '点赞');
        $switch = [
            'on' => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'default'],
        ];
        $grid->column('status', '状态')->switch($switch)->sortable();
        $grid->column('created_at', '评论时间')->sortable();

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
        $show = new Show(SafeguardComments::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('token', __('Token'));
//        $show->field('nickname', __('Nickname'));
        $show->field('openid', __('Openid'));
//        $show->field('headimgurl', __('Headimgurl'));
//        $show->field('created_at', __('Created at'));
//        $show->field('updated_at', __('Updated at'));
//        $show->field('content', __('Content'));
//        $show->field('like_n', __('Like n'));
//        $show->field('status', __('Status'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SafeguardComments);

        $form->text('token', __('Token'));
        $form->text('nickname', __('Nickname'));
        $form->text('openid', __('Openid'));
        $form->text('headimgurl', __('Headimgurl'));
        $form->text('content', __('Content'));
        $form->number('like_n', __('Like n'));
        $form->switch('status', __('Status'));

        return $form;
    }
}
