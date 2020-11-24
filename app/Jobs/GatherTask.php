<?php

namespace App\Jobs;

use App\Models\Notice\ExpireNotice;
use App\Models\Notice\NoticeRecord;
use App\Models\Notice\NoticeTask;
use App\Services\EsBuilder;
use App\Services\OpenlibService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class GatherTask implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $type;
    protected $task_id;

    public function __construct(string $type, int $task_id = 0)
    {
        $this->type = $type;
        $this->task_id = $task_id;
    }

    public function handle()
    {
        switch ($this->type) {
            // 发布采集任务
            case 'publish':
                // 分发采集 任务 start
                $effective = DB::table('w_expire_notice_conf AS conf')->join('admin_wxuser AS wxuser', function ($join) {
                    $join->on('conf.token', '=', 'wxuser.token');
                })->where('conf.status', '=', 1)
                    ->where('wxuser.status', '=', 1)
                    ->whereNotNull('conf.template_id')
                    ->select('conf.*', 'wxuser.openlib_url')->get();

                $insert = [];
                $effective->each(function ($item) use (&$insert) {
                    unset($item->id, $item->created_at, $item->status);
                    $item->te1_da = json_decode($item->te1_da);
                    $insert[] = [
                        'token' => $item->token,
                        'status' => 0,//创建
                        'created_at' => date('Y-m-d'),//创建
                        'conf_data' => json_encode($item)
                    ];
                });
                // 插入任务数据
                DB::table('w_expire_notice_task')->insert($insert);
                // 发布搜集任务队列
                $publishTask = DB::table('w_expire_notice_task')->where([
                    'status' => 0,
                    'created_at' => date('Y-m-d')
                ])->pluck('id');
                $publishTask->each(function ($task_id) {
                    GatherTask::dispatch('perform', $task_id)->onQueue('disposable');
                });
                break;

            case 'perform':
                // 执行采集任务
                $noticeTask = NoticeTask::where('status', 0)->find($this->task_id);
                if ($noticeTask === null) {
                    return true;
                }
                $token = $noticeTask->token;
                // 判断开启状态
                $conf = ExpireNotice::where('token', $token)->first();
                if ($conf['status'] != 1) {
                    return true;
                }
                // 初始化接口参数
                $conf_data = json_decode($noticeTask->conf_data, true);
                $page = 1;
                $total_n = 0;
                $daynum = -1 * $conf->day_n;
                $http_errors = false;
                $is_retry = $noticeTask->is_retry;
                $model = DB::table('w_expire_notice_record');

                // openlib 初始化 start
                $openlib_url = $conf_data['openlib_url'];
//                $openlib_url = 'https://resource4.gzlib.org.cn/openlib';
                if ($is_retry === 1) {
                    // 重试时直接取 wxuser 里的 openlib_url 地址
                    $openlib_url = DB::table('admin_wxuser')->where('token', $token)->value('openlib_url');
                }
                $OpenlibService = OpenlibService::make($token, [
                    'openlib_url' => $openlib_url
                ]);
                $response = $OpenlibService->getToken(true);
                if ($response['success'] != true) {
                    // openlib 获取授权失败
                    $noticeTask->exception_info = 'openlib 获取授权失败';
                    $noticeTask->status = -1;
                    $noticeTask->save();
                    return true;
                }
                $openlibToken = $response['messagelist'][0]['token'];
                // openlib 初始化 end

                // 判断是否是重试任务 is_retry => 1
                if ($is_retry === 1) {
                    $noticeTask->retry_at = date('Y-m-d H:i:s');
                    // 重试任务执行删除旧搜集数据
                    $model->where('t_id', $this->task_id)->delete();
                    // 判断是否已超过可推送的时间
                    $expire_time = Carbon::parse($noticeTask->created_at)->addDays($conf_data['day_n']);
                    $diffInDays = Carbon::today()->diffInDays($expire_time, false);
                    if ($diffInDays < 0) {
                        //已超过可推送时间
                        $noticeTask->exception_info = '抱歉,当前时间已超过可重试推送时间:' . $expire_time;
                        $noticeTask->status = 4;
                        $noticeTask->save();
                        return true;
                    }
                    $daynum = -1 * $diffInDays;
                }
                // 开始采集请求 1 => 采集中
                $noticeTask->status = 1;
                $noticeTask->save();

                $url = $openlib_url . '/service/query/getOverInfo?' . http_build_query([
                        'token' => $openlibToken,
                        'daynum' => $daynum,
                        'isfixed' => 1,
                        'rows' => 100,
                        'orglib' => $conf->libcode
                    ]);

                $http = new Client();
                $basis = [
                    'timeout' => 120.0,
                    'connect_timeout' => 120.0,
                    'http_errors' => true
                ];

                $status = true;
                while ($status) {
                    try {
                        $response = $http->request('GET', $url . '&page=' . $page, $basis);
                    } catch (RequestException $e) {
                        //采集数据接口异常记录                    未处理
                        $http_errors = true;
                        $noticeTask->exception_info = $e->hasResponse() ?
                            $e->getResponse()->getReasonPhrase() : $e->getMessage();
                        break 1;
                    }
                    $response = json_decode((string)$response->getBody(), true);
                    if ($response['success'] === false) break 1;
                    $count = count($response['pagedata']);
                    if ($count == 0) break 1;
                    if ($count !== 100) $status = false;
                    $insert = [];
                    foreach ($response['pagedata'] as $k => $v) {
                        $insert[] = [
                            'token' => $token,
                            'rdid' => $v['rdid'],
                            'rdloginid' => $v['rdloginid'],
                            'rdname' => $v['rdname'],
                            'rdcertify' => $v['rdcertify'],
                            'rdemail' => $v['rdemail'],
                            'created_at' => date('Y-m-d H:i:s'),
                            'info' => json_encode($v['overinfo']),
                            't_id' => $this->task_id
                        ];
                        ++$total_n;
                    }
                    $model->insert($insert);
                    ++$page;
                    unset($insert);
                }

                $noticeTask->total_n = $total_n;
                $noticeTask->last_page = $page;
                //-1异常,0创建,1采集中,2发布中,3执行中,4执行完成
                // 存在异常状态的
                if ($http_errors == true) {
                    $noticeTask->status = -1;
                    $noticeTask->save();
                    return true;
                }
                // 采集数据完成
                if ($total_n > 0) {
                    $noticeTask->status = 2;
                    $noticeTask->save();
                    // 发布延时任务 - 判断是否是重试任务
                    if ($is_retry !== 1) {
                        SendExpireNotices::dispatch($this->task_id)->delay(Carbon::createFromTimeString($conf->time_at)
                            ->addSeconds(rand(1, 60)))->onQueue('disposable');
                    } else {
                        SendExpireNotices::dispatch($this->task_id)->onQueue('disposable');
                    }
                    return true;
                }
                // 没有数据的时候
                $noticeTask->status = 4;
                $noticeTask->save();
                break;

            case 'migrate':
                //迁移mysql数据到es
                $task_list = NoticeTask::where(['status' => 4, 'is_migrate' => 0])->pluck('created_at', 'id');
                foreach ($task_list as $k => $v) {
                    $yearMonth = date('Ym', strtotime($v));
                    $EsBuilder = EsBuilder::index('exp_notice_log_' . $yearMonth);
                    $status = true;  //记录是否有成功过
                    $success_n = 0;
                    NoticeRecord::where('t_id', $k)
                        ->select('id', 'token', 'rdid', 'openid', 'status', 't_id', 'is_bind', 'info', 'created_at', 'send_at')
                        ->chunkById(100, function ($item) use ($EsBuilder, &$success_n, &$status) {
                            $response = $EsBuilder->bulkInsert($item->toArray());
                            if ($response['errors'] === true) {
                                foreach ($response['items'] as $k => $v) {
                                    if (!isset($v['create']['error'])) {
                                        ++$success_n;
                                    }
                                }
                                //插入数据失败  终止
                                $status = false;
                                return false;
                            } else {
                                $success_n += 100;
                            }
                        });
                    if ($status === true) {
                        // 执行删除
                        NoticeRecord::where('t_id', $k)->delete();
                        NoticeTask::where('id', $k)->update(['is_migrate' => 1]);
                    } else {
                        if ($success_n > 0) {
                            //存在成功的把之前插入的数据删除
                            $EsBuilder->whereTerm('t_id', $k)->deleteByQuery();
                        }
                    }
                }
                break;
        }

        return true;
    }
}
