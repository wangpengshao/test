<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\QrCodeList;
use App\Http\Controllers\Controller;
use App\Models\Wechat\QrTask;
use App\Models\Wxuser;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class QrPersonalController extends Controller
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
            ->header('个人二维码列表')
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
            ->header('Detail')
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
        $grid = new Grid(new QrCodeList);
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->expand();

            $filter->column(1 / 2, function ($filter) {
                $filter->equal('rdid', '读者证号');
                $filter->equal('type', '二维码类型')->select(['0' => '临时', '1' => '永久']);
                $filter->equal('t_id', '任务类型')->select(QrTask::where('token', session('wxtoken'))->pluck('title','id'));
            });

            $filter->column(1 / 2, function ($filter) {
                $filter->between('created_at', '创建时间')->datetime();
                $filter->between('expire_at', '过期时间')->datetime();

            });

        });

        $grid->model()->where('token', session('wxtoken'));
        $grid->model()->orderBy('id', 'desc');

        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
        });

//        $grid->id('编号');
        $grid->rdid('读者证号');
        $grid->column('hasOneTask.title', '任务类型 ');

        $grid->invites('邀请数')->sortable();
        $grid->views('扫码次数')->sortable();
        $grid->url('二维码链接')->urlWrapper();

        $grid->type('二维码类型')->using([
            '0' => "<span class='label label-danger'>临时</span>",
            '1' => "<span class='label label-success'>永久</span>",
        ]);

        $grid->expire_at('有效时间(临时)')->sortable();
        $grid->created_at('创建时间')->sortable();
        $grid->status('状态')->switch();
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
        $show = new Show(QrCodeList::findOrFail($id));

        $show->id('Id');
        $show->token('Token');
        $show->rdid('Rdid');
        $show->t_id('T id');
        $show->invites('Invites');
        $show->views('Views');
        $show->url('Url');
        $show->ticket('Ticket');
        $show->status('Status');
        $show->type('Type');
        $show->expire_at('Expire at');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new QrCodeList);

        $form->text('token', 'Token');
        $form->text('rdid', 'Rdid');
        $form->number('t_id', 'T id');
        $form->number('invites', 'Invites');
        $form->number('views', 'Views');
        $form->url('url', 'Url');
        $form->text('ticket', 'Ticket');
        $form->switch('status', 'Status')->default(1);
        $form->switch('type', 'Type');
        $form->datetime('expire_at', 'Expire at')->default(date('Y-m-d H:i:s'));

        return $form;
    }
}
