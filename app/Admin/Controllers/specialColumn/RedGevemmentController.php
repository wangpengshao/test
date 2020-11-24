<?php

namespace App\Admin\Controllers\specialColumn;

use App\Models\specialColumn\RedGevemment;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RedGevemmentController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '红色专题资源授权';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RedGevemment);
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
        });
        $grid->model()->orderBy('created_at');
        $grid->column('token', '授权token');
        $grid->column('name', '授权方名称');
        $grid->column('logo')->image('', '60');
        $grid->column('专题链接')->display(function (){
            return 'https://red-theme.dataesb.com/#/index/'.$this->token;
        })->urlWrapper();
        $grid->column('status', '状态')->switch();
        $grid->column('', '截止日期')->display(function () {
            return $this->date_start . ' ~ ' . $this->date_end;
        });
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
        $show = new Show(RedGevemment::findOrFail($id));
        $show->field('created_at', __('创建时间'));
        $show->field('updated_at', __('修改时间'));

        return $show;
    }

    /**s
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new RedGevemment);
        $form->display('token', 'Token');
        $form->image('logo', 'Logo')->move(materialUrl() . '/redResource')->uniqueName();
        $form->text('name', '授权方名称');
        $form->switch('status', '授权状态');
        $form->datetimeRange('date_start', 'date_end', '有效截止日期')->default(['start' => date('Y-m-d 00:00:00'), 'end' => date('Y-m-d 23:59:59')]);

        $form->saving(function (Form $form) {
            if (request()->isMethod('post')) {
                $form->model()->token = 'RES' . Str::uuid()->getNodeHex();
            }
        });
        $form->saved(function (Form $form) {
            if (request()->isMethod('put')) {
                Cache::forget('Resource.' . $form->model()->token . ':c');
            }
        });

        return $form;
    }
}
