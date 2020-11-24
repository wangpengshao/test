<?php

namespace App\Jobs;

use App\Models\Notice\ExpireNotice;
use App\Models\Notice\NoticeTask;
use App\Models\Wechat\Wechatapp;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class SendExpireNotices implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $task_id;

    public function __construct(int $task_id = 0, bool $retry = false)
    {
        $this->task_id = $task_id;
    }

    public function handle()
    {
        $noticeTask = NoticeTask::where('status', 2)->find($this->task_id);
        if (!$noticeTask) {
            return true;
        }
        $token = $noticeTask->token;
        $doesntExist = ExpireNotice::where('status', 1)->where('token', $token)->doesntExist();
        // 配置没有开启
        if ($doesntExist) {
            return true;
        }
        $conf = json_decode($noticeTask->conf_data, true);
        $conf['expire_time'] = Carbon::parse($noticeTask->created_at)->addDays($conf['day_n'])->toDateString();

        // 更新记录状态: 绑定关系 openid  is_bind => rdid      ....start
        DB::table('w_expire_notice_record AS notice')
            ->join('admin_wechat_reader AS reader', function ($join) {
                $join->on('notice.token', '=', 'reader.token')->on('notice.rdid', '=', 'reader.rdid');
            })->where('reader.token', '=', $token)
            ->where('reader.is_bind', '=', 1)
            ->where('notice.t_id', '=', $this->task_id)
            ->update([
                'notice.openid' => DB::raw("`reader`.`openid`"),
                'notice.is_bind' => 1
            ]);
        // 更新记录状态: 绑定关系 openid  is_bind => rdid      ....end

        // 统计有效的数据条数   有效数据: 存在将过期记录 && 存在绑定关系
        $where = [
            't_id' => $this->task_id,
            'is_bind' => 1,
            'status' => 0,
            'token' => $token
        ];
        $success_n = 0;
        $valid_n = DB::table('w_expire_notice_record')->where($where)->count();
        if ($valid_n == 0) {
            // 没有符合的数据
            $noticeTask->status = 4;
            $noticeTask->save();
            return true;
        }
        // 存在有效数据 修改为发送执行中 状态
        $noticeTask->status = 3;
        if ($noticeTask->valid_n > 0) {
            $valid_n += $noticeTask->valid_n;
        }
        $noticeTask->valid_n = $valid_n;
        $noticeTask->save();

        $client = new Client();
        $app = Wechatapp::initialize($token);
        $access_token = $app->access_token->getToken(true)['access_token'];

        DB::table('w_expire_notice_record')->where($where)->select('id', 'rdid', 'rdname', 'openid', 'info')
            ->chunkById(100, function ($item) use ($client, $conf, $access_token, &$success_n) {

                $reality = [];   //发送成功的数据
                $send_at = date('Y-m-d H:i:s');
                $pool = new Pool($client, $this->send($conf, $item, $access_token), [
                    'concurrency' => 12,
                    'options' => [
                        'timeout' => 5.0,
                        'connect_timeout' => 5.0,
                    ],
                    'fulfilled' => function ($response, $index) use ($item, &$reality) {
                        $response = json_decode((string)$response->getBody(), true);
                        if (isset($response['errcode']) && $response['errcode'] === 0) {
                            $reality[] = $item[$index]->id;
                        }
                    },
                    'rejected' => function ($reason, $index) {

                    },
                ]);
                // Initiate the transfers and create a promise
                $promise = $pool->promise();
                // Force the pool of requests to complete.
                $promise->wait();

                if ($reality) {
                    $success_n += count($reality);
                    // 更新发送成功的
                    DB::table('w_expire_notice_record')->whereIn('id', $reality)->update([
                        'status' => 1,
                        'send_at' => $send_at
                    ]);
                }
            });

        $noticeTask->status = 4;
        $noticeTask->success_n = $success_n;
        $noticeTask->save();
        return true;
    }

    private function send($conf, $list, $access_token)
    {
        $sendData = ['template_id' => $conf['template_id']];
        if ($conf['redirect_url']) {
            $sendData['url'] = str_replace('{token}', $conf['token'], $conf['redirect_url']);
        }
        foreach ($list as $k => $v) {
//            $v->openid = 'ofgxfuNP2fguUNsaeNdrbCKJvMBE';
            $book = $this->setTitleText($v->info);
            //替换数据
            $te1_da = str_ireplace(
                ['book_mark', 'rdid_mark', 'name_mark', 'expire_time_mark'],
                [$book, $v->rdid, $v->rdname, $conf['expire_time']],
                $conf['te1_da']
            );

            $sendData['data'] = array_map(function ($v) {
                return [
                    'value' => $v,
                    'color' => '#173177'
                ];
            }, $te1_da);
            $sendData['touser'] = $v->openid;
            yield  new Request(
                'POST',
                'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token,
                [],
                json_encode($sendData)
            );
        }
    }

    private function setTitleText($info)
    {
        $books = array_column(json_decode($info, true), 'title');
        $book = '';
        $books_n = count($books);
        foreach ($books as $k => $v) {
            $book .= '《' . $v . '》';
            if ($k > 5) {
                $book .= '... ...';
                break;
            }
            if ($k + 1 != $books_n) {
                $book .= ',';
            }
        }
        return $book;
    }
}
