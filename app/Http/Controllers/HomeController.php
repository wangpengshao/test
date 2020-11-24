<?php

namespace App\Http\Controllers;

use App\Models\Wxuser;
use Illuminate\Http\Request;

/**
 * Class HomeController
 * @package App\Http\Controllers
 */
class HomeController extends Controller
{

    /**
     * HomeController constructor.
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }


    public function index()
    {
        return view('Index');
    }


    public function txtVerify(Request $request)
    {
        $verify = $request->route('verify');
        return $verify;
    }

    public function searchWxuser(Request $request)
    {
        $secretCode = $request->input('secretCode');
        if (empty($secretCode) || $secretCode != 'openSesame') {
            abort(404);
        }
        $showField = [
            'wxname',
            'token',
            'type',
            'appid',
            'libcode',
            'glc',
            'opacurl',
            'openlib_url',
            'activity_url',
            'opcs_url',
            'ushop_url',
            'sso_url',
            'activity_sw',
            'guesslike_sw',
            'newbook_sw',
            'yujie_sw',
            'yuyue_sw',
            'qr_type',
            'knowledge_url',
            'qr_code',
            'auth_type',
            'is_cluster'
        ];
        $postUrl = route('public::searchWxuser', ['secretCode' => 'openSesame']);
        $searchData = [];
        $searchKey = null;
        if ($request->isMethod('post')) {
            $searchKey = $request->input('searchKey');
            //判断是否是中文 中文则检索名称
            if (preg_match('/[\x{4e00}-\x{9fa5}]/u', $searchKey) > 0) {
                $searchData = Wxuser::where('wxname', 'like', '%' . $this->escape_like_str($searchKey) . '%')->get($showField);
            } else {
                $searchData = Wxuser::where('token', $searchKey)->get($showField);
            }
        }

        return view('searchwx')->with([
            'postUrl' => $postUrl,
            'searchData' => $searchData,
            'searchKey' => $searchKey
        ]);

    }

    private function escape_like_str($str)
    {
        $like_escape_char = '!';
        return str_replace([$like_escape_char, '%', '_'], [
            $like_escape_char . $like_escape_char,
            $like_escape_char . '%',
            $like_escape_char . '_',
        ], $str);
    }

}
