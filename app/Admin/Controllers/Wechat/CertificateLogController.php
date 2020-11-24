<?php

namespace App\Admin\Controllers\Wechat;

use App\Admin\Extensions\ExcelExporter\CertificateLv1Exporter;
use App\Models\Wechat\CertificateLog;
use App\Http\Controllers\Controller;
use App\Models\Wechat\CertificateOrders;
use App\Models\Wechat\CertificateRefund;
use App\Models\Wechat\Wechatapp;
use App\Models\Wechat\WechatPay;
use App\Services\IcbcService;
use App\Services\OpenlibService;
use App\Services\PayHelper;
use EasyWeChat\Kernel\Messages\Text;
use Encore\Admin\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

class CertificateLogController extends Controller
{

    private $statusSelect = [
        0 => '失败',
        1 => '成功',
        2 => '异常',
        3 => '补办成功',
        4 => '待审核',
        5 => '审核通过',
        6 => '审核不通过',
    ];

    public function index(Content $content)
    {
        Admin::script($this->script());
        $lvType = \request()->input('lvType');
        $header = ($lvType == 1) ? '普通办证列表' : '实名办证列表';
        return $content
            ->header($header)
            ->description('description')
            ->body($this->grid($lvType));
    }

    protected function grid($lvType = 2)
    {
        $grid = new Grid(new CertificateLog);
        $grid->disableExport(false);
        $grid->exporter(new CertificateLv1Exporter($this->statusSelect));

        $grid->model()->where('token', session('wxtoken'));
        $type = ($lvType == 2) ? 1 : 0;
        $grid->model()->where('type', $type)->orderBy('created_at', 'desc');
        $grid->disableCreateButton();
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('status', '状态')->select($this->statusSelect);
                $filter->equal('rdname', '姓名');
                $filter->equal('rdcertify', '身份证');
                $filter->equal('rdtype', '读者类型')->placeholder('请输入读者类型值');
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('user', function ($query) use ($input) {
                        $query->where('nickname', 'like', '%' . $input . '%');
                    });
                }, '微信昵称', 'nickname')->inputmask([], $icon = 'wechat');

            });
            $filter->column(1 / 2, function ($filter) {
                $filter->between('created_at', '提交时间')->datetime();
                $filter->equal('is_pay', '押金方式')->radio([
                    1 => '缴费',
                    0 => '免费',
                ]);

                $filter->where(function ($query) {
                    $query->whereHas('orders', function ($query) {
                        $query->where('pay_status', $this->input);
                    });
                }, '订单状态', 'pay_status')->select([
                    -1 => '支付失败',
                    0 => '未支付',
                    1 => '已支付',
                    2 => '退款处理',
                ]);
                $filter->equal('order_id', '商户订单号');
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('orders', function ($query) use ($input) {
                        $query->where('transaction_id', $input);
                    });
                }, '微信订单号', 'transaction_id');
            });
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            $price = $actions->row->orders['price'];
            $refunds = $actions->row->refunds->toArray();
            $refunds = array_column($refunds, 'refund_fee');
            $refunds = array_sum($refunds);
            if ($actions->row->is_pay == 1) {
                $actions->append("<button class='btn btn-xs btn-success margin-r-5 checkPay' data-oid='{$actions->row->order_id}' data-type='{$actions->row->orders->pay_type}'
>核对支付</button>");
            }
            if ($refunds < $price && in_array($actions->row->orders['pay_status'], [1, 2])) {
                $re = $price - $refunds;
                $actions->append("<button class='btn btn-xs btn-danger margin-r-5 refundPay' data-oid='{$actions->row->order_id}' data-type='{$actions->row->orders->pay_type}'
 data-pr='{$price}' data-re='{$re}'>退款</button>");
            }
            if ($actions->row->status == 2) {
                $actions->append("<button class='btn btn-xs btn-warning margin-r-5 reapply' data-name='{$actions->row->name}'
data-id='{$actions->row->id}'>异常补办</button>");
            }
            if ($actions->row->status == 4) {
                $actions->append("<button class='btn btn-xs btn-primary margin-r-5 audit' data-name='{$actions->row->name}'
data-id='{$actions->row->id}'>审核</button>");
            }
        });
        $grid->column('rdname', '姓名');
        $grid->column('rdcertify', '身份证');
        $grid->column('user.nickname', '微信昵称');

        $grid->column('rdid', '办证号码')->modal('办证详细信息', function ($model) {
            $data = $model->only(['rdlib', 'rdtype', 'operator', 'rdname', 'rdcertify', 'imgData']);
            $addReaderImgOp = config('addReaderImgOp');
            $img = [];
            $otherData = [];
            $imgData = $model->imgData;
            if (!empty($imgData)) {
                foreach ($imgData as $k => $v) {
                    $img[$addReaderImgOp[$k]] = Storage::disk(config('admin.upload.disk'))->url($v);
                }
            }
            if (!empty($this->data)) {
                foreach ($this->data as $k => $v) {
                    $otherData[$k] = $v;
                }
            }
            return view('admin.Custom.custom-1', [
                'img' => $img,
                'otherDataOp' => config('addReaderOp'),
                'data' => $data,
                'otherData' => $otherData,
            ]);
        })->help('此列的值为用户的读者证号码,点击此列蓝色部分可查看证详细!');

        $grid->column('status', '办证状态')->using($this->statusSelect)->dot([
            0 => 'danger',
            1 => 'success',
            2 => 'warning',
            3 => 'info',
            4 => 'primary',
            5 => 'success',
            6 => 'danger',
        ]);
        $grid->column('orders.pay_status', '押金方式')->display(function ($pay_status) {
            if ($this->is_pay !== 1) {
                return '<span class="badge bg-gray">免付</span>';
            }
            switch ($pay_status) {
                case -1:
                    return '<span class="badge bg-brown">支付失败</span>';
                    break;
                case 1:
                    return '<span class="badge bg-green">已支付</span>';
                    break;
                case 2:
                    return '<span class="badge bg-red">退款处理</span>';
                    break;
                default:
                    return '<span class="badge bg-block">未支付</span>';
            }
        });
        $grid->column('orders.price', '押金(元)');

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

        $grid->column('支付详情')->expand(function () {
            $order = $this->toArray()['orders'];
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
            $re_order = $this->toArray()['refunds'];
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

        $grid->created_at('提交时间')->sortable();

        return $grid;
    }

    public function auditReader(Request $request)
    {
        $type = $request->input('type');
        $token = $request->input('token');
        $id = $request->input('id');
        $re = ['status' => false, 'mes1' => '出错了！', 'mes2' => '系统繁忙,请稍后再试!'];
        if (empty($type) || ($type != 'through' && $type != 'refuse')) {
            return $re;
        }
        $certificateLog = CertificateLog::where(['token' => $token, 'id' => $id, 'status' => 4])->first();
        if (!$certificateLog) {
            $re['mes2'] = '非法操作,参数错误!';
            return $re;
        }

        if ($type == 'through') {
            $openlibService = OpenlibService::make($token);
            $newReader = [
                'rdid' => $certificateLog['rdid'],
                'rdname' => $certificateLog['rdname'],
                'rdpasswd' => decrypt($certificateLog['rdpasswd']),
                'rdcertify' => $certificateLog['rdcertify'],
                'rdlib' => $certificateLog['rdlib'],
                'operator' => $certificateLog['operator'],
                'rdtype' => $certificateLog['rdtype'],
                'rdcfstate' => 1
            ];
            $newReader += $certificateLog['data'];
            if ($certificateLog['is_pay'] == 1 && $certificateLog['order_id']) {
                $order = CertificateOrders::where(['order_id' => $certificateLog['order_id'], 'token' => $token])
                    ->first(['price', 'transaction_id']);
                if ($order) {
                    $newReader['deposit'] = $order['price'];
                    $newReader['serialno'] = $order['transaction_id'];
                    $newReader['paytype'] = 6;
                }
            }
            $searchReader = $openlibService->searchreader(null, $certificateLog['rdcertify']);
            if ($searchReader['success'] == true || Arr::get($searchReader, 'messagelist.0.code') == 'R00130') {
                $re['mes2'] = '该用户已经办过证,无法重复办理!';
                return $re;
            }
            $response = $openlibService->addreader($newReader);
            $re['mes2'] = Arr::get($response, 'messagelist.0.message');

            if ($response['success'] == true) {
                $certificateLog->status = 5;
                $certificateLog->check_s = 1;
                $certificateLog->check_at = date('Y-m-d H:i:s');
                $rdid = Arr::get($response, 'messagelist.1.rdid');
                if ($rdid) {
                    $certificateLog->rdid = $rdid;
                    $re['mes2'] = Arr::get($response, 'messagelist.1.message') . '</br>' . '证号:' . Arr::get($response, 'messagelist.1.rdid');
                }
                $certificateLog->save();
                $re['status'] = true;
                $re['mes1'] = '审核完成';
                //进行微信消息通知
                if (isset($certificateLog['openid'])) {
                    $app = Wechatapp::initialize($token);
                    $text = "办证成功通知\n\n您提交的申请已经通过审核啦\n\n证号: " . $certificateLog->rdid . "\n\n请妥善保管好!";
                    $app->customer_service->message(new Text($text))->to($certificateLog['openid'])->send();
                }
            }
            return $re;
        }
        $info = $request->input('info', '');
        $certificateLog->check_info = $info;
        $certificateLog->status = 6;
        $certificateLog->check_s = 1;
        $certificateLog->check_at = date('Y-m-d H:i:s');
        $certificateLog->save();
        //进行微信消息通知
        if (isset($certificateLog['openid'])) {
            $app = Wechatapp::initialize($token);
            $text = "办证失败通知\n\n抱歉,您提交的申请未能通过审核\n\n";
            if ($info) {
                $text .= "原因:" . $info;
            }
            $app->customer_service->message(new Text($text))->to($certificateLog['openid'])->send();
        }
        $re = ['status' => true, 'mes1' => '审核完成', 'mes2' => ''];
        return $re;
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

        } else {
            $app = WechatPay::initialize($token);
            $response = $app->order->queryByOutTradeNumber($order_id);
            if (Arr::get($response, 'return_code') === 'SUCCESS') {
                if (Arr::get($response, 'trade_state') === 'SUCCESS') {
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
        $re = ['status' => false, 'mes1' => '退款失败', 'mes2' => '系统繁忙,请稍后再试!'];
        $refund_order_id = $payHelper->GenerateOrderNumber('BZYJ-TK');

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
                    'out_refund_no' => $refund_order_id
                ];
                CertificateRefund::create($refundLog);
                CertificateOrders::where('order_id', $order_id)->update(['pay_status' => 2]);
                $re = ['status' => true, 'mes1' => '申请退款成功', 'mes2' => '退款金额: ' . $refundNumber,];
            } else {
                $re = ['status' => false, 'mes1' => '申请退款失败', 'mes2' => array_get($response, 'return_msg')];
            }

        } else {
            $app = WechatPay::initialize($token);
            $response = $app->refund->byOutTradeNumber($order_id, $refund_order_id, $price * 100, $refundNumber * 100, [
                'refund_desc' => '退押金',
                'notify_url' => route('WxRefund_certificate', $token),
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
                    CertificateRefund::create($refundLog);
                    CertificateOrders::where('order_id', $order_id)->update(['pay_status' => 2]);
                    $re = ['status' => true, 'mes1' => '申请退款成功', 'mes2' => '退款金额: ' . $refundNumber,];
                }
            }
        }

        return $re;

    }

    public function reapplyReader(Request $request)
    {
        //根据id查看状态
        $token = $request->input('token');
        $openlibService = OpenlibService::make($token);
        $id = $request->input('id');
        $re = ['status' => false, 'mes1' => '补办失败', 'mes2' => '系统繁忙,请稍后再试!'];
        $certificateLog = CertificateLog::where(['token' => $token, 'id' => $id, 'status' => 2])->first();
        if (!$certificateLog) {
            $re['mes2'] = '非法操作,参数错误!';
            return $re;
        }
        $newReader = [
            'rdid' => $certificateLog['rdid'],
            'rdname' => $certificateLog['rdname'],
            'rdpasswd' => decrypt($certificateLog['rdpasswd']),
            'rdcertify' => $certificateLog['rdcertify'],
            'rdlib' => $certificateLog['rdlib'],
            'operator' => $certificateLog['operator'],
            'rdtype' => $certificateLog['rdtype'],
            'rdcfstate' => 1
        ];
        $newReader += $certificateLog['data'];

        if ($certificateLog['is_pay'] == 1 && $certificateLog['order_id']) {
            $order = CertificateOrders::where(['order_id' => $certificateLog['order_id'], 'token' => $token])
                ->first(['price', 'transaction_id']);
            if ($order) {
                $newReader['deposit'] = $order['price'];
                $newReader['serialno'] = $order['transaction_id'];
                $newReader['paytype'] = 6;
            }
        }

        $searchReader = $openlibService->searchreader(null, $certificateLog['rdcertify']);
        if ($searchReader['success'] == true || array_get($searchReader, 'messagelist.0.code') == 'R00130') {
            $re['mes2'] = '该用户已经办过证,无法重复办理!';
            return $re;
        }
        $response = $openlibService->addreader($newReader);
        $re['mes2'] = array_get($response, 'messagelist.0.message');

        if ($response['success'] == true) {
            $certificateLog->status = 3;
            $rdid = array_get($response, 'messagelist.1.rdid');
            if ($rdid) {
                $certificateLog->rdid = $rdid;
                $re['mes2'] = array_get($response, 'messagelist.1.message') . '</br>' . '证号:' . array_get($response, 'messagelist.1.rdid');
            }
            $certificateLog->save();
            $re['status'] = true;
            $re['mes1'] = '补办成功';
        }
        return $re;

    }

    protected function script()
    {
        $checkPayUrl = route('wechat.certificate.checkPay');
        $refundPayUrl = route('wechat.certificate.refundPay');
        $reapplyReaderUrl = route('wechat.certificate.reapplyReader');
        $auditReaderUrl = route('wechat.certificate.auditReader');

        $token = session('wxtoken');
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
         inputValidator: function(value) {
         
         return new Promise(function(resolve, reject) {
             if(!value){
               reject('退款金额不能为空');
             }
             if( value > re_price){
                reject('退款金额不能大于可退款金额!');
             }
             resolve();
            
                });
              }
         }).then(result => {
         console.log(result)
                  $.ajax({
                        url: "{$refundPayUrl}",
                        type: "post",
                        data: {
                            "_token": LA.token,
                            "order_id":order_id,
                            "token":"{$token}",
                            "refundNumber":result,
                            "price":price,
                            "pay_type":pay_type,
                        },
                        dataType: "json",
                        success: function (data) {
                          if(data.status==true){
                            swal(data.mes1,data.mes2,'success');
                            $.pjax.reload('#pjax-container');
                          }else{
                            swal(data.mes1,data.mes2,'error');
                          }
                        },
                        error:function(){
                         swal("哎呦……", "出错了！","error");
                        }
                    });
     
    }).catch(swal.noop)


});

$('.reapply').on('click', function () {
    var id=$(this).data('id');
    var name=$(this).data('name');
    swal({
         title: '确认要为'+name+'补办证件?',
         type: 'warning',
         text:'提示:请先核实支付状态和办证详情内容无误之后再进行操作！',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$reapplyReaderUrl}",
                        type: "post",
                        data: 
                        {"_token": LA.token,
                        "id":id,
                        "token":"{$token}"
                        },
                        dataType: "json",
                        success: function (data) {
                           if(data.status==true){
                            swal(data.mes1,data.mes2,'success');
                            $.pjax.reload('#pjax-container');
                          }else{
                            swal(data.mes1,data.mes2,'error');
                          }
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

$('.audit').on('click', function () {
    var id=$(this).data('id');
    swal({
      title: '提示', 
      text: '先考虑清楚哦,点击右上角X可以取消!', 
      type: 'warning',
      showCancelButton: true, 
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: '通过', 
      cancelButtonText: '不通过',
      showCloseButton: true,
      allowOutsideClick: false,
      showLoaderOnConfirm: true,
      preConfirm: () => {
            let data={_token: LA.token,"token":"{$token}","type":"through","id":id};
            let headers={'Content-Type': 'application/json'};
            return fetch('{$auditReaderUrl}' ,{method: 'POST',body: JSON.stringify(data),headers: new Headers(headers)}
            ).then(response => {
                 if (!response.ok) {
                      throw new Error(response.statusText)
                 }
                 return response.json()
            }).catch(error => {throw new Error(error);})
      }
    }).then(function(response) {
         if(response.status ==false){
           return swal(response.mes1,response.mes2,'error');
         }
       $.pjax.reload('#pjax-container');
       swal(response.mes1,response.mes2,'success');
    }, function(dismiss) {
      //审核不通过
      if (dismiss === 'cancel') {
             swal({
              input: 'textarea',
              title: '原因', 
              text: '可以备注不通过的原因,也可以留空不填', 
              showCloseButton: true,
              allowOutsideClick: false,
              showLoaderOnConfirm: true,
              preConfirm: (info) => {
                    let data={_token: LA.token,"token":"{$token}","type":"refuse","id":id,"info":info};
                    let headers={'Content-Type': 'application/json'};
                    return fetch('{$auditReaderUrl}' ,{method: 'POST',body: JSON.stringify(data),headers: new Headers(headers)}
                    ).then(response => {
                         if (!response.ok) {
                              throw new Error(response.statusText)
                         }
                         return response.json()
                    }).catch(error => {throw new Error(error);})
              }
            }).then(function (response) {
                  if(response.status ==false){
                   return swal(response.mes1,response.mes2,'error');
                 }
               $.pjax.reload('#pjax-container');
               swal(response.mes1,response.mes2,'success');
            })
      } 
      
    }).catch(swal.noop);
    
});
SCRIPT;
    }

}
