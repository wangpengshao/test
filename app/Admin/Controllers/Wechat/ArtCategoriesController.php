<?php

namespace App\Admin\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Models\Wechat\ArtCategories;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;

class ArtCategoriesController extends Controller
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
            $content->header('分类列表');
            $content->description('...');
            $content->row(function (Row $row) {
                $row->column(8, $this->tree()->render());
            });
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
            $content->header('编辑分类');
            $content->description('...');
            $content->body($this->form()->edit($id));
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
            $content->header('分类');
            $content->description('...');
            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return tree
     */
    protected function tree()
    {
        return ArtCategories::tree(function (Tree $tree) {
            $tree->query(function ($model) {
                return $model->where('token', session('wxtoken'));
            });
            $tree->branch(function ($branch) {
                $status = ($branch['status']) ?
                    "<i class='fa  fa-circle' style='color:#3c8dbc'></i>" :
                    "<i class='fa  fa-circle' style='color:#d1d5d4'></i>";
                return "-  {$branch['title']} &nbsp;&nbsp;&nbsp;&nbsp;" . $status;
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return ArtCategories::form(function (Form $form) {

            $form->tools(function (Form\Tools $tools) {
                $tools->disableView();
                $tools->disableDelete();
            });

            $form->display('id', 'ID');
            $form->select('parent_id', '父级')->options(ArtCategories::getparent());

            $form->text('title', '菜单名称')->rules('required');

            $form->number('order','排序');
            $form->text('desc','描述');

            $states = [
                'on' => ['value' => 1, 'text' => '显示', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
            ];
            $form->switch('status', '状态')->states($states);

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');

            $form->saving(function (Form $form) {
                $model = $form->model();
                if (empty($model->token)) {
                    $model->token = session('wxtoken');
                }
            });
        });
    }


}
