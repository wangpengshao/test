<?php

namespace App\Admin\Controllers\Wechat\Seat;

use App\Models\Seat\SeatChart;
use App\Models\Seat\SeatRegion;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;

class SeatRegionController extends Controller
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
        Admin::script($this->propInput());
        return $content
            ->header('区域配置')
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
        $data = SeatRegion::where('id',$id)->select('pid')->get()->toArray();
        if($data[0]['pid']){
            return $content
                ->header('编辑')
                ->description('座位预约')
                ->body($this->form()->edit($id));
        }else{
            return $content
                ->header('编辑')
                ->description('座位预约')
                ->body($this->form2()->edit($id));
        }
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
            ->header('新增')
            ->description('预约区域')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SeatRegion);
        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->disableFilter();
        $grid->disableColumnSelector();
        $grid->perPages = [50, 100];
        $grid->perPage = 50;
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
            $tools->append('<div class="btn-group pull-right propInput" style="margin-right: 10px">
                                <a href="javascript:void(0)" class="btn btn-sm btn-success" title="新增">
                                    <i class="fa fa-plus"></i><span class="hidden-xs">&nbsp;&nbsp;新增</span>
                                </a>
                            </div>');
        });
        $grid->actions(function ($actions) {
            $actions->disableView();
            if($actions->row->pid==0){
                //$actions->disableEdit();
                $createUrl = route('seatRegion.create',['currId'=>$actions->row->id]);
                $actions->prepend('<a href="'. $createUrl . '" title="添加子区域" class=" btn btn-xs btn-default"><i class="fa fa-level-down"></i></a>');
            }else{
                $charts = route('seat.charts',['currId'=>$actions->row->id]);
                $actions->prepend('<a href="javascript:void(0)" class="checkregion" data-id="'. $actions->row->id .'" data-num="'. $actions->row->chart_nums .'" data-lines="'. $actions->row->lines .'" data-cols="'. $actions->row->cols .'" data-url="'. $charts .'" title="配置座位"><i class="fa fa-calculator"></i></a>');
            }
        });
        $grid->rows(function($row){
            if($row->column('pid')==0){
                $row->setAttributes(['class'=>'info']);
            }
        });
        $grid->column('name', '区域名')
            ->display(function(){
                if($this->pid !== 0){
                    $this->name = str_repeat('&nbsp;',8) . $this->name;
                }
                return $this->name;
            });
        $grid->column('img', '平面图')->image('');
        $grid->column('is_hot', '热度')->using(['1' => '<span class="badge bg-red">热</span>']);
        $grid->column('', '开放时间')
            ->display(function () {
                if($this->pid !== 0){
                    return $this->s_time . '——' . $this->e_time;
                }else{
                    return ;
                }
            });
        $grid->column('chart_nums', '座位数')->display(function ($num) {
            if($this->pid !== 0){
                return $num;
            }else{
                return ;
            }
        });
//        $grid->column('status', '状态开关')->switch();
//        $grid->column('booking_switch', '预约开关')->switch();

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
        $show = new Show(SeatRegion::findOrFail($id));

        $show->cols('Cols');
        $show->e_time('E time');
        $show->id('Id');
        $show->img('Img');
        $show->is_booking('Is booking');
        $show->is_hot('Is hot');
        $show->keeptime('Keeptime');
        $show->name('Name');
        $show->num('Num');
        $show->order_number('Order number');
        $show->pid('Pid');
        $show->poseition_left('Poseition left');
        $show->poseition_top('Poseition top');
        $show->remarks('Remarks');
        $show->rows('Rows');
        $show->s_time('S time');
        $show->status('Status');
        $show->token('Token');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SeatRegion);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
        });
        $form->select('pid', '上级区域')->options(SeatRegion::Regions())->default(request('currId'));
        $form->text('name', '区域名称');
        $form->number('chart_nums', '座位数量')->default(0)->min(0);
        $form->image('img', '平面图')->help('平面图(600px * 500px)')->move(materialUrl().'/seat')->uniqueName()->attribute('hideMaterial');
        $form->number('cols', '列数')->default(6)->min(0);
        $form->date('s_time', '开放时间')->format('HH:mm')->default('00:00');
        $form->date('e_time', '关闭时间')->format('HH:mm')->default('00:00');
        $form->switch('is_hot', '是否热门')->default(0);
        $form->switch('status', '状态开关')->default(1);
        $form->switch('booking_switch', '预约开关')->default(1);
        $form->hidden('token')->default(session('wxtoken'));

        //在表单提交前调用
        $form->submitted(function (Form $form) {
           $GLOBALS['oldnum'] = $form->model()->chart_nums;
        });

        //保存后回调
        $form->saved(function (Form $form) {
            $num = $form->model()->chart_nums;
            if ($GLOBALS['oldnum'] != $num){
                $max_numid = SeatChart::where('token',session('wxtoken'))->where('region_id',$form->model()->id)->max('numid');
                $iniItem = ['token'=>session('wxtoken'), 'region_id'=>$form->model()->id,'created_at'=>date('Y-m-d H:i:s')];
                $allCharts = [];
                // 初次生成位置
                if (empty($max_numid)){
                    for ($i = 0; $i < $num; $i++){
                        $iniItem['numid'] = $i+1;
                        $allCharts[] = $iniItem;
                    }
                    DB::table('seat_chart')->insert($allCharts);
                } else {
                    // 增加座位数量
                    if ($num > $GLOBALS['oldnum']){
                        for ($i=$GLOBALS['oldnum']; $i<$num; $i++){
                            $iniItem['numid'] = $i+1;
                            $allCharts[] = $iniItem;
                        }
                        DB::table('seat_chart')->insert($allCharts);
                    }else{
                        // 减少座位数量
                        $chartIds = DB::table('seat_chart')->where('token','=',session('wxtoken'))->where('numid','>',$num)->pluck('id')->toArray();
                        DB::table('seat_chart')->where('token','=',session('wxtoken'))->where('numid','>',$num)->delete();
                        DB::table('seat_chart_attr')->whereIn('chart_id',$chartIds)->delete();
                    }
                }
            }
        });
        return $form;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form2()
    {
        $form = new Form(new SeatRegion);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
        });
        $form->select('pid', '上级区域')->options(SeatRegion::Regions())->default(request('currId'));
        $form->text('name', '区域名称');
//        $form->switch('status', '状态开关')->default(1);
//        $form->switch('booking_switch', '预约开关')->default(1);
        $form->hidden('token')->default(session('wxtoken'));


        return $form;
    }

    /**
     * create root region
     */
    public function postRegion()
    {
        $region = new SeatRegion();
        $region->name = request()->regionname;
        $region->token = session('wxtoken');
        $region->created_at = date('Y-m-d H:i:s');
        $region->save();
        return response()->json([
            'status' => true,
            'message' => '添加成功'
        ]);
    }

    /**
     * An input box pops up
     */
    public function propInput()
    {
        $action1 = route('seat.postRegion');
        return <<<SCRIPT
            $('.propInput').on('click', function () {
                swal({
                    title: '请输入区域名称',
                    input: 'text',
                    inputPlaceholder: '区域名称',
                    showCancelButton: true,
                    confirmButtonText: "确定", 
                    cancelButtonText: "取消",
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: function(region) {
                    return new Promise(function(resolve, reject) {
                        if (!region) {
                          reject('别急呀，您还没有输入区域名称呢……');
                          return;
                        }
                        $.ajax({
                            url: "{$action1}",
                            type: "post",
                            dataType: "json",
                            data:{'regionname':region, _token: LA.token},
                            success: function (data) {
                                if(data.status==true){
                                 swal({title:data.message,type:"success",showConfirmButton:false});
                                 setTimeout(function(){ 
                                     window.location.reload();
                                  }, 2000);
                                }else{
                                   swal({title:data.message,type:"error",showConfirmButton:false});
                                }
                            },
                            error:function(){
                               swal("哎呦……", "出错了！","error");
                            }
                        });
                    });
                    },
                }).then(function(email) {
                  swal({
                    type: 'success',
                    title: 'Ajax请求完成！',
                    html: '提交的email是：' + email
                  });
                })
            });
            $('.checkregion').on('click', function () {
                if($(this).attr('data-num')==0){
                    swal({
                        type: 'info',
                        text: '请先设置完区域的座位数量...'
                    });
                }else{
                    window.location.href = $(this).attr('data-url');
                }
            });
SCRIPT;

    }

}
