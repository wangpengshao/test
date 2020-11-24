<?php

namespace App\Services;

use App\Models\Wechat\IntegralLog;
use App\Models\Wechat\QrCodeConfig;
use App\Models\Wechat\QrCodeList;
use App\Models\Wechat\QrCodeLog;
use App\Models\Wechat\QrCodeSeo;
use App\Models\Wechat\QrTask;
use App\Models\Wechat\Reader;
use App\Models\Wechat\ReaderToMany;
use App\Models\Wechat\Wechatapp;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class ScanCodeTool
{
    public function ScanSubSeo(&$replyData, $eventKey, $token, $openid)
    {
        if (!isset($eventKey['s_id']) || !is_numeric($eventKey['s_id'])) {
            return;
        }
        $s_id = $eventKey['s_id'];
        $first = QrCodeSeo::where(['id' => $s_id, 'token' => $token, 'status' => 1])
            ->first(['keyword', 'views', 'id', 'group_id', 'invites']);
        if (!$first) return;
        if ($first['keyword']) {
            $replyData->type = 1;
            $replyData->keyword = $first['keyword'];
        }
        // 判断缓存是否更新数据
        $cacheKey = 'seo:' . $token . ':' . $openid;
        if (Cache::has($cacheKey)) return;
        Cache::put($cacheKey, 1, 720);
        $first->views++;
        $first->invites++;
        $first->save();
        //统计分组
        if ($first['group_id']) {
            $app = Wechatapp::initialize($token);
            $app->user_tag->tagUsers([$openid], $first['group_id']);
        }
    }

    public function ScanSubTask(&$replyData, $eventKey, $token, $openid)
    {
        $rdid = Arr::get($eventKey, 'rdid');
        $t_id = Arr::get($eventKey, 't_id', 0);

        //config未开启
        $qrCodeConfig = QrCodeConfig::where(['token' => $token, 'status' => 1])->first(['keyword']);
        if (empty($qrCodeConfig)) {
            return;
        }

        if (Arr::get($qrCodeConfig, 'keyword')) {
            $replyData->type = 1;
            $replyData->keyword = $qrCodeConfig['keyword'];
        }

        if ($t_id !== 0) {
            $qrTask = QrTask::where(['id' => $t_id])->first();
            //未开启活动
            if (empty($qrTask) && !Carbon::now()->between(Carbon::parse($qrTask['s_time']), Carbon::parse($qrTask['e_time']))) {
                return;
            }
            if (!empty($qrTask['keyword'])) {
                $replyData->type = 1;
                $replyData->keyword = $qrTask['keyword'];
            }
            //提示
            if ($qrTask['is_inform'] == 1 && !empty($qrTask['te1_id'])) {
                $reader = Reader::where(['token' => $token, 'rdid' => $rdid, 'is_bind' => 1])->first(['openid']);
                if ($reader['openid']) {
                    $app = Wechatapp::initialize($token);
                    $app->template_message->send([
                        'touser' => $reader['openid'],
                        'template_id' => $qrTask['te1_id'],
                        'data' => $qrTask['te1_da'],
//                            'url' => 'https://easywechat.org',   //跳转到个人推广页面
                    ]);
                }

            }

            $readerToMany = ReaderToMany::firstOrNew(['token' => $token, 'rdid' => $rdid]);
            //常规奖励
            if ($qrTask['integral'] > 0) {
                //.....进行增加积分
                $integral = ($readerToMany->integral) ?: 0;
                $readerToMany->integral = $integral + $qrTask['integral'];
                $readerToMany->save();
                $addIntegralLog = [
                    'token' => $token,
                    'number' => $qrTask['integral'],
                    'rdid' => $rdid,
                    'description' => '邀请关注成功奖励积分！',
                    'type' => 1,
                    'operation' => 1,
                ];
                IntegralLog::create($addIntegralLog);
            }

            $bindQrReader = QrCodeList::where(['token' => $token, 'rdid' => $rdid, 't_id' => $t_id])->first();
            $qrCodeLogId = '';
            if ($bindQrReader) {
                //增加扫码次数 && 邀请次数
                $bindQrReader->invites++;
                $bindQrReader->views++;
                $bindQrReader->save();
                //记录邀请成功log  &&  并保留有效时间可失效（未完成）  log变无效  并减少积分
                $addLog = [
                    'rdid' => $rdid,
                    'token' => $token,
                    'openid' => $openid,
                    't_id' => $t_id,
                    'isValid' => 1
                ];
                $qrCodeLogId = QrCodeLog::create($addLog)->id;
            }

            //保持天数
            if ($qrTask['k_days'] > 0) {
                $cacheKey = 'qrTask_' . $token . '_' . $openid;
                $keepMin = Carbon::now()->addDay($qrTask['k_days'])->diffInMinutes();

                $cacheData = [
                    'log_id' => $qrCodeLogId,
                    'rdid' => $rdid,
                    'c_time' => date('Y-m-d H:i:s'),
                    'integral' => $qrTask['integral'],
                ];
                Cache::put($cacheKey, $cacheData, $keepMin);
            }

            //达标处理
            if ($qrTask['number'] > 0 && $bindQrReader['is_da'] !== 1) {

                $count = QrCodeLog::where(['token' => $token, 'rdid' => $rdid, 't_id' => $t_id, 'isValid' => 1])->count();
                if ($count >= $qrTask['number']) {

                    //.....更新达标状态
                    $bindQrReader->is_da = 1;
                    $bindQrReader->save();

                    //.....达标奖励积分
                    if ($qrTask['d_integral'] > 0) {
                        //.....进行增加积分
                        $readerToMany->integral = $readerToMany->integral + $qrTask['d_integral'];
                        $readerToMany->save();

                        $addIntegralLog = [
                            'token' => $token,
                            'number' => $qrTask['d_integral'],
                            'rdid' => $rdid,
                            'description' => '邀请关注达标奖励积分！',
                            'type' => 1,
                            'operation' => 1,
                        ];
                        IntegralLog::create($addIntegralLog);
                    }

                    //.....发送达标通知
                    if (!empty($qrTask['te2_id'])) {
                        $reader = Reader::where(['token' => $token, 'rdid' => $rdid, 'is_bind' => 1])->first(['openid']);
                        if ($reader['openid']) {
                            $app = Wechatapp::initialize($token);
                            $app->template_message->send([
                                'touser' => $reader['openid'],  //二维码拥有者
                                'template_id' => $qrTask['te2_id'],
                                'data' => $qrTask['te2_da'],
                                //                            'url' => 'https://easywechat.org',   //跳转到个人推广页面
                            ]);
                        }
                    }

                }
            }
        }
    }

    public function qrTask(&$keyword, $eventKey, $token, $openid)
    {
        $rdid = Arr::get($eventKey, 'rdid');
        $t_id = Arr::get($eventKey, 't_id', 0);

        $qrCodeConfig = QrCodeConfig::where(['token' => $token, 'status' => 1])->first(['keyword']);
        if (empty($qrCodeConfig)) {
            return;
        }

        if (Arr::get($qrCodeConfig, 'keyword')) {
            $keyword = $qrCodeConfig['keyword'];
        }

        if ($t_id !== 0) {
            $qrTask = QrTask::where(['id' => $t_id])->first();
            //未开启活动
            if (empty($qrTask) && !Carbon::now()->between(Carbon::parse($qrTask['s_time']), Carbon::parse($qrTask['e_time']))) {
                return;
            }
            if (!empty($qrTask['keyword'])) {
                $keyword = $qrTask['keyword'];
            }
            $bindQrReader = QrCodeList::where(['token' => $token, 'rdid' => $rdid, 't_id' => $t_id])->first();
            if ($bindQrReader) {
                //增加扫码次数 && 邀请次数
                $bindQrReader->views++;
                $bindQrReader->save();
            }
        }
    }

    public function qrSeo(&$keyword, $eventKey, $token, $openid)
    {
        if (!isset($eventKey['s_id']) || !is_numeric($eventKey['s_id'])) {
            return;
        }
        $s_id = $eventKey['s_id'];
        $first = QrCodeSeo::where(['id' => $s_id, 'token' => $token, 'status' => 1])
            ->first(['keyword', 'views', 'id', 'group_id']);

        if (!$first) return;
        if ($first['keyword']) {
            $keyword = $first['keyword'];
        }

        $cacheKey = 'seo:' . $token . ':' . $openid;
        if (Cache::has($cacheKey)) return;
        Cache::put($cacheKey, 1, 720);

        $first->views++;
        $first->save();

        //统计分组
        if ($first['group_id']) {
            $app = Wechatapp::initialize($token);
            $app->user_tag->tagUsers([$openid], $first['group_id']);
        }
    }

}
