<?php

namespace App\Admin\Controllers\Share;

use App\Models\Wechat\Articles;
use App\Models\Wechat\UserArticlesStore;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use App\Admin\Extensions\Tools\BackButton;

class StoreArticleStatusController extends Controller
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
            ->header('助力文章|')
            ->description('共享状态')
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(UserArticlesStore::class, function (Grid $grid) {
            $grid->model()->where('token', session('wxtoken'))->where('store_status', '1');
            $grid->article_id('文章编号');
            $grid->created_at('创建时间');
            $grid->actions(function ($actions) {
                //关闭行操作
                $actions->disableEdit();
                $actions->disableDelete();
            });
            $grid->disableFilter();
            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->disableColumnSelector();
            $grid->disableCreateButton();
        });
    }

    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('助力文章')
                    ->description('共享状态');
            $result = UserArticlesStore::where('id', $id)->first();
            $content->body($this->form()->edit($result['article_id']));
        });
    }

    protected function form()
    {
        $form = new Form(new Articles);
        $states = [
            'on' => ['value' => 1, 'text' => '开启', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
        ];
        $form->switch('is_province', '省共享')->states($states)->disable();
        $form->switch('is_city', '市共享')->states($states)->disable();
        $form->switch('is_district', '区共享')->states($states)->disable();
        $form->tools(function (Form\Tools $tools) {
            // 去掉`列表`按钮
            $tools->disableList();
            // 去掉`删除`按钮
            $tools->disableDelete();
            // 去掉`查看`按钮
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            // 去掉`重置`按钮
            $footer->disableReset();
            // 去掉`提交`按钮
            $footer->disableSubmit();
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();
            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        });
        $form->tools(function ($tools) {
            $url = "/admin/share/storearticlestatus";
            $tools->append(new BackButton($url));
        });
        return $form;
    }
}

