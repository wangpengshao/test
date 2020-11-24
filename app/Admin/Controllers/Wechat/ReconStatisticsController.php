<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\ArrearsLog;
use App\Models\Wechat\ArrearsOrders;
use App\Models\Wechat\ArrearsRefund;
use App\Models\Wechat\DfArrearsLog;
use App\Models\Wechat\DfArrearsOrders;
use App\Http\Controllers\Controller;
use App\Models\Wechat\DfArrearsRefund;
use Encore\Admin\Widgets\Form;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Facades\DB;

class ReconStatisticsController extends Controller
{
    /**
     * time  2019.9.17.
     *
     * @content  显示统计页面
     *
     * @author  wsp
     */
    public function index(Content $content)
    {
        $content->row(function (Row $row) {
            $row->column(9, function (Column $column) {
                $form = new Form();
                $form->tools(function (Form\Tools $tools) {
                    // 去掉`列表`按钮
                    $tools->disableList();
                });
                $form->footer(function ($footer) {
                    // 去掉`继续编辑`checkbox
                    $footer->disableEditingCheck();
                });
                $statistUrl = route('wechat.dfArrears.statistics');
                $form->setAction($statistUrl);
                $form->method('get');
                $form->dateRange('start_time', 'end_time', '请选择时间');
                $form->radio('pay_type', '支付类型')->options(['0' => '欠款支付', '1' => '代付欠款']);
                $column->append(new Box("筛选", $form));
            });
            $row->column(9, function (Column $column) {
                $filter['start'] = \request('start_time', '');
                $filter['end'] = \request('end_time', '');
                $filter['pay_type'] = \request('pay_type', '0');
                // 判断是否有时间/付款方式筛选条件,有则增加条件筛选。默认为欠款支付
                if ($filter['pay_type'] == 0) {
                    // 欠款支付
                    if (!empty($filter['start']) && !empty($filter['end'])) {
                        $payCount = ArrearsOrders::where(['token' => session('wxtoken'), 'pay_status' => 1])
                            ->whereBetween('created_at', [$filter['start'], $filter['end']])->count(); // 已支付的笔数

                        $paycancelCount = DB::table('financial_arrears_orders')
                            ->join('financial_arrears_log', function ($join) {
                                $join->on('financial_arrears_orders.order_id', '=', 'financial_arrears_log.order_id')
                                    ->where(['financial_arrears_orders.token' => session('wxtoken'), 'financial_arrears_orders.pay_status' => 2, 'financial_arrears_log.status' => 1]);
                            })->whereBetween('financial_arrears_orders.created_at', [$filter['start'], $filter['end']])
                            ->count();// 已支付并成功销账笔数(关联financial_arrears_log表中的status销账状态值)

                        $cancelCount = ArrearsLog::where(['token' => session('wxtoken'), 'status' => 1])
                            ->whereBetween('created_at', [$filter['start'], $filter['end']])->count();// 成功销账的笔数
                        $cancelCountfail = ArrearsLog::where(['token' => session('wxtoken'), 'status' => 2])
                            ->whereBetween('created_at', [$filter['start'], $filter['end']])->count();// 销账失败的笔数
                        $payCountNum = ArrearsOrders::where(['token' => session('wxtoken'), 'pay_status' => 1])
                            ->whereBetween('created_at', [$filter['start'], $filter['end']])->sum('price');// 成功支付总额
                        $cancelCountNum = ArrearsRefund::where(['token' => session('wxtoken'), 'status' => 1])
                            ->whereBetween('created_at', [$filter['start'], $filter['end']])->sum('total_fee');// 成功退款总额
                    } else {
                        $payCount = ArrearsOrders::where(['token' => session('wxtoken'), 'pay_status' => 1])->count(); // 已支付的笔数

                        $paycancelCount = DB::table('financial_arrears_orders')
                            ->join('financial_arrears_log', function ($join) {
                                $join->on('financial_arrears_orders.order_id', '=', 'financial_arrears_log.order_id')
                                    ->where(['financial_arrears_orders.token' => session('wxtoken'), 'financial_arrears_orders.pay_status' => 1, 'financial_arrears_log.status' => 1]);
                            })->count();// 已支付并成功销账笔数(关联financial_arrears_log表中的status销账状态值)

                        $cancelCount = ArrearsLog::where(['token' => session('wxtoken'), 'status' => 1])->count();// 成功销账的笔数
                        $cancelCountfail = ArrearsLog::where(['token' => session('wxtoken'), 'status' => 2])->count();// 销账失败的笔数
                        $payCountNum = ArrearsOrders::where(['token' => session('wxtoken'), 'pay_status' => 1])->sum('price');// 成功支付总额
                        $cancelCountNum = ArrearsRefund::where(['token' => session('wxtoken'), 'status' => 1])->sum('total_fee');// 成功退款总额
                    }
                    $data = array(
                        'payCount' => $payCount,
                        'paycancelCount' => $paycancelCount,
                        'cancelCount' => $cancelCount,
                        'cancelCountfail' => $cancelCountfail,
                        'payCountNum' => $payCountNum,
                        'cancelCountNum' => $cancelCountNum
                    );
                    $html = view('admin.ReconStatistics.index', $data)->render();
                    $column->append(new Box("", $html));
                }
                if ($filter['pay_type'] == 1) {
                    // 代付欠款
                    if (!empty($filter['start']) && !empty($filter['end'])) {
                        $dfpayCount = DfArrearsOrders::where(['token' => session('wxtoken'), 'pay_status' => 1])
                            ->whereBetween('created_at', [$filter['start'], $filter['end']])->count(); // 已代付的笔数

                        $dfpaycancelCount = DB::table('financial_df_arrears_orders')
                            ->join('financial_df_arrears_log', function ($join) {
                                $join->on('financial_df_arrears_orders.order_id', '=', 'financial_df_arrears_log.order_id')
                                    ->where(['financial_df_arrears_orders.token' => session('wxtoken'), 'financial_df_arrears_orders.pay_status' => 2, 'financial_df_arrears_log.status' => 1]);
                            })->whereBetween('financial_df_arrears_orders.created_at', [$filter['start'], $filter['end']])
                            ->count();// 代付并且成功销账的笔数

                        $dfcancelCount = DfArrearsLog::where(['token' => session('wxtoken'), 'status' => 1])
                            ->whereBetween('created_at', [$filter['start'], $filter['end']])->count();// 代付销账成功的笔数
                        $dfcancelCountfail = DfArrearsLog::where(['token' => session('wxtoken'), 'status' => 2])
                            ->whereBetween('created_at', [$filter['start'], $filter['end']])->count();// 代付销账失败的笔数
                        $dfpayCountNum = DfArrearsOrders::where(['token' => session('wxtoken'), 'pay_status' => 1])
                            ->whereBetween('created_at', [$filter['start'], $filter['end']])->sum('price');// 代付成功支付总额
                        $dfcancelCountNum = DfArrearsRefund::where(['token' => session('wxtoken'), 'status' => 1])
                            ->whereBetween('created_at', [$filter['start'], $filter['end']])->sum('total_fee');// 代付成功退款总额

                    } else {
                        // 代付
                        $dfpayCount = DfArrearsOrders::where(['token' => session('wxtoken'), 'pay_status' => 1])->count(); // 已代付的笔数

                        $dfpaycancelCount = DB::table('financial_df_arrears_orders')
                            ->join('financial_df_arrears_log', function ($join) {
                                $join->on('financial_df_arrears_orders.order_id', '=', 'financial_df_arrears_log.order_id')
                                    ->where(['financial_df_arrears_orders.token' => session('wxtoken'), 'financial_df_arrears_orders.pay_status' => 1, 'financial_df_arrears_log.status' => 1]);
                            })->count();// 代付并且成功销账的笔数

                        $dfcancelCount = DfArrearsLog::where(['token' => session('wxtoken'), 'status' => 1])->count();// 代付销账成功的笔数
                        $dfcancelCountfail = DfArrearsLog::where(['token' => session('wxtoken'), 'status' => 2])->count();// 代付销账失败的笔数
                        $dfpayCountNum = DfArrearsOrders::where(['token' => session('wxtoken'), 'pay_status' => 1])->sum('price');// 代付成功支付总额
                        $dfcancelCountNum = DfArrearsRefund::where(['token' => session('wxtoken'), 'status' => 1])->sum('total_fee');// 代付成功退款总额
                    }
                    $data = array(
                        'dfpayCount' => $dfpayCount,
                        'dfpaycancelCount' => $dfpaycancelCount,
                        'dfcancelCount' => $dfcancelCount,
                        'dfcancelCountfail' => $dfcancelCountfail,
                        'dfpayCountNum' => $dfpayCountNum,
                        'dfcancelCountNum' => $dfcancelCountNum
                    );
                    $html = view('admin.ReconStatistics.index', $data)->render();
                    $column->append(new Box("", $html));
                }
            });
        });
        return $content;
    }
}
