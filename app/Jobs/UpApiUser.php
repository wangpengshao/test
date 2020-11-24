<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

//Timing update user(api) data
class UpApiUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200;

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
        DB::table('oauth_access_token_providers')
            ->where('created_at', '<', now()->subDay(2)->toDateTimeString())->delete();
        DB::table('oauth_access_tokens')->where('expires_at', '<', date('Y-m-d H:i:s'))->delete();
        DB::table('oauth_refresh_tokens')->where('expires_at', '<', date('Y-m-d H:i:s'))->delete();

        DB::table('users')->select('id', 'r_num', 'r_allnum')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $k => $v) {
                $allNum = \Redis::get('apiAuth:user:' . $v->id . ':allNum');
                if ($allNum) {
                    $v->r_allnum = ($allNum) ?: $v->r_allnum;
                    DB::table('users')->where('id', $v->id)->update(json_decode(json_encode($v), true));
                }
            }
            unset($k, $v);
        });
    }
}
