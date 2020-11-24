<?php

namespace App\Http\Controllers\Web\InvitationStar;

use App\Http\Controllers\Controller;
use App\Models\Wechat\QrCodeConfig;
use App\Models\Wechat\QrCodeList;
use App\Models\Wechat\QrCodeLog;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use App\Services\WechatOAuth;
use Carbon\Carbon;
use Illuminate\Http\Request;


class IndexController extends Controller
{
    public function __construct()
    {
        if (basename($_SERVER['SCRIPT_NAME']) == 'artisan') {
            return null;
        }
        $this->middleware('RequiredToken');
    }

    public function index(Request $request)
    {
        $token = $request->input('token');
        //config未开启
        $qrConfig = QrCodeConfig::where(['token' => $token, 'status' => 1])->first();
        if (empty($qrConfig)) {
            dd('抱歉,功能尚未开启');
        }
        $WechatOAuth = WechatOAuth::make($token);
        $fansInfo = $WechatOAuth->webOAuth($request);
//        $fansInfo['openid'] = 'ofgxfuNP2fguUNsaeNdrbCKJvMBE';
        $reader = Reader::checkBind($fansInfo['openid'], $token)->first();
        if (empty($reader)) {
            dd('抱歉,需要绑定读者证才能访问');
        }

        $qrTask = $qrConfig->hasOneTask;
        if (!empty($qrTask) && Carbon::now()->between(Carbon::parse($qrTask['s_time']), Carbon::parse($qrTask['e_time']))) {
            dd('抱歉，当前活动时间已结束!!');
        }
        //判断是否已经生成过了 t_id  token rdid  status
        $where = [
            'token' => $token,
            'rdid' => $reader['rdid'],
            'type' => $qrConfig['type'],
            't_id' => $qrConfig['t_id']
        ];
        $first = QrCodeList::where($where)->first(['expire_at', 'status', 'ticket', 'url', 'type']);
        if (!empty($first)) {
            if ($first['status'] != 1 || (Carbon::now()->gt($first['expire_at']) && $first['type'] !== 1)) {
                $app = Wechatapp::initialize($token);
                $qrData = [
                    'rdid' => $reader['rdid'],
                    't_id' => $qrConfig['t_id'],
                    'type' => 'task'
                ];
                $s = Carbon::now()->addDays($qrConfig['days'])->diffInSeconds();
                $expire_at = Carbon::now()->addSecond($s)->toDateTimeString();
                if ($qrConfig['type'] === 1) {
                    //永久二维码
                    $response = $app->qrcode->forever(json_encode($qrData));
                    $qrData += [
                        'type' => 1
                    ];
                } else {
                    //临时二维码  type=0
                    $response = $app->qrcode->temporary(json_encode($qrData), $s);
                    $qrData += [
                        'expire_at' => $expire_at,
                        'type' => 0
                    ];
                }

                $qrData += [
                    'token' => $token,
                    'ticket' => $response['ticket'],
                    'url' => $response['url'],
                    'status' => 1,
                ];
                QrCodeList::create($qrData);
                $first = $qrData;
            }
        }

        $qrCodeLogId = QrCodeLog::where(['token' => $token, 'rdid' => $reader['rdid']])->with('fans')->get();
        return view('web.InvitationStar.index', [
            'bindQrReader' => $first,
            'qrCodeLogId' => $qrCodeLogId,
        ]);
    }

}
