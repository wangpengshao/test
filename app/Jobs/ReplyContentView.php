<?php

namespace App\Jobs;

use App\Models\Wechat\Replycontent;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;

class ReplyContentView implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
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
        $redis = Redis::connection();
        Replycontent::select('id', 'views')->chunk(200, function ($flights) use ($redis) {
            foreach ($flights as $flight) {
                $cacheKey = 'wechat:replycontent_' . $flight['id'];
                $cahce = $redis->get($cacheKey);
                $views = empty($cahce) ? 0 : $cahce;

                if ($views > 0 && $views > $flight['views']) {
                    Replycontent::where('id', $flight['id'])->update(['views' => $views]);
                }

            }
        });
    }
}
