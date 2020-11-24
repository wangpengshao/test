<?php

namespace App\Jobs;

use App\Models\Wechat\CusMsgData;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;

class WechatCustomMsg implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $cusMsgData;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CusMsgData $cusMsgData)
    {
        $this->cusMsgData = $cusMsgData;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \EasyWeChat\Kernel\Exceptions\HttpException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function handle()
    {
        $msg = null;
        if($this->cusMsgData->msg_type == 'text'){
            $msg = new Text($this->cusMsgData->text_data['content']);
        }
        elseif ($this->cusMsgData->msg_type == 'news'){
            $items = [
                new NewsItem([
                    'title'       => $this->cusMsgData->news_data['title'],
                    'description' => $this->cusMsgData->news_data['description'],
                    'url'         => $this->cusMsgData->news_data['url'],
                    'image'       => Storage::disk('admin')->url($this->cusMsgData->news_data['picurl']),
                ]),
            ];
            $msg = new News($items);
        }

        $app = Wechatapp::initialize($this->cusMsgData->token);

        switch($this->cusMsgData->send_type){
            case 0: //全部粉丝
                $redis = Redis::connection();
                $lists = $redis->zrange('fans:'. $this->cusMsgData->token, 0, -1);
                foreach ($lists as $k => $v){
                    $app->customer_service->message($msg)->to($v)->send();
                }
                unset($redis, $lists);
                break;
            case 1: //分组粉丝
                foreach ($this->cusMsgData->group_tag as $tag){
                    $next_openid = '';
                    $status = true;
                    while ($status){
                        $app->access_token->getToken(true);
                        $lists = $app->user_tag->usersOfTag($tag, $next_openid);
                        $status = $lists['count'] != 0 ? true : false;
                        if(!$status) break;
                        $next_openid = $lists['next_openid'];
                        foreach ($lists['data']['openid'] as $k => $v){
                            $app->customer_service->message($msg)->to($v)->send();
                        }
                        unset($lists);
                    }
                }

                break;
            case 2: //绑定用户
                $lists = Reader::where('token', $this->cusMsgData->token)->pluck('openid');
                $app->access_token->getToken(true);
                foreach ($lists as $k => $v){
                    $app->customer_service->message($msg)->to($v)->send();
                }
                unset($lists);
                break;
            default: //指定粉丝
                $lists = preg_replace("/\\r\\n/", ',', $this->cusMsgData->openids);
                $lists = explode(',', $lists);
                foreach ($lists as $v){
                    $app->customer_service->message($msg)->to($v)->send();
                }
                unset($lists);
                break;
        }

    }
}
