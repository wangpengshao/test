<?php

namespace App\Admin\Controllers\Wechat\Recommend;

use App\Admin\Controllers\CustomView\OnlyInfo;
use App\Http\Controllers\Controller;
use App\Models\Recommend\RecommendBooks;
use App\Models\Recommend\RecommendIsbn;
use App\Models\Recommend\Isbn;
use Carbon\Carbon;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;
use Illuminate\Support\Facades\DB;

/**
 * 书单统计
 * Class RecommendBooksController
 * @package App\Admin\Controllers\Wechat\Recommend
 */
class BookStatisticsController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('书单统计数据');
            $content->description('...');
            $content->row(function ($row) {
                $where = [
                    'token' => \request()->session()->get('wxtoken')
                ];
                $bookCount = RecommendBooks::where($where)->count('id');
                $row->column(4, new OnlyInfo('本馆书单总期数', 'book', 'green', '', $bookCount));

                $booksCount = Isbn::where($where)->whereNotNull('id')->count('id');
                $row->column(4, new OnlyInfo('本馆书籍总数', 'book', 'yellow', '', $booksCount));

                $booksCount = RecommendIsbn::where($where)->whereNotNull('c_id')->count('c_id');
                /*$rBooksCount = RepeatIsbn::where($where)->whereNotNull('c_id')->count('c_id');
                $cBooksCount = $booksCount + $rBooksCount;*/
                $row->column(4, new OnlyInfo('收藏其他馆书籍的总数', 'book', 'blue', '', $booksCount));

                // 导入书单数目的数据走向

                $token = \request()->session()->get('wxtoken');
                // 初始化年月
                $year = date('Y');
                // 将全年的数据全部筛选出来
                $time_between['time_start'] = Carbon::create($year, 1, 1, 0, 0, 0)->toDateTimeString();
                $time_between['time_end'] = Carbon::create($year, 12, 31, 23, 59, 59)->toDateTimeString();
                $SdData = DB::table('w_recommend_sd')->where('token', $token)
                    ->whereBetween('created_at', $time_between)
                    ->orderBy('id', 'ASC')->get()->toArray();
                $ColData = DB::table('w_recommend_isbn')->where('token', $token)
                    ->whereBetween('created_at', $time_between)
                    ->whereNotNull('c_id')
                    ->orderBy('c_id', 'ASC')->get()->toArray();
                $SjData = DB::table('w_isbn')->where('token', $token)
                    ->whereBetween('created_at', $time_between)
                    ->whereNotNull('id')
                    ->orderBy('id', 'ASC')->get()->toArray();
                $monthWhere = $this->getAllMonthTime($year);
                // 遍历一年的月份时间，将每个月的数据收集起来装在单独的数组中最后取max值
                foreach ($monthWhere as $k => $v) {
                    // 初始化数据
                    $SdMonthData = 0; // 导入的书单总数
                    $ColMonthData = 0; // 收藏其他馆书籍的总数
                    $SjMonthData = 0; // 该馆书籍的总数
                    foreach ($SdData as $value_m) {
                        if ($v[0] <= strtotime($value_m->created_at) && strtotime($value_m->created_at) < $v[1]) {
                            ++$SdMonthData; // 导入的书单数自增
                        }
                    }
                    foreach ($ColData as $value_c) {
                        if ($v[0] <= strtotime($value_c->created_at) && strtotime($value_c->created_at) < $v[1]) {
                            ++$ColMonthData; // 收藏其他馆书籍的数自增
                        }
                    }
                    foreach ($SjData as $value_s) {
                        if ($v[0] <= strtotime($value_s->created_at) && strtotime($value_s->created_at) < $v[1]) {
                            ++$SjMonthData; // 该馆书籍的数自增
                        }
                    }
                    $SdMonthDataM[] = !empty($SdMonthData) ? $SdMonthData : 0;
                    $ColMonthDataM[] = !empty($ColMonthData) ? $ColMonthData : 0;
                    $SjMonthDataM[] = !empty($SjMonthData) ? $SjMonthData : 0;
                }
                $data = [
                    'SdData' => $SdMonthDataM,
                    'ColData' => $ColMonthDataM,
                    'SjData' => $SjMonthDataM,
                ];
                $barData = [
                    'data' => $data,
                    'year' => date('Y')
                ];
                $row->column(6, function (Column $column) use ($barData) {
                    $column->row(new Box($barData['year'] . '年全年各月总值数据表', view('admin.Chart.SdDataY')->with($barData)));
                });
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
}