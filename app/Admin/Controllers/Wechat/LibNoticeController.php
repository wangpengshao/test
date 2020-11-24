<?php

namespace App\Admin\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Models\LibNotice;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class LibNoticeController extends Controller
{
    use HasResourceActions;

    protected $status = [
        'on' => ['value' => 1, 'text' => '显示', 'color' => 'success'],
        'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'default'],
    ];

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
            ->header('公告列表')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new LibNotice);
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->like('title', '公告标题');
        });
        $grid->column('title', '公告标题');
        $grid->status('显示状态')->switch($this->status);
        $grid->column('content', '公告内容');
        $grid->column('created_at', '创建时间')->sortable();
        $grid->column('updated_at', '更新时间')->sortable();
        $grid->actions(function ($actions) {
        });
        return $grid;
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
            ->header('发布公告')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
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
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     *
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
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(LibNotice::findOrFail($id));
        $show->id('Id');
        $show->title('公告名称');
        $show->content('公告内容');
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
        $form = new Form(new LibNotice);
        $form->text('title', '名称')->rules('required');
        $form->switch('status', '显示状态')->states($this->status)->default(1);
        $form->editor('content', '公告内容');
        $form->file('file_path', '附件');
        $form->datetimeRange('start_at', 'end_at', '可显示时间');
        $form->hidden('token')->default(session('wxtoken'));

        return $form;
    }

}