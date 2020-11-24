<?php

namespace App\Admin\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Models\WechatApi\GroupList;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class GroupController extends Controller
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

            $content->header('分组标签列表');
            $content->description('....');

            $content->body($this->grid());
        });
    }


    public function grid()
    {
        return Admin::grid(GroupList::class, function (Grid $grid) {

            $grid->actions(function ($actions) {
//                $actions->disableEdit();
                $actions->disableView();
//                $actions->disableDelete();
//                $actions->append('<a href=""><i class="fa fa-lock"></i></a>');
            });
            $grid->disableFilter();

            $grid->id();
            $grid->name('标签名')->badge('green');
            $grid->count('人数');
//
            $grid->disableExport();

        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('创建标签');
            $content->description('description');

            $content->body($this->form());
        });
    }


    /**
     * @return \Encore\Admin\Form
     */
    protected function form()
    {
        return Admin::form(GroupList::class, function (Form $form) {
            $form->tools(function (Form\Tools $tools) {
                $tools->disableDelete();
                $tools->disableView();
            });
            $form->display('id', 'ID');
            $form->text('name', '标签名');
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $content->header('编辑标签');
            $content->description('description');

            $content->body($this->form()->edit($id));
        });
    }


    public function destroy($id)
    {
        $ids = explode(',', $id);
        $status = GroupList::deleteIds($ids);
        if ($status) {
            return response()->json([
                'status'  => true,
                'message' => trans('admin.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.delete_failed'),
            ]);
        }
    }


}
