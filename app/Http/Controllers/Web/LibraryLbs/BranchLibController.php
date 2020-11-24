<?php

namespace App\Http\Controllers\Web\LibraryLbs;

use App\Models\LibraryLbs\Company;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Wechatapp;
use Illuminate\Http\Request;
use App\Api\Helpers\ApiResponse;

/**
 * 图书馆前端交互 -- 腾讯地图
 * Class BranchLibController
 * @package App\Http\Controllers\Web\LibraryLbs
 */
class BranchLibController extends Controller
{
    use ApiResponse;

    /**
     * BranchLibController constructor.
     */
    public function __construct()
    {
        if (basename($_SERVER['SCRIPT_NAME']) == 'artisan') {
            return null;
        }
        $this->middleware('RequiredToken');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $token = $request->input('token');
        $where = [
            'token' => $token,
            'is_show' => 1
        ];
        $company = Company::where($where)->orderBy('p_id')->orderBy('order')->get()
            ->each(function ($item) {
                return $item->keys = $item['lat'] . ',' . $item['lng'];
            });

        $with = [
            'app' => Wechatapp::initialize($token),
            'list' => $company,
            'key' => config('envCommon.TENCENT_MAP_KEY'),
            'defaultImg' => 'https://u.interlib.cn/uploads/k/kycufj1447117355/b/1/0/e/thumb_56580a5821d5a.jpg'
        ];
        return view('web.branchLib.index', $with);
    }
}
