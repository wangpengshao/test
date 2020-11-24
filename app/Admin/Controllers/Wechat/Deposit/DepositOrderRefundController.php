<?php

namespace App\Admin\Controllers\Wechat\Deposit;

use App\Models\Deposit\Deposit;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Api\Helpers\ApiResponse;

class DepositOrderRefundController extends Controller
{

    use ApiResponse;

    /**
     * @time   2019/9/4
     * 接收token显示页面
     * @author wsp@xxx.com wsp
     */
    public function index(Request $request)
    {
        $deposit = Deposit::where('token', session('wxtoken'))->first();
        // 判断押金系统状态是否开启
        if ($deposit && $deposit['status']) {
            return view('admin.deposit.orderRefund', $deposit);
        } else {
            return view('admin.deposit.error');
        }
    }
}
