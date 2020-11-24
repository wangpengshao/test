<?php

namespace App\Admin\Controllers\Wechat\Seat;

use App\Models\Seat\SeatAttr;
use App\Models\Seat\SeatChart;
use App\Http\Controllers\Controller;
use App\Models\Seat\SeatRegion;
use App\Models\Seat\SeatCurrBooking;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class SeatChartController extends Controller
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
            ->header('座位列表')
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
     * Destroy interface.
     */
    public function destroy($ids)
    {
        SeatChart::destroy(explode(',',$ids));
        DB::table('seat_chart_attr')->whereIn('chart_id',explode(',',$ids))->delete();
        return Response::json(['status'=>true, 'message'=>'删除成功 !']);
    }

    /**
     * Add Attr to seat
     */
    public function addAttr()
    {
        $input = request()->all();
        $idsArr = explode(',', $input['ids']);
        $chart = DB::table('seat_chart_attr')->where('attr_id',$input['type'])->pluck('chart_id')->toArray();
        $data = [];
        foreach ($idsArr as $v){
            if(!in_array($v,$chart)){
                $temp = ['chart_id'=>$v, 'attr_id'=>$input['type']];
                $data[] = $temp;
            }
        }
        DB::table('seat_chart_attr')->insert($data);
        return Response::json(['status'=>true, 'message'=>'添加成功']);
    }

    /**
     * Remove seat attr
     */
    public function removeAttr()
    {
        $input = request()->all();
        DB::table('seat_chart_attr')->where([['chart_id',$input['id']],['attr_id',$input['type']]])->delete();
        return Response::json(['status'=>true, 'message'=>'移除成功']);
    }

    /**
     * Generate seating configuration
     */
    public function charts(Content $content)
    {
        $id = request()->route('id');
        $chartModel = new SeatChart();
        $allCharts = $chartModel->where([['token',session('wxtoken')],['region_id',$id]])->with('attr:id,name')->orderBy('id')->get();
        $allAttrs = SeatAttr::where('token','=',session('wxtoken'))->get();

        return $content
            ->header('座位配置')
            ->description('座位预约')
            ->body(view('admin.diy.seatCharts',compact('allAttrs','allCharts'))->render());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        Admin::script($this->changeStatusJs());
        $grid = new Grid(new SeatChart);
        $grid->model()->where('token', session('wxtoken'))->with(['region:name','attr:name'])->orderBy('region_id')->orderBy('numid');
        $regions = SeatRegion::where('token',session('wxtoken'))->where('pid','>',0)->orderBy('pid')->orderBy('id')->get(['id','name'])->toArray();
        $select = [];
        foreach($regions as $v){
            $select[$v['id']] = $v['name'];
        }
        //查询当前预约的座位记录
        $currBooking = SeatCurrBooking::where('token', session('wxtoken'))->get();

        $grid->disableCreateButton();
        $grid->filter(function($filter) use($select){
            $filter->disableIdFilter();
            $filter->equal('region_id', '区域')->select($select);
            $filter->equal('numid', '座位编号');

        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            if($this->row->status == 3){
                $actions->prepend('<a href="javascript:void(0)" class="btn btn-xs btn-danger row-changeStatus" data-toggle="tooltip" data-original-title="开启" data-id="'.$this->row->id.'" data-status="1"><i class="fa fa-lock"></i></a>');
            }
            else{
                $actions->prepend('<a href="javascript:void(0)" class="btn btn-xs btn-success row-changeStatus" data-toggle="tooltip" data-original-title="关闭" data-id="'.$this->row->id.'" data-status="3"><i class="fa fa-unlock-alt"></i></a>');
            }

        });

        $grid->column('region', '区域')
            ->display(function($region){
                return $region['name'];
            });
        $grid->column('numid', '座位编号')
            ->display(function($numid){
                return $numid . ' 号座';
            });
        $grid->column('attr', '属性')->pluck('name')->label();

        $grid->column('status', '当前状态')
            ->display(function($status) use ($currBooking){
                if ($status == 1){
                    return '<span class="label label-success">空位</span>';
                }
                elseif ($status == 2){
                    return '<span class="label label-danger">使用中</span>';
                }
                elseif ($status == 3){
                    return '<span class="label label-warning">关闭</span>';
                }
            });
        $grid->column('rdid', '使用者');
        /*$grid->column('seated_id', '使用者')->display(function($seated_id) use ($currBooking){
            if(!$seated_id){
                //空位情况下，判断座位在当前时间是否有预约
                foreach($currBooking as $value){
                    if($value->chart_id == $this->id && date('Y-m-d H:i:s') >= $value->s_time && date('Y-m-d H:i:s') <= $value->allow_max){
                        return $value->user_id;
                    }
                }
            }else{
                return $seated_id;
            }

        });*/
        $grid->column('queue_id', '排队id')
            ->display(function($queue){
                if($queue == 0){
                    return ;
                }
                return $queue;
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
        $show = new Show(SeatChart::findOrFail($id));

        $show->id('Id');
        $show->token('Token');
        $show->region_id('Region id');
        $show->numid('Numid');
        $show->attr_id('Attr id');
        $show->status('Status');
        $show->user_id('User id');
        $show->line_id('Line id');
        $show->usetime('Usetime');
        $show->queque_id('Queque id');
        $show->booking('Booking');
        $show->is_booking('Is booking');
        $show->s_time('S time');
        $show->e_time('E time');
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
        $form = new Form(new SeatChart);

        $form->text('token', 'Token');
        $form->number('region_id', 'Region id');
        $form->number('numid', 'Numid');
        $form->text('attr_id', 'Attr id');
        $form->switch('status', 'Status')->default(1);
        $form->text('user_id', 'User id');
        $form->text('line_id', 'Line id');
        $form->number('usetime', 'Usetime');
        $form->number('queque_id', 'Queque id');
        $form->text('booking', 'Booking');
        $form->switch('is_booking', 'Is booking');


        return $form;
    }

    /**
     * 座位二维码下载
     * @param Request $request
     * @return string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function downloadQrcode(Request $request)
    {
        $token = session('wxtoken');
        $dir = './seatQrcode/'.$token;

        if (!@is_dir($dir)){
            @mkdir($dir, 0777, true);
        }

        $charts = SeatChart::where([['token',$token], ['region_id',$request->input('region')]])->select(['id','numid'])->get();

        if(!$charts->first()){
            return '暂无可供导出的座位二维码';
        }

        $url = action('Web\SeatBooking\IndexController@chartStatus',['token'=>$token, 'opentype'=>'qrcode']);
        foreach ($charts as $chart){
            $sign = md5($chart->id . '2019');
            QrCode::format('png')
                ->errorCorrection('L')
                ->margin(2)
                ->size(300)
                ->generate(route('Seat::chartStatus',['token'=>$token, 'opentype'=>'qrcode', 'id'=>$chart->id, 'sign'=> $sign]), $dir.'/No'. $chart->numid .'.png');
                //->generate($url . '&id='.$chart->id, $dir.'/No '. $chart->numid .'.png');
        }
        exec('zip -q -m -r '.$dir.'/qrcode.zip '.$dir, $output, $return_var);

        return response()->download($dir.'/qrcode.zip')->deleteFileAfterSend(true);

    }

    /**
     * 修改座位状态
     * @param Request $request
     */
    public function changeStatus(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $chart = SeatChart::find($id);
        if($chart->status == 1 || $chart->status == 3){
            $chart->status = $status;
            $chart->save();
            return response()->json(['status'=>true,'message'=>'修改成功']);
        }else{
            return response()->json(['status'=>false,'message'=>'此座位正在使用中，不能修改']);
        }
    }

    public function changeStatusJs()
    {
        $action1 = route('seat.chart.changeStatus');
        return <<<SCRIPT
            $('.row-changeStatus').on('click', function () {
                let id = $(this).data('id');
                let status = $(this).data('status');
                let title = status == 3 ? '确定关闭此座位吗？' : '确定开启此座位吗？';
                swal({ 
                  title: title, 
                  type: 'question',
                  showCancelButton: true, 
                  confirmButtonColor: '#3085d6',
                  confirmButtonText: '确定', 
                  cancelButtonText: '取消',
                }).then(function(){
                console.log(id,status)
                    $.ajax({
                        url: "{$action1}",
                        type: "post",
                        dataType: "json",
                        data:{'id':id, 'status':status, _token:LA.token},
                        success: function (data) {
                            if(data.status==true){
                                 swal({title:data.message,type:"success",showConfirmButton:true});
                                 setTimeout(function(){ 
                                 $.pjax.reload('#pjax-container');
                                     //window.location.reload();
                                  }, 2000);
                            }else{
                               swal({title:data.message,type:"error",showConfirmButton:true});
                            }
                        },
                        error:function(){
                           swal("哎呦……", "出错了！","error");
                        }
                    });
                }).catch(swal.noop);
            })
SCRIPT;

    }
}
