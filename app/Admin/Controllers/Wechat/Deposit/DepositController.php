<?php

namespace App\Admin\Controllers\Wechat\Deposit;

use App\Admin\Controllers\CustomView\OnlyInfo;
use App\Admin\Extensions\ExcelExporter\DepositExporter;
use App\Models\Deposit\DepositEveryday;
use App\Models\Deposit\DepositLog;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;


class DepositController extends Controller
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
        Admin::script($this->script());
        return $content->header('预约记录')
            ->description('管理')
            ->body($this->grid());
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new DepositLog);
        $grid->rdid('证号');
        $grid->idCard('身份证号');
        $grid->name('用户名');
        $grid->deposit('押金');
        $grid->yuyue_date('预约日期');
        $grid->yuyue_time('预约时间');
        $grid->from('预约渠道');
        $grid->column('status', '状态')->display(function ($status) {
            switch ($status) {
                case 0:
                    $str = '<span class="badge bg-brown">已取消</span>';
                    break;
                case 1:
                    $str = '<span class="badge bg-green">已退款</span>';
                    break;
                case 2:
                    $str = '<span class="badge bg-red">已逾约</span>';
                    break;
                case 3:
                    $str = '<span class="badge bg-brown">已取消</span>';
                    break;
                case 4:
                    $str = '<span class="badge bg-brown">已拉黑</span>';
                    break;
                default:
                    $str = '<span class="badge bg-gray">已取消</span>';
            }
            return $str;
        });
        $grid->filter(function ($filter) {
            // 设置yuyue_date字段的范围查询
            $filter->between('yuyue_date', '预约时间')->datetime();

            $filter->like('rdid',   '读书者号');
            $filter->like('idCard', '身份证号');
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
        });
        $grid->exporter(new DepositExporter());
        $grid->disableExport(false);
        $grid->disableCreateButton();
        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $actions->append("<button class='btn btn-xs btn-danger margin-r-5 cancel' data-oid='{$actions->row->id}'
>取消</button>");
            $actions->append("<button class='btn btn-xs btn-success margin-r-5 checkPay' data-oid='{$actions->row->id} '
>退款</button>");
            $actions->append("<button class='btn btn-xs btn-warning margin-r-5 overdue' data-oid='{$actions->row->id}'
>逾约处理</button>");
            $actions->append("<button class='btn btn-xs btn-question margin-r-5 block' data-oid='{$actions->row->id}'
>拉黑处理</button>");
            $actions->append("<button class='btn btn-xs btn-success margin-r-5 remove_block' data-oid='{$actions->row->id}'
>解除拉黑</button>");
        });
        return $grid;
    }

    //退款处理
    public function refundPay(Request $request)
    {
        $id = $request->input('id');
        DepositLog::where('id', $id)->update(['status' => 1]);
        $re = ['status' => true, 'mes1' => '申请退款成功'];
        return $re;
    }

    //取消退款处理
    public function cancel(Request $request)
    {
        $id = $request->input('id');
        DepositLog::where('id', $id)->update(['status' => 0]);
        $re = ['status' => true, 'mes1' => '取消成功'];
        return $re;
    }

    //逾约处理
    public function overdue(Request $request)
    {
        $id = $request->input('id');
        DepositLog::where('id', $id)->update(['status' => 2]);
        $re = ['status' => true, 'mes1' => '处理成功'];
        return $re;
    }
    //拉黑处理
    public function block(Request $request)
    {
        $id = $request->input('id');
        DepositLog::where('id', $id)->update(['status' => 4]);
        $re = ['status' => true, 'mes1' => '处理成功'];
        return $re;
    }

    //取消拉黑操作
    public function remove_block(Request $request)
    {
        $id = $request->input('id');
        DepositLog::where('id', $id)->update(['status' => 0]);
        $re = ['status' => true, 'mes1' => '处理成功'];
        return $re;
    }

    protected function script()
    {
        $checkPayUrl = route('deposit.refundPay');
        $cancelPayUrl = route('deposit.cancel');
        $overduePayUrl = route('deposit.overdue');
        $blockPayUrl = route('deposit.block');
        $remove_blockPayUrl = route('deposit.remove_block');
        $token = session('wxtoken');
        return <<<SCRIPT
$('.checkPay').on('click', function () {
    var id=$(this).data('oid');
    swal({
         title: '是否给客户安排退款?',
         type: 'question',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$checkPayUrl}",
                        type: "post",
                        data: {"_token": LA.token,"id":id},
                        dataType: "json",
                        success: function (data) {
                           swal.close();
                           swal({
                             type:"success",
                             text: "退款成功！"
                            });
                        },
                        error:function(){
                         swal("对不起……", "出错了！","error");
                        }
                    });
            
                });
              },
         }).then(result => {
         console.log(result)
    })

});

$('.cancel').on('click', function () {
    var id=$(this).data('oid');
    var price=$(this).data('pr');
    var re_price=$(this).data('re');
    swal({
         title: '确定要取消吗',
         type: 'warning',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$cancelPayUrl}",
                        type: "post",
                        data: {"_token": LA.token,"id":id},
                        dataType: "json",
                        success: function (data) {
                           swal.close();
                           swal({
                             type:"success",
                             text: "取消成功！"
                            });
                        },
                        error:function(){
                         swal("对不起……", "出错了！","error");
                        }
                    });
            
                });
              },
         }).then(result => {
         console.log(result)
    })

});

$('.overdue').on('click', function () {
    var id=$(this).data('oid');
    swal({
         title: '是否进行逾约处理?',
         type: 'question',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$overduePayUrl}",
                        type: "post",
                        data: {"_token": LA.token,"id":id},
                        dataType: "json",
                        success: function (data) {
                           swal.close();
                           swal({
                             type:"success",
                             text: "处理成功！"
                            });
                        },
                        error:function(){
                         swal("对不起……", "出错了！","error");
                        }
                    });
            
                });
              },
         }).then(result => {
         console.log(result)
    })

});
$('.block').on('click', function () {
    var id=$(this).data('oid');
    swal({
         title: '是否进行拉黑处理?',
         type: 'warning',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$blockPayUrl}",
                        type: "post",
                        data: {"_token": LA.token,"id":id},
                        dataType: "json",
                        success: function (data) {
                           swal.close();
                           swal({
                             type:"success",
                             text: "处理成功！"
                            });
                        },
                        error:function(){
                         swal("对不起……", "出错了！","error");
                        }
                    });
            
                });
              },
         }).then(result => {
         console.log(result)
    })

});
$('.remove_block').on('click', function () {
    var id=$(this).data('oid');
    swal({
         title: '是否进行取消拉黑操作',
         type: 'question',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$remove_blockPayUrl}",
                        type: "post",
                        data: {"_token": LA.token,"id":id},
                        dataType: "json",
                        success: function (data) {
                           swal.close();
                           swal({
                             type:"success",
                             text: "处理成功！"
                            });
                        },
                        error:function(){
                         swal("对不起……", "出错了！","error");
                        }
                    });
            
                });
              },
         }).then(result => {
         console.log(result)
    })

});
SCRIPT;
    }

    /*//预约统计
    public function census(Request $request,Content $content){
        $status = $request->route('status');
        if ($status){
            $data['cancel']=DepositLog::where(['token'=>session('wxtoken'),'status'=>$status])->count();
            $data['block']=DepositLog::where(['token'=>session('wxtoken'),'status'=>'4'])->count();
            $data['refund']=DepositLog::where(['token'=>session('wxtoken'),'status'=>'1'])->count();
            $data['alloted']=DepositLog::where(['token'=>session('wxtoken'),'status'=>'2'])->count();
            $data['log_count']=DepositLog::where('token',session('wxtoken'))->count();
            echo json_encode(array('msg' => 'OK','v'=>$data));
            exit;
        }
        return $content->body(view('admin.deposit.census'));
    }*/
    public function census()
    {
        return \Encore\Admin\Facades\Admin::content(function (Content $content) {
            $doesntExist = DepositLog::whereToken(\request()->session()->get('wxtoken'))
                ->doesntExist();
            if ($doesntExist) {
                return $content->withWarning('提示', '抱歉，非法访问');
            }
            $content->header('预约数据中心');
            $content->description('统计');
            $content->row(function ($row) {
                $where1 = [
                    'token' => \request()->session()->get('wxtoken'),
                    'status' => '1'
                ];
                $userCount = DepositLog::where($where1)->count('id');
                $row->column(4, new OnlyInfo('退款用户总数', 'user', 'green', '', $userCount));
                $where2 = [
                    'token' => \request()->session()->get('wxtoken'),
                    'status' => '2'
                ];
                $alloted = DepositLog::where($where2)->count('id');
                $row->column(4, new OnlyInfo('逾约用户总数', 'user', 'yellow', '', $alloted));
                $where3 = [
                    'token' => \request()->session()->get('wxtoken'),
                    'status' => '3'
                ];
                $cancel = DepositLog::where($where3)->count('id');
                $row->column(4, new OnlyInfo('取消用户总数', 'credit-card', 'red', '', $cancel));
                $where4 = [
                    'token' => \request()->session()->get('wxtoken'),
                    'status' => '4'
                ];
                $block = DepositLog::where($where4)->count('id');
                $row->column(4, new OnlyInfo('黑名单用户总数', 'users', 'purple', '', $block));
                $where5 = [
                    'token' => \request()->session()->get('wxtoken'),
                    'status' => '0'
                ];
                $pending = DepositLog::where($where5)->count('id');
                $row->column(4, new OnlyInfo('待处理的用户总数', 'user', 'blue', '', $pending));

                $type1Count = DepositEveryday::where('date', date("Y-m-d"))->get(['balance','amount'])->first()
                              ;
                $data = [
                    'data' => json_encode([$type1Count['balance'], $type1Count['amount']]),
                    'labels' => json_encode(['剩余押金', '总押金']),
                    'id' => 'box2'
                ];
                $box2 = new Box('用户类型', view('admin.Chart.pie')->with($data));
                $box2->removable();
                $box2->collapsable();
                $box2->style('info');
                $row->column(4, $box2);
            });

        });
    }
}