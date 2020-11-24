<?php

namespace App\Jobs;

use App\Models\Wechat\Reader;
use App\Models\Wechat\TplMsgData;
use App\Models\Wechat\Wechatapp;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;


class WechatTemplateMsg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $token;

    public function __construct($token, $id)
    {
        $this->token = $token;
        $this->id = $id;
    }


    public function handle()
    {
        $config = TplMsgData::where(['id' => $this->id, 'token' => $this->token])->first();
        if ($config === null || empty($config->template_id) || $config->status !== 2) {
            return false;
        }
        $sendData = [
            'template_id' => $config->template_id,
            'data' => $config->te1_da
        ];
        if ($config->redirect_type == 1 && !empty($config->redirect_url)) {
            $sendData['url'] = $config->redirect_url;
        }
        if ($config->redirect_type == 2 && !empty($config->mini_appid) && !empty($config->mini_path)) {
            $sendData['miniprogram'] = [
                'appid' => $config->mini_appid,
                'pagepath' => $config->mini_path,
            ];
        }
        $client = new Client();
        $app = Wechatapp::initialize($this->token);
        $access_token = $app->access_token->getToken(true)['access_token'];
        //修改状态为发送中
        $config->status = 3;
        $config->save();

        $reality_n = 0;                         //成功数
        $failure_n = 0;                         //失败数
        $lists = [];
        $next_openid = '';                      //openid
        $status = true;                         //开关

        if ($config->send_type == 0) {             //群发全部粉丝
            $average_value = 0;                     //平均值
            while ($status) {
                $lists = $app->user->list($next_openid);
                if ($average_value === 0) {
                    $average_value = round($lists['total'] / 10);     //分10份
                }
                if ($lists['count'] === 0) break 1;

                $next_openid = $lists['next_openid'];
                $openid_list = $lists['data']['openid'];
                $pool = new Pool($client, $this->send($sendData, $openid_list, $access_token), [
                    'concurrency' => 12,
                    'options' => [
                        'timeout' => 5.0,
                        'connect_timeout' => 5.0,
                    ],
                    'fulfilled' => function ($response, $index) use (
                        &$reality_n, &$failure_n, $openid_list, $average_value, $config
                    ) {
                        $response = json_decode((string)$response->getBody(), true);
                        if (isset($response['errcode']) && $response['errcode'] === 0) {
                            ++$reality_n;
                        } else {
                            ++$failure_n;
                            if ($failure_n < 100) {
                                $create = [
                                    't_id' => $this->id,
                                    'openid' => $openid_list[$index],
                                    'mes' => $response['errmsg'],
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'errcode' => $response['errcode']
                                ];
                                DB::table('w_tplmsg_failure')->insert($create);
                            }
                        }
                        $current_n = $reality_n + $failure_n;
                        if ($current_n % $average_value === 0) {
                            $config->reality_n = $reality_n;
                            $config->failure_n = $failure_n;
                            $config->save();
                        }
                    },
                    'rejected' => function ($reason, $index) use (&$failure_n, $openid_list) {
                        ++$failure_n;
                        $create = [
                            't_id' => $this->id,
                            'openid' => $openid_list[$index],
                            'mes' => $reason->getMessage(),
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        DB::table('w_tplmsg_failure')->insert($create);
                    },
                ]);
                // Initiate the transfers and create a promise
                $promise = $pool->promise();
                // Force the pool of requests to complete.
                $promise->wait();
            }
        }

        if ($config->send_type == 1) {              //分组发送
            $average_value = 500;                     //平均值
            foreach ($config->group_tag as $k => $v) {
                while ($status) {
                    $lists = $app->user_tag->usersOfTag($v, $next_openid);
                    if ($lists['count'] === 0) break 1;

                    $next_openid = $lists['next_openid'];
                    $openid_list = $lists['data']['openid'];
                    $pool = new Pool($client, $this->send($sendData, $openid_list, $access_token), [
                        'concurrency' => 12,
                        'options' => [
                            'timeout' => 5.0,
                            'connect_timeout' => 5.0,
                        ],
                        'fulfilled' => function ($response, $index) use (
                            &$reality_n, &$failure_n, $openid_list, $average_value, $config
                        ) {
                            $response = json_decode((string)$response->getBody(), true);
                            if (isset($response['errcode']) && $response['errcode'] === 0) {
                                ++$reality_n;
                            } else {
                                ++$failure_n;
                                if ($failure_n < 100) {
                                    $create = [
                                        't_id' => $this->id,
                                        'openid' => $openid_list[$index],
                                        'mes' => $response['errmsg'],
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'errcode' => $response['errcode']
                                    ];
                                    DB::table('w_tplmsg_failure')->insert($create);
                                }
                            }
                            $current_n = $reality_n + $failure_n;
                            if ($current_n % $average_value === 0) {
                                $config->reality_n = $reality_n;
                                $config->failure_n = $failure_n;
                                $config->save();
                            }
                        },
                        'rejected' => function ($reason, $index) use (&$failure_n, $openid_list) {
                            ++$failure_n;
                            $create = [
                                't_id' => $this->id,
                                'openid' => $openid_list[$index],
                                'mes' => $reason->getMessage(),
                                'created_at' => date('Y-m-d H:i:s')
                            ];
                            DB::table('w_tplmsg_failure')->insert($create);
                        },
                    ]);
                    // Initiate the transfers and create a promise
                    $promise = $pool->promise();
                    // Force the pool of requests to complete.
                    $promise->wait();
                }
            }
        }

        if ($config->send_type == 2) {
            //绑定读者
            Reader::where(['token' => $this->token, 'is_bind' => 1])->select('openid')
                ->chunk(200, function ($flights) use (
                    $app, $sendData, &$reality_n, &$failure_n, $client, $access_token, $config
                ) {
                    $openid_list = Arr::pluck($flights->toArray(), 'openid');
                    $pool = new Pool($client, $this->send($sendData, $openid_list, $access_token), [
                        'concurrency' => 12,
                        'options' => [
                            'timeout' => 5.0,
                            'connect_timeout' => 5.0,
                        ],
                        'fulfilled' => function ($response, $index) use (
                            &$reality_n, &$failure_n, $openid_list, $config
                        ) {
                            $response = json_decode((string)$response->getBody(), true);
                            if (isset($response['errcode']) && $response['errcode'] === 0) {
                                ++$reality_n;
                            } else {
                                ++$failure_n;
                                if ($failure_n < 100) {
                                    $create = [
                                        't_id' => $this->id,
                                        'openid' => $openid_list[$index],
                                        'mes' => $response['errmsg'],
                                        'created_at' => date('Y-m-d H:i:s'),
                                        'errcode' => $response['errcode']
                                    ];
                                    DB::table('w_tplmsg_failure')->insert($create);
                                }
                            }
                            $current_n = $reality_n + $failure_n;
                            if ($current_n % 1000 === 0) {
                                $config->reality_n = $reality_n;
                                $config->failure_n = $failure_n;
                                $config->save();
                            }
                        },
                        'rejected' => function ($reason, $index) use (&$failure_n, $openid_list) {
                            ++$failure_n;
                            $create = [
                                't_id' => $this->id,
                                'openid' => $openid_list[$index],
                                'mes' => $reason->getMessage(),
                                'created_at' => date('Y-m-d H:i:s')
                            ];
                            DB::table('w_tplmsg_failure')->insert($create);
                        },
                    ]);
                    // Initiate the transfers and create a promise
                    $promise = $pool->promise();
                    // Force the pool of requests to complete.
                    $promise->wait();
                });
        }

        if ($config->send_type == 3) {
            $lists = preg_replace("/\\r\\n/", ',', $config->openids);
            $lists = explode(',', $lists);
            foreach ($lists as $v) {
                $sendData['touser'] = $v;
                $response = $app->template_message->send($sendData);
                if (isset($response['errcode']) && $response['errcode'] === 0) {
                    ++$reality_n;
                } else {
                    ++$failure_n;
                }
            }
        }

        unset($lists);

        $config->reality_n = $reality_n;
        $config->failure_n = $failure_n;
        $config->status = 1;
        $config->save();
        return true;

    }

    private function send($sendData, $list, $access_token)
    {
        // 兼容数据结构
        if (isset($sendData['data'])) {
            $sourceData = $sendData['data'];
            foreach ($sourceData as $k => $v) {
                $sendData['data'][$k] = [
                    'value' => $v,
                    'color' => '#173177'
                ];
            }
        }
        foreach ($list as $value) {
            $sendData['touser'] = $value;
            yield new Request(
                'POST',
                'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token,
                [],
                json_encode($sendData)
            );
        }
    }
}
