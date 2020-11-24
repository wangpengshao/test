<?php

namespace App\Admin\Controllers\Wechat\Seat;

use App\Models\Seat\SeatByBooking;
use App\Models\Seat\SeatByScan;
use App\Models\Seat\SeatCurrBooking;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Layout\Column;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;


class SeatDataController extends Controller
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
        return Admin::content(function (Content $content) {

            $content->header('数据统计');
            $content->description('座位预约');

            $content->row(function (Row $row) {
                $row->column(12, function (Column $column) {

                    //扫码入座数据
                    $year = date('Y-01-01 00:00:00');
                    $month = date('Y-m-01 00:00:00');
                    $today = date('Y-m-d 00:00:00');
                    $allLog = 0;
                    $yearLog = 0;
                    $monthLog = 0;
                    $todayLog = 0;
                    $downWeekData = [];

                    $weekDate = [];
                    for($i=0; $i<=6; $i++){
                        $date = date('Y-m-d',strtotime("-$i day"));
                        $weekDate[$date]['start'] = $date . ' 00:00:00';
                        $weekDate[$date]['end'] = $date . ' 23:59:59';
                    }

                    $SeatDownLog = SeatByScan::where('token',session('wxtoken'))->get(['s_time','e_time']);
                    foreach($SeatDownLog as $value){
                        $allLog += 1;
                        if($value['s_time'] >= $year){
                            $yearLog += 1;
                        }
                        if ($value['s_time'] >= $month){
                            $monthLog += 1;
                        }
                        if ($value['s_time'] >= $today){
                            $todayLog += 1;
                        }
                        foreach ($weekDate as $key => $val){
                            if(!isset($downWeekData[$key])) $downWeekData[$key] = 0;
                            if($value['s_time'] >= $val['start'] && $value['s_time'] <= $val['end']){
                                $downWeekData[$key] +=1;
                            }
                        }
                    }

                    //预约数据
                    //当前
                    $bookingNum = SeatCurrBooking::where('token',session('wxtoken'))->count();
                    //取消
                    $bookingNum_q = 0;
                    //成功
                    $bookingNum_c = 0;
                    //违约
                    $bookingNum_w = 0;
                    //一周
                    $bookingWeekData = [];

                    $SeatBookingLog = SeatByBooking::where('token',session('wxtoken'))->get(['status', 's_time', 'e_time']);
                    foreach ($SeatBookingLog as $value){
                        if($value['status'] == 0){
                            $bookingNum_q += 1;
                        }
                        elseif ($value['status'] == 1){
                            $bookingNum_c += 1;
                        }
                        elseif ($value['status'] == 2){
                            $bookingNum_w += 1;
                        }

                        foreach ($weekDate as $key => $val){
                            if(!isset($bookingWeekData[$key])) $bookingWeekData[$key] = 0;
                            if($value['s_time'] >= $val['start'] && $value['s_time'] <= $val['end']){
                                $bookingWeekData[$key] +=1;
                            }
                        }
                    }

                    $data = [
                        'allLog' => $allLog,
                        'yearLog' => $yearLog,
                        'monthLog' => $monthLog,
                        'todayLog' => $todayLog,
                        'bookingNum' => $bookingNum,
                        'bookingNum_q' => $bookingNum_q,
                        'bookingNum_c' => $bookingNum_c,
                        'bookingNum_w' => $bookingNum_w,
                        'downWeekData' => json_encode($downWeekData),
                        'bookingWeekData' => json_encode($bookingWeekData)
                    ];

                    $column->append(view('admin.diy.seatData', ['data'=>$data]));
                });
            });
        });
    }

}