<?php

namespace App\Http\Controllers\Api;

use App\Api\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use App\Models\Wxuser;
use App\Services\EsBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;


/**
 * 公司内部接口列表
 *
 * Class CompanyController
 *
 * @package App\Http\Controllers\Api
 */
class CompanyController extends Controller
{
    use ApiResponse;


    /**
     * 智慧墙 - 粉丝数量获取接口
     * @param Request $request
     * @return mixed
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     */
    public function zhqGetFansInfo(Request $request)
    {
        if (!$request->filled(['token', 'sign'])) {
            return $this->message('缺少参数', false);
        }
        $sign = $request->input('sign');
        $token = $request->input('token');

        if ($sign != md5($token . config('envCommon.MENU_ENCRYPT_STR') . date('Ymd'))) {
            return $this->message('sign无效', false);
        }

        $now = Carbon::now();
        $startOfDay = (string)$now->startOfDay();
        $endOfDay = (string)$now->endOfDay();
        $startOfMonth = (string)$now->startOfMonth();
        $endOfMonth = (string)$now->endOfMonth();

        $dayResponse = EsBuilder::index('wechat_event_record')
            ->whereBetween('created_at', [$startOfDay, $endOfDay])
            ->whereTerm('token', $token)
            ->customSearch([
                "size" => 0,
                "aggs" => [
                    'type_terms' => [
                        'terms' => [
                            'field' => 'type',
                        ]
                    ]
                ],
            ]);
        $dayBuckets = $dayResponse['aggregations']['type_terms']['buckets'];
        $daySubNum = 0;
        $dayUnSubNum = 0;
        if (count($dayBuckets) > 0) {
            foreach ($dayBuckets as $k => $v) {
                if ($v['key'] == 1) {
                    $daySubNum = $v['doc_count'];
                }
                if ($v['key'] == 2) {
                    $dayUnSubNum = $v['doc_count'];
                }
            }
        }
        $monthResponse = EsBuilder::index('wechat_event_record')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereTerm('token', $token)
            ->customSearch([
                "size" => 0,
                "aggs" => [
                    'type_terms' => [
                        'terms' => [
                            'field' => 'type',
                        ]
                    ]
                ],
            ]);
        $monthBuckets = $monthResponse['aggregations']['type_terms']['buckets'];
        $monthSubNum = 0;
        $monthUnSubNum = 0;
        if (count($monthBuckets) > 0) {
            foreach ($monthBuckets as $k => $v) {
                if ($v['key'] == 1) {
                    $monthSubNum = $v['doc_count'];
                }
                if ($v['key'] == 2) {
                    $monthUnSubNum = $v['doc_count'];
                }
            }
        }
        $todayNum = $daySubNum - $dayUnSubNum;
        $todayNum = ($todayNum > 0) ? $todayNum : 0;
        $monthNum = $monthSubNum - $monthUnSubNum;
        $monthNum = ($monthNum > 0) ? $monthNum : 0;

        $app = Wechatapp::initialize($token);
        $list = $app->user->list($nextOpenId = null);
        $total = Arr::get($list, 'total');

        unset($list);
        $cacheKey = 'fans:record:' . $token;

        if (empty($total)) {
            $total = Cache::get($cacheKey);
        } else {
            Cache::forever($cacheKey, $total);
        }

        $response = [
            'total' => $total,
            'todayNum' => $todayNum,
            'monthNum' => $monthNum,
        ];
        return $this->success($response, true);
    }

    /**
     * 公司内部绑定接口 - 开采使用中
     * @param Request $request
     * @return mixed
     */
    public function internalBindReader(Request $request)
    {
        if (!$request->filled(['token', 'sign', 'openid', 'rdid', 'password'])) {
            return $this->message('lack of parameter', false);
        }
        [
            'token' => $token,
            'sign' => $sign,
            'openid' => $openid,
            'rdid' => $rdid,
            'password' => $password
        ] = $request->input();

        $name = $request->input('name', '');

        if ($sign != md5($token . config('envCommon.MENU_ENCRYPT_STR') . date('Ymd'))) {
            return $this->message('sign is invalid', false);
        }

        $doesntExist = Wxuser::where('token', $token)->doesntExist();
        if ($doesntExist) {
            return $this->message('token is invalid', false);
        }
        //检查此openid是否已绑定
        $currentBind = Reader::checkBind($openid, $token)->first();
        if ($currentBind) {
            //判断证号是否相同
            if ($currentBind['rdid'] == $rdid) {
                $currentBind->name = $name;
                $currentBind->save();
                return $this->message('绑定成功', true);
            }
            //不相同进行解绑
            $log = [
                'token' => $token,
                'openid' => $openid,
                'rdid' => $rdid,
                'created_at' => date('Y-m-d H:i:s'),
                'type' => 0
            ];
            DB::table('admin_wechat_reader_log')->insert($log);
            $currentBind->is_bind = 0;
            $currentBind->save();
        }
        //查看该证号是否已被绑
        $rdidCurrentBind = Reader::rdidGetBind($rdid, $token)->first();
        if ($rdidCurrentBind) {
            //进行解绑
            $rdidCurrentBind->is_bind = 0;
            $rdidCurrentBind->save();
        }
        //新增绑定
        $create = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $rdid,
            'password' => encrypt($password),
            'is_bind' => 1,
            'name' => $name
        ];
        $status = Reader::create($create);
        if ($status == false) {
            return $this->internalError('服务器繁忙，请稍后再试!');
        }
        $log = [
            'token' => $token,
            'openid' => $openid,
            'rdid' => $status['rdid'],
            'created_at' => date('Y-m-d H:i:s'),
            'type' => 1
        ];
        DB::table('admin_wechat_reader_log')->insert($log);

        return $this->message('绑定成功', true);
    }

    public function getBindReader(Request $request)
    {
        if (!$request->filled(['openid', 'token'])) {
            return $this->message('缺少必填参数', false);
        }
        $token = $request->input('token');
        $openid = $request->input('openid');
        $reader = Reader::checkBind($openid, $token)->first(['id', 'rdid', 'name']);
        if (empty($reader)) {
            return $this->message('不存在绑定关系', false);
        }
        $success = [
            'rdid' => $reader['rdid'],
            'name' => $reader['name'],
        ];
        return $this->success($success, true);
    }

    public function getAccessToken(Request $request)
    {
        if (!$request->filled(['time', 'token', 'sign'])) {
            return $this->message('缺少必填参数', false);
        }
        $time = $request->input('time');
        $token = $request->input('token');
        $sign = $request->input('sign');

        if ($token != '542ef3edc367') {
            return $this->message('token is invalid', false);
        }
        $appidKey = md5('wxac20d3ba95b1676b');
        if ($sign != md5($token . '_' . $appidKey . '_' . $time)) {
            return $this->message('sign is invalid', false);
        }
        if ($time + 600 < time()) {
            return $this->message('url is invalid', false);
        }
        $app = Wechatapp::initialize($token);
        $accessToken = $app->access_token->getToken();
        $success = [
            'authorizer_access_token' => $accessToken['access_token']
        ];
        return $this->success($success, true);
    }

    public function getTicket(Request $request)
    {
        if (!$request->filled(['time', 'token', 'sign'])) {
            return $this->message('缺少必填参数', false);
        }
        $time = $request->input('time');
        $token = $request->input('token');
        $sign = $request->input('sign');

        if ($token != '542ef3edc367') {
            return $this->message('token is invalid', false);
        }
        $appidKey = md5('wxac20d3ba95b1676b');
        if ($sign != md5($token . '_' . $appidKey . '_' . $time)) {
            return $this->message('sign is invalid', false);
        }
        if ($time + 600 < time()) {
            return $this->message('url is invalid', false);
        }
        $app = Wechatapp::initialize($token);
        $ticket = $app->jssdk->getTicket();
        return $this->success($ticket, true);
    }

    public function sendTemplateForOpenid(Request $request)
    {
        if ($request->header('Content-Type') != 'application/json') {
            return $this->message('缺少指定头部信息', false);
        }

        if (!$request->filled(['datalist', 'template_id', 'datalist.0.openid', 'time', 'token', 'sign'])) {
            return $this->message('缺少必填参数', false);
        }

        $token = $request->input('token');
        $template_id = $request->input('template_id');
        $list = $request->input('datalist');
        $time = $request->input('time');
        $sign = $request->input('sign');

        if ($token != '542ef3edc367') {
            return $this->message('token is invalid', false);
        }
        $appidKey = md5('wxac20d3ba95b1676b');
        if ($sign != md5($token . '_' . $appidKey . '_' . $time)) {
            return $this->message('sign is invalid', false);
        }

        $app = Wechatapp::initialize($token);
        $response = [];
        foreach ($list as $k => $v) {
            $response[] = [
                'openid' => $v['openid'],
                'status' => 0,
                'meg' => 'Send failed'
            ];

            $sendData = [
                'touser' => $v['openid'],
                'template_id' => $template_id,
                'url' => $v['url'] ?: '',
                'data' => $v['keyValArr']
            ];
            $sendStatus = $app->template_message->send($sendData);
            if (!empty($sendStatus['errcode'])) {
                $response[$k]['meg'] = $sendStatus['errmsg'];
            } else {
                $response[$k]['status'] = 1;
                $response[$k]['meg'] = 'ok';
            }

        }
        return $this->success($response, true);
    }
}
