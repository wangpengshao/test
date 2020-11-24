<?php

namespace App\Jobs;

use App\Models\Wechat\Wechatapp;
use EasyWeChat\Kernel\Messages\Text;
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
class GroupSendMes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $token;
    /**
     * @var
     */
    protected $openid;
    /**
     * @var
     */
    protected $text;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * GroupSendMes constructor.
     *
     * @param $token
     * @param $openid
     * @param $text
     */
    public function __construct($token, $openid, $text)
    {
        $this->token = $token;
        $this->openid = $openid;
        $this->text = $text;
    }

    /**
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidArgumentException
     * @throws \EasyWeChat\Kernel\Exceptions\RuntimeException
     */
    public function handle()
    {
        $app = Wechatapp::initialize($this->token);
        $message = new Text($this->text);
        foreach ($this->openid as $value) {
            $app->customer_service->message($message)->to($value)->send();
        }
        unset($value);
    }
}
