<?php

namespace App\Admin\Controllers\Wechat\Seat;

use App\Models\Seat\SeatByBooking;
use App\Models\Seat\SeatCurrBooking;
use App\Models\Seat\seatByScan;
use App\Models\Seat\SeatRegion;
use App\Models\Seat\SeatUser;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class SeatUserController extends Controller
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
            ->header('用户中心')
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
            ->header('用户编辑')
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
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * 入座记录
     */
    public function seatedLog(Content $content,Request $request)
    {
        return $content
            ->header('用户入座记录')
            ->description('座位预约')
            ->body($this->grid2($request->rdid));
    }

    /**
     * 当前预约记录
     */
    public function bookingCurr(Content $content,Request $request)
    {
        return $content
            ->header('用户当前预约记录')
            ->description('座位预约')
            ->body($this->grid4($request->rdid));
    }

    /**
     * 预约历史记录
     */
    public function bookingLog(Content $content,Request $request)
    {
        return $content
            ->header('用户预约记录')
            ->description('座位预约')
            ->body($this->grid3($request->rdid));
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SeatUser);
        $grid->model()->with('fans')->where('token',session('wxtoken'));

        $grid->tools(function($tools){
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

        });
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->filter(function($filter){
            $filter->disableIdFilter();
            $filter->equal('status', '用户状态')->select(['1'=>'正常', '2'=>'黑名单']);
            $filter->equal('rdid', '读者证号');
        });
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
            $seatedLogUrl = route('seat.seatUser.seatedLog', ['rdid'=>$actions->row->rdid]);
            $bookingLog = route('seat.seatUser.bookingLog', ['rdid'=>$actions->row->rdid]);
            $bookingCurr = route('seat.seatUser.bookingCurr', ['rdid'=>$actions->row->rdid]);
            $actions->prepend('<a href="'. $bookingLog .'" title="预约历史记录" class="btn btn-xs btn-default"><i class="fa fa-th-large"></i></a>');
            $actions->prepend('<a href="'. $bookingCurr .'" title="当前预约记录" class="btn btn-xs btn-default"><i class="fa fa-shield"></i></a>');
            $actions->prepend('<a href="'. $seatedLogUrl .'" title="入座历史记录" class="btn btn-xs btn-default"><i class="fa fa-envira"></i></a>');
        });
        $grid->column('rdid', '读者证号');
        $grid->column('violations', '违规次数');
        $grid->column('last_date', '最近登录时间');
        $grid->column('status', '状态')->using([
            '1' => '<span class="badge bg-green">正常</span>',
            '0' => '<span class="badge bg-red">黑名单</span>',
        ]);
        return $grid;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid2($id)
    {
        $grid = new Grid(new seatByScan());
        $grid->model()->where(['token'=>session('wxtoken'), 'rdid'=>$id])->orderBy('created_at','desc');

        $grid->tools(function($tools){
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
            $tools->append('<div class="btn-group pull-right new-create" style="margin-right: 10px">
                                <a href="'. url('admin/wechat/seat/seatUser') .'" class="btn btn-sm btn-default" title="返回">
                                    <span class="hidden-xs">&nbsp;&nbsp;返回</span>
                                </a>
                            </div>');
        });
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->filter(function($filter){
            //$filter->disableIdFilter();
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            //$actions->disableDelete();
        });
        $grid->mark('座位信息');
        $grid->openid('openid');
        $grid->s_time('入座时间');
        $grid->e_time('离座时间');
        return $grid;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid3($id)
    {
        $grid = new Grid(new SeatByBooking());
        $grid->model()->where(['token'=>session('wxtoken'), 'rdid'=>$id])->orderBy('created_at','desc');

        $grid->tools(function($tools){
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
            $tools->append('<div class="btn-group pull-right new-create" style="margin-right: 10px">
                                <a href="'. url('admin/wechat/seat/seatUser') .'" class="btn btn-sm btn-default" title="返回">
                                    <span class="hidden-xs">&nbsp;&nbsp;返回</span>
                                </a>
                            </div>');
        });
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->filter(function($filter){
            $filter->disableIdFilter();
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            //$actions->disableDelete();
        });
        $grid->mark('座位信息');
        $grid->status('状态')->using([
            1 => '<span class="badge bg-green">签到</span>',
            2 => '<span class="badge bg-default">已取消</span>',
            3 => '<span class="badge bg-red">违约</span>'
        ]);
        $grid->s_time('开始时间');
        $grid->e_time('结束时间');
        $grid->real_time('实际离开时间');
        return $grid;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid4($id)
    {
        $grid = new Grid(new SeatCurrBooking());
        $grid->model()->where(['token'=>session('wxtoken'), 'rdid'=>$id])->with('region')->orderBy('created_at','desc');

        $grid->tools(function($tools){
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
            $tools->append('<div class="btn-group pull-right new-create" style="margin-right: 10px">
                                <a href="'. url('admin/wechat/seat/seatUser') .'" class="btn btn-sm btn-default" title="返回">
                                    <span class="hidden-xs">&nbsp;&nbsp;返回</span>
                                </a>
                            </div>');
        });
        $grid->disableRowSelector();
        $grid->disableCreateButton();
        $grid->filter(function($filter){
            $filter->disableIdFilter();
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            //$actions->disableDelete();
        });

        $grid->mark('区域位置');
        $grid->status('状态')->using([
            '0' => '<span class="badge bg-red">待签到</span>',
            '1' => '<span class="badge bg-green">已签到</span>',
            '2' => '<span class="badge bg-default">已取消</span>',
        ]);

        $grid->s_time('开始时间');
        $grid->e_time('结束时间');
        $grid->column('','签到时间范围')->display(function(){
            return $this->sign_min . '~' .substr($this->sign_max,10);
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
        $show = new Show(SeatUser::findOrFail($id));

        $show->id('Id');
        $show->rdid('Rdid');
        $show->username('Username');
        $show->open_id('Open id');
        $show->nickname('Nickname');
        $show->head_img('Head img');
        $show->lastdate('Lastdate');
        $show->token('Token');
        $show->score('Score');
        $show->status('Status');
        $show->forbid_t('Forbid t');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SeatUser);

        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->text('rdid', '读者证号')->readonly();
        $form->number('violations', '违规次数')->min(0);
        $status = [
            'on' => ['value' => 1, 'text' => '正常', 'color' => 'success'],
            'off' => ['value' => 2, 'text' => '黑名单'],
        ];
        $form->switch('status', '状态')->states($status);
        $form->datetime('forbidden', '禁用截止时间');
        $form->datetime('last_date', '最近使用时间')->default(date('Y-m-d H:i:s'))->disable();
        return $form;
    }

}
