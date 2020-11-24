<?php

namespace App\Jobs;

use App\Models\Wxuser;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;

class ClearFans48 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     * 自动化清除48小时之外的活跃粉丝数据
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $tokenList = Wxuser::pluck('token');
        $redis = Redis::connection();
        $min = 0;
        //两天 加多两小时定时时间
        $max = time() - 172800 + 7200;
        foreach ($tokenList as $k => $v) {
            $key = 'fans:' . $v;
            $redis->zremrangebyscore($key, $min, $max);
        }
        unset($tokenList, $redis);
    }
}
