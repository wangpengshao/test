<?php

namespace App\Jobs;

use App\Models\Wxuser;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class RecordWxAllData implements ShouldQueue
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
        //记录48小时活跃粉丝数量
        //绑证总数量 && 存证总数量
        //当天绑证 && 当天解绑
        $redis = Redis::connection();
        $tokenList = Wxuser::pluck('token');
        $yesterday = Carbon::now()->subDay()->toDateString();

        foreach ($tokenList as $k => $v) {
            $data = [];
            $data['token'] = $v;
            $data['created_at'] = $yesterday;

            $data['active_n'] = $redis->zcard('fans:' . $v);

            $bind_n = DB::table('admin_wechat_reader')->where([
                'token' => $v,
                'is_bind' => 1
            ])->count();
            $data['bind_n'] = $bind_n;

            $save_n = DB::table('admin_wechat_reader')->where([
                'token' => $v,
                'is_bind' => 0
            ])->count();
            $data['save_n'] = $save_n;

            $newbind_n = DB::table('admin_wechat_reader')->where([
                'token' => $v,
                'is_bind' => 1
            ])->whereDate('created_at', $yesterday)->count();
            $data['newbind_n'] = $newbind_n;

            $newsave_n = DB::table('admin_wechat_reader')->where([
                'token' => $v,
                'is_bind' => 0
            ])->whereDate('created_at', $yesterday)->count();

            $data['newsave_n'] = $newsave_n;
            DB::table('w_all_data')->insert($data);
        }
    }
}
