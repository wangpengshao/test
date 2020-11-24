<?php

namespace App\Admin\Controllers\Wechat\CollectCard;

use App\Models\CollectCard\SelfService;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class SelfServiceController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('自助机管理')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
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
     * @param mixed $id
     * @param Content $content
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
        $grid = new Grid(new SelfService);
        $grid->model()->where('token', session('wxtoken'));
        $grid->id('编号');
        $grid->name('机器名称');
        $grid->region('区域名称');
//        $grid->token('Token');
        $grid->lat('纬度');
        $grid->lng('经度');
//        $grid->created_at('Created at');
        $grid->status('状态')->switch();
        $grid->disableExport();
        $grid->disableFilter();
        $grid->actions(function ($actions) {
            $actions->disableView();
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
        $show = new Show(SelfService::findOrFail($id));

        $show->id('Id');
        $show->token('Token');
        $show->lat('Lat');
        $show->lng('Lng');
        $show->created_at('Created at');
        $show->status('Status');
        $show->name('Name');
        $show->region('Region');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SelfService);

//        $form->text('token', 'Token');
        $form->switch('status', '状态');
        $form->text('name', '机器名称');
        $form->text('region', '区域名称');

        $form->decimal('lat', '纬度')->default(0.000000);
        $form->decimal('lng', '经度')->default(0.000000);

        $form->hidden('token')->default(session('wxtoken'));
        return $form;
    }
}
