<?php

namespace App\Admin\Controllers\Wechat\Seat;

use App\Models\Seat\SeatAttr;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class SeatAttrController extends Controller
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
            ->header('座位属性')
            ->description('座位预约')
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
            ->description('座位预约')
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
            ->header('编辑属性')
            ->description('座位预约')
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
            ->header('添加属性')
            ->description('座位预约')
            ->body($this->form());
    }

    /**
     * Destroy interface.
     */
    public function destroy($ids)
    {
        SeatAttr::destroy(explode(',',$ids));
        DB::table('seat_chart_attr')->whereIn('attr_id',explode(',',$ids))->delete();
        return Response::json(['status'=>true, 'message'=>'删除成功 !']);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SeatAttr);
        $grid->model()->where('token', session('wxtoken'));

        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->like('name', '名称');
        });
        $grid->name('名称');
        $grid->color('图标颜色');

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
        $show = new Show(SeatAttr::findOrFail($id));

        $show->id('Id');
        $show->token('Token');
        $show->name('Name');
        $show->color('Color');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SeatAttr);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
        });
        $form->text('name', '名称');
        $form->color('color', '图标颜色');
        $form->hidden('token')->default(session('wxtoken'));
        return $form;
    }
}
