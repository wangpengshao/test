<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\ArtCategories;
use App\Models\Wechat\Articles;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;

class ArticlesController extends Controller
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

            $content->header('文章管理');
            $content->description('description');

            $content->body($this->grid());
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

            $content->header('文章管理');
            $content->description('description');

            $doesntExist = Articles::where(['token' => session('wxtoken'), 'id' => $id])->doesntExist();
            if ($doesntExist) {
                return $content->withError('非法操作', '你没有权限编辑此内容..');
            }

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

            $content->header('文章管理');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Articles::class, function (Grid $grid) {

            $grid->model()->where('token', session('wxtoken'));

            $grid->id('ID')->sortable();
            $grid->column('hasOneCategories.title', '分类');

            $grid->title('标题');
            $grid->description('描述');
            $grid->picture('封面')->image('', 60, 60);

            $grid->column('order', '顺序')->editable()->sortable();;

            $states = [
                'on' => ['value' => 1, 'text' => '开启', 'color' => 'primary'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
            ];
            $grid->status('状态')->switch($states);

            $grid->created_at();

            $grid->actions(function ($actions) {
                $actions->disableView();
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
        return Admin::form(Articles::class, function (Form $form) {

            $form->tools(function (Form\Tools $tools) {
                $tools->disableView();
            });

            $form->display('id', 'ID');
            $form->select('category_id', '分类')->options(ArtCategories::getAll())->rules('required');;
            $form->text('title', '标题')->rules('required');
            $form->textarea('description', '描述');

            $states = [
                'on' => ['value' => 1, 'text' => '开启', 'color' => 'success'],
                'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
            ];
            $form->switch('status', '状态')->states($states);

            $form->switch('is_index', '首页热推')->states($states);
            $form->switch('is_province', '省共享')->states($states);
            $form->switch('is_city', '市共享')->states($states);
            $form->switch('is_district', '区共享')->states($states);

            /* 素材库 上传图片 例子 start */
            $form->image('picture', '封面')->move(materialUrl())->uniqueName();
            $form->hidden(config('materialPR') . 'picture');
            $imgArray = [config('materialPR') . 'picture'];
            $form->ignore($imgArray);
            /* 素材库 上传图片 例子 end */

            $form->editor('content', '内容');
            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
            $form->hidden('token')->default(session('wxtoken'));
            $form->hidden('order')->default(0);

            $form->saving(function (Form $form) use ($imgArray) {

                foreach ($imgArray as $k => $v) {
                    if (\request()->input($v)) {
                        $imgName = substr($v, strlen(config('materialPR')));
                        $form->model()->$imgName = \request()->input($v);
                    }
                }
                unset($k, $v);

            });
        });
    }
}
