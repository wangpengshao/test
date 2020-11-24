<?php

namespace App\Jobs;

use App\Models\Wechat\TplMsgThird;
use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class OldPlatformTplMsg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;
    protected $token;
    protected $http;
    protected $is_retry;

    public function __construct($token, $id, $is_retry = false)
    {
        $this->token = $token;
        $this->id = $id;
        $this->is_retry = $is_retry;
    }


    public function handle()
    {
        $config = TplMsgThird::where(['id' => $this->id, 'token' => $this->token])->first();
        if ($config === null || empty($config->template_id) || $config->status !== 2) {
            return false;
        }

        $this->http = new Client();

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
        $access_token = $this->getAccessToken($this->token, $config['appid']);

        //修改状态为发送中
        $config->status = 3;
        $config->save();

        $reality_n = 0;                         //成功数
        $failure_n = 0;                         //失败数
        $lists = [];
        $next_openid = '';                      //openid

        // 判断是否是重试 , 指定类型才可重试 40001 access_token失效
        if ($this->is_retry === true) {
            $firstError = DB::table('w_tplmsg_failure_third')->where(['t_id' => $this->id, 'errcode' => '40001'])->first();
            if (empty($firstError)) {
                return false;
            }
            $reality_n = $config->reality_n;
            $next_openid = $firstError->openid;
        }

        $status = true;                         //开关

        if ($config->send_type == 1) {              //分组发送
            $average_value = 500;                     //平均值
            foreach ($config->group_tag as $k => $v) {
                while ($status) {
                    $lists = $this->getTagUser($access_token, $v, $next_openid);
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
                                    DB::table('w_tplmsg_failure_third')->insert($create);
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
                            DB::table('w_tplmsg_failure_third')->insert($create);
                        },
                    ]);
                    // Initiate the transfers and create a promise
                    $promise = $pool->promise();
                    // Force the pool of requests to complete.
                    $promise->wait();
                }
            }
        }

        if ($config->send_type == 2) {             //群发全部粉丝
            $average_value = 0;                     //平均值
            while ($status) {
                $lists = $this->getAllUser($access_token, $next_openid);
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
                                DB::table('w_tplmsg_failure_third')->insert($create);
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
                        DB::table('w_tplmsg_failure_third')->insert($create);
                    },
                ]);
                // Initiate the transfers and create a promise
                $promise = $pool->promise();
                // Force the pool of requests to complete.
                $promise->wait();
            }
        }

        if ($config->send_type == 3) {              //绑定发送
            $average_value = 500;                     //平均值
            $lists = $this->getBindUser($this->token);
            $openid_list = $lists['data'];
            unset($lists);

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
                            DB::table('w_tplmsg_failure_third')->insert($create);
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
                    DB::table('w_tplmsg_failure_third')->insert($create);
                },
            ]);
            // Initiate the transfers and create a promise
            $promise = $pool->promise();
            // Force the pool of requests to complete.
            $promise->wait();
        }


        unset($lists);

        $config->reality_n = $reality_n;
        $config->failure_n = $failure_n;
        $config->status = 1;
        $config->save();
        $this->callbackNotification($this->token, $config->old_id);     // 回调通知

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
            //$sendData['touser'] = 'oAXk1t7j9yARrL1A-pHofTGQ0cTo';  // 测试openid
            yield new Request(
                'POST',
                'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $access_token,
                [],
                json_encode($sendData)
            );
        }
    }

    private function getAllUser($access_token, $next_openid = '')
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $access_token . '&next_openid=' . $next_openid;

        $response = $this->http->get($url);
        $response = (string)$response->getBody();

        return json_decode($response, true);
    }

    private function getTagUser($access_token, $tagid = '', $next_openid = '')
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/tag/get?access_token=' . $access_token;
        $params = [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'tagid' => $tagid,
                'next_openid' => $next_openid
            ],
        ];
        $response = $this->http->post($url, $params);
        $response = (string)$response->getBody();

        return json_decode($response, true);
    }

    private function getAccessToken($token, $appid)
    {
        // 调用旧U微的接口获取 AccessToken
        $url = 'https://u.interlib.cn/index.php?g=Mysql&m=Apidata&a=getAccessToken&refresh=true&token=' . $token;
        $time = time();
        $sign = md5($token . '_' . md5($appid) . '_' . $time);

        $url .= '&time=' . $time . '&sign=' . $sign;
        $response = $this->http->get($url);
        $response = (string)$response->getBody();
        $response = json_decode($response, true);

        return $response['data']['authorizer_access_token'];
    }

    private function getBindUser($token)
    {
        // 调用旧U微的接口获取 AccessToken
        $url = 'https://u.interlib.cn/index.php?g=Mysql&m=Apidata&a=getBindOpenid&token=' . $token;
        $time = time();
        $sign = md5(md5($token) . '_' . $time);

        $url .= '&time=' . $time . '&sign=' . $sign;
        $response = $this->http->get($url);
        $response = (string)$response->getBody();

        return json_decode($response, true);
    }

    private function callbackNotification($token, $old_id, $status = '1')
    {
        $url = 'https://u.interlib.cn/index.php?g=Mysql&m=Apidata&a=handleTplMsgStatus&token=' . $token . '&id=' . $old_id . '&status=' . $status;
        $time = time();
        $sign = md5($old_id . '_' . md5($token) . '_' . $time);

        $url .= '&time=' . $time . '&sign=' . $sign;
        $response = $this->http->get($url);
        $response = (string)$response->getBody();

        return json_decode($response, true);
    }


}
