<?php

namespace App\Admin\Controllers\Wechat;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Form;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpandDataController extends Controller
{

    public function show(Request $request, Content $content)
    {
        $content->header('拓展数据图表');
        $content->description('....');
        $token = $request->session()->get('wxtoken');
        $type = $request->input('type', 'y');
        $time = $request->input('time', date('Ym'));
        // 初始化年月
        $year = date('Y');
        $month = date('m');
        switch ($type) {
            // 根据time值获取相应的年份跟月份
            case 'y':
                if (!empty($time)) {
                    $year = substr($time, '0', '4');
                }
                break;
            case 'm':
                $year = substr($time, '0', '4');
                $month = substr($time, '4', '2');
                break;
        }
        // 默认显示当下年份的月数据
        if ($type == 'm') {
            // 显示所选择的年份下相关数据
            // 年份下各个月份数据
            // 将该月份的相关数据全部筛选出来
//            $days = cal_days_in_month(CAL_GREGORIAN, $month, $year); // 该月的总天数
            $days = date("t", strtotime($year . $month));
            $time_between['time_start'] = Carbon::create($year, $month, 1, 0, 0, 0)->toDateTimeString();
            $time_between['time_end'] = Carbon::create($year, $month, $days, 23, 59, 59)->toDateTimeString();
            $DayData = DB::table('w_all_data')->where('token', $token)
                ->whereBetween('created_at', $time_between)
                ->orderBy('id', 'ASC')->get()->toArray();
            // 取出整个月的所有日期时间，与之前取出月份相关数据时间进行匹配筛选，最终整合数据
            $DayWhere = $this->getAllDayTime($year, $month, $days);
            foreach ($DayWhere as $key_w => $value_w) {
                foreach ($DayData as $value_d) {
                    if ($value_d->created_at == $value_w[0]) {
                        $ActiveDayData[$key_w] = $value_d->active_n;
                        $BindDayData[$key_w] = $value_d->bind_n;
                        $SaveDayData[$key_w] = $value_d->save_n;
                        $NewbindDayData[$key_w] = $value_d->newbind_n;
                        $NewsaveDayData[$key_w] = $value_d->newsave_n;
                        $log[$key_w] = $key_w; // 标记当天是否有数据
                    }
                }
                if (!isset($log[$key_w])) {
                    $ActiveDayData[$key_w] = NULL;
                    $BindDayData[$key_w] = NULL;
                    $SaveDayData[$key_w] = NULL;
                    $NewbindDayData[$key_w] = NULL;
                    $NewsaveDayData[$key_w] = NULL;
                }
            }
            $data = array(
                'ActiveDayData' => $ActiveDayData,
                'BindDayData' => $BindDayData,
                'SaveDayData' => $SaveDayData,
                'NewbindDayData' => $NewbindDayData,
                'NewsaveDayData' => $NewsaveDayData,
            );
        } else {
            // 将全年的数据全部筛选出来
            $time_between['time_start'] = Carbon::create($year, 1, 1, 0, 0, 0)->toDateTimeString();
            $time_between['time_end'] = Carbon::create($year, 12, 31, 23, 59, 59)->toDateTimeString();
            $MonthData = DB::table('w_all_data')->where('token', $token)
                ->whereBetween('created_at', $time_between)
                ->orderBy('id', 'ASC')->get()->toArray();
            $monthWhere = $this->getAllMonthTime($year);
            // 遍历一年的月份时间，将每个月的数据收集起来装在单独的数组中最后取max值
            foreach ($monthWhere as $k => $v) {
                foreach ($MonthData as $value_m) {
                    if ($v[0] <= strtotime($value_m->created_at) && strtotime($value_m->created_at) < $v[1]) {
                        $ActiveMonthData[] = $value_m->active_n;  // 48小时活跃粉丝数
                        $BindMonthData[] = $value_m->bind_n;    // 当前绑定读者数
                        $SaveMonthData[] = $value_m->save_n;  // 当前存卡数
                        $NewbindMonthData[] = $value_m->newbind_n;  // 当天新绑定数
                        $NewsaveMonthData[] = $value_m->newsave_n;  // 当天新存卡数
                    }
                }
                // 若当月存在数据,取出当月数据的最大值
                $ActiveMonthDataMax[] = !empty($ActiveMonthData) ? max($ActiveMonthData) : NULL;
                $BindMonthDataMax[] = !empty($BindMonthData) ? max($BindMonthData) : NULL;
                $SaveMonthDataMax[] = !empty($SaveMonthData) ? max($SaveMonthData) : NULL;
                $NewbindMonthDataMax[] = !empty($NewbindMonthData) ? max($NewbindMonthData) : NULL;
                $NewsaveMonthDataMax[] = !empty($NewsaveMonthData) ? max($NewsaveMonthData) : NULL;

                // 若当月存在数据,取出当月数据的平均值
                $ActiveMonthDataAg[] = !empty($ActiveMonthData) ? array_sum($ActiveMonthData) / count($ActiveMonthData) : NULL;
                $BindMonthDataAg[] = !empty($BindMonthData) ? array_sum($BindMonthData) / count($BindMonthData) : NULL;
                $SaveMonthDataAg[] = !empty($SaveMonthData) ? array_sum($SaveMonthData) / count($SaveMonthData) : NULL;
                $NewbindMonthDataAg[] = !empty($NewbindMonthData) ? array_sum($NewbindMonthData) / count($NewbindMonthData) : NULL;
                $NewsaveMonthDataAg[] = !empty($NewsaveMonthData) ? array_sum($NewsaveMonthData) / count($NewsaveMonthData) : NULL;

                // 确保每个数组中存放的数据只为当个月的数据,故将当前存放的数据销毁
                unset($ActiveMonthData);
                unset($BindMonthData);
                unset($SaveMonthData);
                unset($NewbindMonthData);
                unset($NewsaveMonthData);
            }
            $data = [
                'ActiveMonthData' => $ActiveMonthDataMax,
                'BindMonthData' => $BindMonthDataMax,
                'SaveMonthData' => $SaveMonthDataMax,
                'NewbindMonthData' => $NewbindMonthDataMax,
                'NewsaveMonthData' => $NewsaveMonthDataMax,
                // 平均值
                'ActiveMonthDataAvg' => $ActiveMonthDataAg,
                'BindMonthDataAvg' => $BindMonthDataAg,
                'SaveMonthDataAvg' => $SaveMonthDataAg,
                'NewbindMonthDataAvg' => $NewbindMonthDataAg,
                'NewsaveMonthDataAvg' => $NewsaveMonthDataAg,
            ];
        }
        $barData = [
            'data' => $data,
            'type' => $type,
            'year' => $year,
            'month' => $month,
            'time' => $time,
        ];
        return $content->row(function ($row) use ($barData, $request) {

            $row->column(6, function (Column $column) use ($barData) {
                if ($barData['type'] == 'm') {
                    $column->row(new Box($barData['year'] . '年 ' . $barData['month'] . '月数据表', view('admin.Chart.ExpandM')->with($barData)));
                } else {
                    $column->row(new Box($barData['year'] . '年全年各月峰值数据表', view('admin.Chart.ExpandY')->with($barData)));
                    $column->row(new Box($barData['year'] . '年全年各月均值数据表', view('admin.Chart.ExpandYA')->with($barData)));
                }
            });

            $row->column(6, function (Column $column) use ($barData, $request) {
                $form = new Form();
                $form->method('get');
                $form->action($request->url());
                $form->date('time', '时间')->format('YYYYMM')->default($barData['time']);
                $form->radio('type', '图表类型')->options(['y' => '年', 'm' => '月'])
                    ->stacked()->default($barData['type'])
                    ->help('注:年类型即横坐标为12个月份，月类型横坐标为月内天数');
                $column->append((new Box(' ', $form))->style('success'));
            });
        });
    }

    // 获取所有当前年份下的所有月份
    public function getAllMonthTime($year)
    {
        $monthWhere = [];
        for ($a = 1; $a <= 12; $a++) {
//            $days = cal_days_in_month(CAL_GREGORIAN, $a, $year);
            $days = date("t", strtotime($year . $a));
            $start = Carbon::create($year, $a, 1, 0, 0, 0)->timestamp;
            $end = Carbon::create($year, $a, $days, 23, 59, 59)->timestamp;
            $monthWhere[] = [$start, $end];
        }
        return $monthWhere;
    }

    // 获取所有当前年份及月份下的所有天数
    public function getAllDayTime($year, $month, $days)
    {
        // y-m-d 0-0-0 : y-m-d 23-59-59 为一天,比较也依此为依据
        $DayWhere = [];
        for ($a = 1; $a <= $days; $a++) {
            if ($a != $days) {
                $start = Carbon::create($year, $month, $a, 0, 0, 0)->toDateTimeString();
            } else {
                $start = Carbon::create($year, $month, $a, 0, 0, 0)->toDateTimeString();
            }
            $DayWhere[] = [$start];
        }
        return $DayWhere;
    }
}
