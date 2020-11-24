<?php

namespace App\Services;

use App\Models\Wxuser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FansEvent
{
    protected $token;
    protected $openid;

    public function __construct($token, $openid)
    {
        $this->token = $token;
        $this->openid = $openid;
    }

    public function check($eventName)
    {
        $wxuser = Wxuser::getCache($this->token);
        $datetime = date('Y-m-d H:i:s');
        $eventData = '';
        if ($wxuser['event_type'] && $wxuser['event_id'] && $wxuser['event_s_at'] < $datetime && $datetime < $wxuser['event_e_at']) {
            $eventConfig = config('RelatedEvent.' . $wxuser['event_type']);
            if ($eventConfig) {
                $a_id = $wxuser['event_id'];
                $where = [
                    'origin_type' => 1,
                    'origin_id' => config('EventList.' . $eventName),
                    'a_id' => $a_id,
                    'token' => $this->token
                ];
                $exists = DB::table($eventConfig['typeTable'])->where($where)->where('status', 1)->exists();
                if ($exists) {
                    $cacheKey = 'event:' . $this->token . ':' . $this->openid;
                    $eventData = [
                        'typeName' => $eventConfig['typeName'],
                        'typeData' => [
                            'url' => route($eventConfig['routeName'], ['token' => $this->token, 'a_id' => $a_id])
                        ],
                    ];
                    Cache::put($cacheKey, $where, 30);
                }
            }
        }
        return $eventData;
    }

}
