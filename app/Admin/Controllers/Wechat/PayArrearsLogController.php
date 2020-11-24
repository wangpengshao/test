<?php

namespace App\Admin\Controllers\Wechat;

use App\Admin\Extensions\ExcelExporter\PayArrearsExporter;
use App\Models\Wechat\ArrearsLog;
use App\Models\Wechat\ArrearsOrders;
use App\Models\Wechat\ArrearsRefund;
use App\Http\Controllers\Controller;
use App\Models\Wechat\WechatPay;
use App\Services\IcbcService;
use App\Services\OpenlibService;
use App\Services\PayHelper;
use Encore\Admin\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use function GuzzleHttp\Psr7\str;

class PayArrearsLogController extends Controller
{

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
        return $content
            ->header('支付欠款列表')
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
        $grid = new Grid(new ArrearsOrders());
        $token = \request()->session()->get('wxtoken');

        $grid->disableExport(false);
        $grid->exporter(new PayArrearsExporter());
        $grid->model()->where('token', $token)->orderBy('created_at', 'desc');
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('rdid', '读者账号');
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('user', function ($query) use ($input) {
                        $query->where('nickname', 'like', '%' . $input . '%');
                    });
                }, '微信昵称', 'nickname')->inputmask([], $icon = 'wechat');
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('hasManyLog', function ($query) use ($input) {
                        $query->where('tranid', $input);
                    });
                }, 'tranid', 'tranid');

            });
            $filter->column(1 / 2, function ($filter) {
                $filter->between('created_at', '支付时间')->datetime();
                $filter->equal('pay_status', '支付状态')->radio([
                    1 => '已支付',
                    0 => '未支付',
                ]);
                $filter->equal('order_id', '商户订单号');

            });
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $price = $actions->row->price;
            $refunds = $actions->row->refunds->toArray();
            $refunds = array_column($refunds, 'refund_fee');
            $refunds = array_sum($refunds);
            $actions->append("<button class='btn btn-xs btn-success margin-r-5 checkPay' data-oid='{$actions->row->order_id}' data-type='{$actions->row->pay_type}'
>核对支付</button>");

            if ($refunds < $price && in_array($actions->row->pay_status, [1, 2])) {
                $re = $price - $refunds;
                $actions->append("<button class='btn btn-xs btn-danger margin-r-5 refundPay' data-oid='{$actions->row->order_id}' data-type='{$actions->row->pay_type}'
 data-pr='{$price}' data-re='{$re}' >退款</button>");
            }
        });

        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        $grid->column('user.nickname', '微信昵称');
        $grid->rdid('付款证号');
        $grid->price('支付金额(元)');

        $grid->hasManyLog('订单内容')->expand(function () {
            $logs = $this->hasManyLog;
            $da = [];
            if (!empty($logs)) {
                foreach ($logs as $k => $v) {
                    $statusStr = ($v['status'] == 1) ?
                        '<span class="badge bg-green">成功</span>' : '<span class="badge bg-gray">失败</span>';
                    $ac = ($this->pay_status == 1 && $v['status'] !== 1) ?
                        '<button class="badge bg-blue reapply" data-id="' . $v["id"] . '">异常补销</button>' :
                        '';
                    $da[] = [
                        $v['tranid'],
                        $v['feetype'],
                        $v['price'],
                        $statusStr,
                        $ac,
                    ];
                }
                unset($k, $v);
            }
            return new Table(['tranid', '缴费类型', '金额(元)', '销账状态', ''], $da);

        }, '查看', 'r1');

        $grid->refunds('退款(元)')->display(function ($refunds) {
            $refund_fee = 0;
            $wait = 0;
            foreach ($refunds as $k => $v) {
                if ($v['status'] == 1) {
                    $refund_fee += $v['refund_fee'];
                } else {
                    $wait += $v['refund_fee'];
                }
            }
            unset($k, $v);
            $str = '';
            if ($refund_fee > 0) {
                $str .= '已退¥ ' . $refund_fee . '&nbsp;';
            }
            if ($wait > 0) {
                $str .= '待退¥ ' . $wait . '&nbsp;';
            }
            return ' <span class="badge bg-white">' . $str . '</span>';
        });

        $grid->column('pay_status', '支付状态')->display(function ($pay_status) {
            switch ($pay_status) {
                case -1:
                    $str = '<span class="badge bg-brown">支付失败</span>';
                    break;
                case 1:
                    $str = '<span class="badge bg-green">已支付</span>';
                    break;
                case 2:
                    $str = '<span class="badge bg-red">退款处理</span>';
                    break;
//                case 3:
//                    $str = '<span class="badge bg-brown">已退款</span>';
//                    break;
                default:
                    $str = '<span class="badge bg-gray">未支付</span>';
            }
            return $str;
        })->sortable();

        $grid->column('支付详情')->expand(function () {
            $order = $this;
            switch ($order['pay_type']) {
                case 1:
                    $pay_type = '<span class="badge bg-blue">扫码支付</span>';
                    break;
                case 0:
                    $pay_type = '<span class="badge bg-green">微信支付</span>';
                    break;
                case 2:
                    $pay_type = '<span class="badge bg-info">工行聚合支付</span>';
                    break;
                default:
                    $pay_type = '';
            }

            $da = [
                '支付方式' => $pay_type,
                '商户订单号' => $order['order_id'],
                '微信订单号' => $order['transaction_id'],
                '金额(元)' => $order['price'],
                '原金额(元)' => $order['origin_price'],
                '现金支付金额(元)' => $order['cash_fee'],
                '支付者openid' => $order['openid'],
                '支付时间' => $order['pay_at'],
            ];
            $re_order = $this->refunds;

            if ($re_order) {
                foreach ($re_order as $k => $v) {
                    $str = '申请时间: ' . $v['created_at'] . ' => ¥ ' . $v['refund_fee'];
                    if ($v['status'] == 1) {
                        $str .= ' => 已到账(' . $v['refund_str'] . ') => 到账时间: ' . $v['updated_at'];
                    } else {
                        $str .= ' => 退款中';
                    }
                    $da['退款-' . ($k + 1)] = $str;
                }
                unset($k, $v);
            }

            return new Table(['类型', '值'], $da);
        }, '查看', 'r2');

        $grid->created_at('支付时间')->sortable();
        return $grid;
    }

    protected function script()
    {
        $checkPayUrl = route('wechat.arrears.checkPay');
        $refundPayUrl = route('wechat.arrears.refundPay');
        $reapplyUrl = route('wechat.arrears.reapply');

        $token = \request()->session()->get('wxtoken');;
        return <<<SCRIPT
$('.checkPay').on('click', function () {
    var order_id=$(this).data('oid');
    var pay_type=$(this).data('type');
    swal({
         title: '查看微信官方商户号当前订单支付状态?',
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
                        data: {"_token": LA.token,"order_id":order_id,"pay_type":pay_type,"token":"{$token}"},
                        dataType: "json",
                        success: function (data) {
                          var type="error";
                          if(data.status==true){
                            type="success";
                          }
                          swal('',data.mes,type);
                        },
                        error:function(){
                         swal("哎呦……", "出错了！","error");
                        }
                    });
            
                });
              },
         }).then(result => {
         console.log(result)
    })

});

$('.refundPay').on('click', function () {
    var order_id=$(this).data('oid');
    var price=$(this).data('pr');
    var re_price=$(this).data('re');
    var pay_type=$(this).data('type');
    swal({
         title: '请输入需要退款金额',
         text: '可退款金额 '+ re_price,
         input: 'number',
         type: 'warning',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         inputPlaceholder:'请输入数字',
         inputValidator: function(value) {
              return new Promise(function (resolve, reject) {
                     if(!value){
                        reject('退款金额不能为空');
                      }
                      if(value>re_price){
                        reject('退款金额不能大于可退款金额!');
                      }
                     resolve();
            });
         },
         preConfirm: function(number) {
               let data= {
                    "_token": LA.token,
                    "order_id":order_id,
                    "token":"{$token}",
                    "refundNumber":number,
                    "price":price,
                    "pay_type":pay_type
                };
                let headers={'Content-Type': 'application/json'};
            return fetch('{$refundPayUrl}',{method: 'POST',body: JSON.stringify(data),headers: new Headers(headers)})
             .then(response => {
                    if (!response.ok) {
                      throw new Error(response.statusText)
                    }
                    return response.json()})
                    .catch(error => {throw new Error(error);})
         },
        allowOutsideClick: false,
    }).then(result => {
      $.pjax.reload('#pjax-container');
      if(result.status==true){
          swal(result.mes1,result.mes2,'success');
      }else{
          swal(result.mes1,result.mes2,'error');
      }
    }) .catch(swal.noop);

});
$(document).on('click', '.reapply', function(){
    var id=$(this).data('id');
    swal({
         title: '确定要补销当前欠单?',
         type: 'warning',
         text:'提示:请先核实支付状态和办证详情内容无误之后再进行操作！',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function(number) {
               let data= { "_token": LA.token,"id":id,"token":"{$token}"};
               let headers={'Content-Type': 'application/json'};
            return fetch('{$reapplyUrl}',{method: 'POST',body: JSON.stringify(data),headers: new Headers(headers)})
             .then(response => {
                    if (!response.ok) {
                      throw new Error(response.statusText)
                    }
                    return response.json()})
                    .catch(error => {throw new Error(error);})
         },
        allowOutsideClick: false,
       }).then(result => {
      $.pjax.reload('#pjax-container');
      if(result.status==true){
          swal(result.mes1,result.mes2,'success');
      }else{
          swal(result.mes1,result.mes2,'error');
      }
    }) .catch(swal.noop);

});

SCRIPT;
    }

    public function checkPay(Request $request, PayHelper $payHelper)
    {
        $token = $request->input('token');
        $order_id = $request->input('order_id');
        $pay_type = $request->input('pay_type');

        $re = ['status' => false, 'mes' => '系统繁忙,请稍后再试!'];

        if($pay_type == 2){
            $icbc = IcbcService::make($token);
            $request = array(
                "method" => 'POST',
                "isNeedEncrypt" => false,
                "biz_content" => array(
                    "out_trade_no" => $order_id,
                )
            );
            $msg_id = $payHelper->GenerateMsgId($token);
            $response = $icbc->execute($request,$msg_id, 'api_payment_query');//执行调用;msgId消息通讯唯一编号，要求每次调用独立生成，APP级唯一

            if (array_get($response, 'return_code') === '0') {
                if (array_get($response, 'pay_status') === '1') {
                    $re['status'] = true;
                    $trade_state_desc = '支付成功';
                }

                $re['mes'] = '商户订单号</br>' . $order_id . '</br>状态: ' . $trade_state_desc;
            } else {
                $trade_state_desc = array_get($response, 'return_msg');
                $re['mes'] = '商户订单号</br>' . $order_id . '</br>状态: ' . $trade_state_desc;
            };

        }else{
            $app = WechatPay::initialize($token);
            $response = $app->order->queryByOutTradeNumber($order_id);

            if (array_get($response, 'return_code') === 'SUCCESS') {
                if (array_get($response, 'trade_state') === 'SUCCESS') {
                    $re['status'] = true;
                };
                $re['mes'] = '商户订单号</br>' . $order_id . '</br>状态: ' . $response['trade_state_desc'];
            }
        }
        return $re;
    }


    public function refundPay(Request $request, PayHelper $payHelper)
    {
        $token = $request->input('token');
        $order_id = $request->input('order_id');
        $price = $request->input('price');
        $refundNumber = $request->input('refundNumber');
        $pay_type = $request->input('pay_type');

        $refund_order_id = $payHelper->GenerateOrderNumber('ZFQK-TK');
        $re = ['status' => false, 'mes1' => '退款失败', 'mes2' => '系统繁忙,请稍后再试!'];

        if($pay_type == 2){
            $requestData = array(
                "method" => 'POST',
                "isNeedEncrypt" => false,
                "biz_content" => array(
                    "out_trade_no" => $order_id,
                    "reject_no" => $refund_order_id,
                    "reject_amt" => (string) ($price * 100),
                )
            );
            $msg_id = $payHelper->GenerateMsgId($token);
            $icbc = IcbcService::make($token);
            $response = $icbc->execute($requestData,$msg_id,'api_reject');
            if (array_get($response, 'return_code') === '0') {
                //修改订单状态
                $re['mes2'] = array_get($response, 'err_code_des');
                //添加退款记录
                $refundLog = [
                    'token' => $token,
                    'status' => 1,
                    'data' => json_encode($response),
                    'order_id' => $order_id,
                    'refund_fee' => $refundNumber,
                    'total_fee' => $price,
                    'out_refund_no' => $refund_order_id,
                    'refund_id' => ''
                ];
                ArrearsRefund::create($refundLog);
                ArrearsOrders::where('order_id', $order_id)->update(['pay_status' => 2]);
                $re = ['status' => true, 'mes1' => '申请退款成功', 'mes2' => '退款金额: ' . $refundNumber];
            } else {
                $re = ['status' => false, 'mes1' => '申请退款失败', 'mes2' => array_get($response, 'return_msg')];
            }

        } else {
            $app = WechatPay::initialize($token);
            $response = $app->refund->byOutTradeNumber($order_id, $refund_order_id, $price * 100, $refundNumber * 100, [
                'refund_desc' => '支付欠款金额返还',
                'notify_url' => route('WxRefund_payArrears', $token),
            ]);

            if (array_get($response, 'return_code') === 'SUCCESS') {
                //修改订单状态
                $re['mes2'] = array_get($response, 'err_code_des');
                if (array_get($response, 'result_code') === 'SUCCESS') {
                    //添加退款记录
                    $refundLog = [
                        'token' => $token,
                        'status' => 0,
                        'data' => json_encode($response),
                        'order_id' => $order_id,
                        'refund_fee' => $refundNumber,
                        'total_fee' => $price,
                        'out_refund_no' => $refund_order_id
                    ];
                    ArrearsRefund::create($refundLog);
                    ArrearsOrders::where('order_id', $order_id)->update(['pay_status' => 2]);
                    $re = ['status' => true, 'mes1' => '申请退款成功', 'mes2' => '退款金额: ' . $refundNumber,];
                }
            }
        }

        return $re;
    }

    public function reapply(Request $request)
    {
//        //根据id查看状态
        $token = $request->input('token');
        $openlibService = OpenlibService::make($token);
        $id = $request->input('id');
        $re = ['status' => false, 'mes1' => '补销失败', 'mes2' => '系统繁忙,请稍后再试!'];
        $arrearsLog = ArrearsLog::where(['token' => $token, 'id' => $id, 'status' => 2])->first();
        if (!$arrearsLog) {
            $re['mes2'] = '非法操作,参数错误!';
            return $re;
        }
        $params = [
            'rdid' => $arrearsLog['rdid'],
            'tranid' => $arrearsLog['tranid'],
            'optype' => 1,
            'money' => $arrearsLog['price'],
            'moneytype' => 6
        ];
        $response = $openlibService->onefinhandle($params);
        $re['mes2'] = array_get($response, 'messagelist.0.message');
        if ($response['success'] == true) {
            $arrearsLog->status = 1;
            $arrearsLog->save();
            $re['status'] = true;
            $re['mes1'] = '操作成功';
        }
        return $re;

    }

}
