<?php

namespace App\Admin\Controllers\Wechat\Seat;

use App\Models\Seat\Config;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use function foo\func;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Services\MenuService;
use App\Models\Wechat\IndexMenu;

class SeatConfigController extends Controller
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
        Admin::script($this->newCreate());
        return $content
            ->header('配置')
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
            ->header('全局配置')
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
            ->header('配置')
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
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Config);
        $grid->disableCreateButton();
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disablePagination();
        $grid->tools(function ($tools) use($grid){
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });

            $tools->append('<div class="btn-group pull-right new-create" style="margin-right: 10px">
                        <a href="javascript:void(0)" class="btn btn-sm btn-success" title="初始化">
                            <span class="hidden-xs">&nbsp;&nbsp;初始化</span>
                        </a>
                    </div>');

        });
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
        });
        $grid->model()->where('token',session('wxtoken'));
        $grid->column('keeptime','保留时间');
//        $grid->column('num', '预约数量/人');
        $grid->column('shortest_t', '最短预约时间');
        $grid->column('longest_t', '最长预约时间');
        $grid->column('day_t', '预约提前天数');
        $grid->column('ok_t', '签到提前时间');
        $grid->column('delay_t', '签到延迟时间');
        $grid->column('violate_num', '违规上现');
        $grid->column('disabled_date', '禁用天数');
        $grid->column('updated_at', '更新时间');
        $grid->column('','预约地址')->display(function(){
            return route('Seat::index', ['token' => $this->token]);
        })->urlWrapper();
        $grid->column('status', '系统状态')->switch();
        $grid->column('booking_switch', '预约状态')->switch();

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
        $show = new Show(Config::findOrFail($id));
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Config);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
        });
        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
        });
        $form->tab('基本设置', function ($form){
            $form->switch('status', '系统总开关')->states();
            $form->number('keeptime','座位保留时间')->min(0)->help('单位：分钟');
            $form->number('violate_num','违规次数限制')->min(3);
            $form->number('disabled_date','禁用天数')->min(5);
        });
        $form->tab('预约设置', function ($form){
            $form->switch('booking_switch', '预约总开关')->states();
            $form->number('shortest_t','最短预约时长')->min(0)->help('单位：分钟');
            $form->number('longest_t','最长预约时长')->min(0)->help('单位：分钟');
            $form->number('ok_t','签到可提前时长')->min(0)->help('单位：分钟');
            $form->number('delay_t','签到可延迟时长')->min(0)->help('单位：分钟');
            $form->number('day_t','可预约天数')->min(0);
//            $form->number('num','可预约座位数')->min(1);
        });
        $form->tab('地址设置', function ($form) {
            $form->map('lat', 'lng', '地图');
            $form->text('purview','签到范围半径')->default(0)->help('单位km，0表示不限制范围');
        });
        $form->tab('公告信息设置', function ($form) {
            $form->editor('notice','公告信息');
        });

        $form->saved(function(){
            $cacheKey = 'seatGlobalConfig_'.session('wxtoken');
            Cache::forget($cacheKey);
        });

        return $form;
    }

    /**
     * Initialize the global configuration
     */
    public function initConfig()
    {
        $config = new Config();
        $result = $config->where('token','=',session('wxtoken'))->get()->toArray();
        if($result){
            return Response::json(['status'=>false]);
        }else{
            $config->create(['token'=>session('wxtoken'), 'created_at'=>date('Y-m-d H:i:s')]);
        }
        return Response::json(['status'=>true]);
    }

    /**
     * new create
     */
    public function newCreate()
    {
        $action1 = route('seat.initConfig');
        return <<<SCRIPT
        
            let length = document.querySelectorAll('.column-s_score').length;
            
            if(length>1) document.querySelector('.new-create').style.display = "none";
            
            $('.new-create').on('click', function () {
                swal({
                    type: 'question',
                    text: '确认要初始化全局配置吗？',
                    showCancelButton: true,
                    confirmButtonText: "确定", 
                    cancelButtonText: "取消",
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: function() {
                    return new Promise(function(resolve, reject) {
                        $.ajax({
                            url: "{$action1}",
                            type: "get",
                            dataType: "json",
                            success: function (data) {
                                if(data.status==true){
                                 swal({title:'初始化成功',type:"success",showConfirmButton:false});
                                 setTimeout(function(){ 
                                     window.location.reload();
                                  }, 2000);
                                }else{
                                   swal({text:'已经初始化过了',timer: 1500,type:"info",showConfirmButton:false});
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
SCRIPT;

    }
}
