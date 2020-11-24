<?php

namespace App\Jobs;

use App\Models\Wechat\Wechatapp;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Class GroupSendMes
 *
 * @package App\Jobs
 */
class GroupSendImgMes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $token;

    protected $openid;


    protected $item;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;


    public function __construct($token, $openid, $item)
    {
        $this->token = $token;
        $this->openid = $openid;
        $this->item = $item;
    }

    public function handle()
    {
        $app = Wechatapp::initialize($this->token);

        $news = new News([new NewsItem($this->item)]);
        foreach ($this->openid as $value) {
            $app->customer_service->message($news)->to($value)->send();
        }
        unset($value);
    }
}
