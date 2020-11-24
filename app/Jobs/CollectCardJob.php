<?php

namespace App\Jobs;

use App\Models\CollectCard\CollectCard;
use App\Models\CollectCard\CollectPrize;
use App\Models\CollectCard\CollectRedPack;
use App\Models\CollectCard\CollectUsers;
use App\Services\RandRedPackage;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class CollectCardJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $a_id;
    protected $updated_at;
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 900;
    public $tries = 1;

    public function __construct($a_id, $updated_at)
    {
        $this->updated_at = $updated_at;
        $this->a_id = $a_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //根据时间来判断是否需要发奖
        $first = CollectCard::where('id', $this->a_id)->where('updated_at', $this->updated_at)
            ->first(['id', 'type', 'token', 'is_prize']);
        if ($first && $first['type'] != 1 && $first['is_prize'] != 1) {
            $token = $first['token'];
            $userList = CollectUsers::where('a_id', $this->a_id)->where('collect_all', 1)->pluck('id');
            $userCount = $userList->count();
            if ($userCount > 0) {
                $collectPrize = CollectPrize::where('a_id', $this->a_id)->get();
                $type0 = $collectPrize->whereStrict('type', 0);
                $type0Number = $type0->sum('inventory');
                $type1 = $collectPrize->whereStrict('type', 1);
                $type1Number = $type1->sum('inventory');
                $type2 = $collectPrize->whereStrict('type', 2);
                $firstRedType = $type2->firstWhere('money', '>', 0);

                if ($firstRedType) {
                    //暂时只支持第一个拼手气红包
                    $redPackNumber = $userCount - $type0Number - $type1Number;
                    //进行拼手气红包设置
                    $randRedPackage = RandRedPackage::setOptions($firstRedType['money'], $redPackNumber, $firstRedType['min_n'], $firstRedType['max_n'])
                        ->create();
                    //进行红包数据插入mysql
                    $newRedPackage = [];
                    foreach ($randRedPackage as $k => $v) {
                        $newRedPackage[] = [
                            'p_id' => $firstRedType['id'],
                            'money' => $v,
                            'status' => 0,
                            'isValid' => 1,
                            'created_at' => date('Y-m-d H:i:s'),
                        ];
                    }
                    DB::table('w_collect_card_redpack')->insert($newRedPackage);
                    unset($newRedPackage);
                }
                //如果没多余的话，进行正常发奖
                $reward = [];
                $rewardDemo = [
                    'token' => $token,
                    'a_id' => $this->a_id,
                    'type' => 0,
                    'user_id' => 0,
                    'prize_id' => 0,
                    'is_get' => 0,
                    'redpack_id' => 0,
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $type0->each(function ($item) use (&$reward, $rewardDemo) {
                    for ($a = 1; $a <= $item['inventory']; $a++) {
                        $rewardDemo['type'] = 0;
                        $rewardDemo['prize_id'] = $item['id'];
                        $reward[] = $rewardDemo;
                    }
                });
                $type1->each(function ($item) use (&$reward, $rewardDemo) {
                    for ($a = 1; $a <= $item['inventory']; $a++) {
                        $rewardDemo['type'] = 1;
                        $rewardDemo['prize_id'] = $item['id'];
                        $reward[] = $rewardDemo;
                    }
                });
                //开始发奖 => 先查出红包 数量的id
                if ($firstRedType) {
                    $redPackIdList = CollectRedPack::where('p_id', $firstRedType['id'])->pluck('id');
                    foreach ($redPackIdList as $k => $v) {
                        $rewardDemo['type'] = 2;
                        $rewardDemo['prize_id'] = $firstRedType['id'];
                        $rewardDemo['redpack_id'] = $v;
                        $reward[] = $rewardDemo;
                    }
                    unset($redPackIdList);
                }
                //奖品数量相等，进行发奖
                $rewardCount = count($reward);
                if ($userCount > 0 && $rewardCount > 0) {
                    $forCount = ($userCount > $rewardCount) ? $rewardCount : $userCount;
                    $userList = $userList->toArray();
                    shuffle($reward);
                    $newReward = [];
                    foreach ($reward as $k => $v) {
                        if ($forCount == 0) break;
                        $reward[$k]['user_id'] = $userList[$k];
                        $newReward[] = $reward[$k];
                        $forCount--;
                    }
                    unset($reward, $userList);
                    if ($newReward) {
                        DB::table('w_collect_card_u_reward')->insert($newReward);
                    }
                    CollectPrize::where('a_id', $this->a_id)->update(['inventory' => 0]);
                }
            }
            $first->is_prize = 1;
            $first->save();
        }

    }
}
