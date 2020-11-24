<?php

namespace App\Services;

use App\Models\Wechat\IntegralLog;
use App\Models\Wechat\ReaderToMany;

class IntegralService
{
    public function getIntegral($token, $rdid)
    {
        $readerToMany = ReaderToMany::firstOrNew(['token' => $token, 'rdid' => $rdid]);
        return $readerToMany['integral'];
    }

    public function increment($token, $rdid, $number, $text = '增加积分')
    {
        if ($number > 0) {
            ReaderToMany::where(['token' => $token, 'rdid' => $rdid])->increment('integral', $number);
            $log = [
                'token' => $token,
                'number' => $number,
                'rdid' => $rdid,
                'description' => $text,
                'type' => 1,
                'operation' => 1,
            ];
            IntegralLog::create($log);
        }
    }

    public function decrement($token, $rdid, $number, $text = '减少积分')
    {
        if ($number > 0) {
            ReaderToMany::where(['token' => $token, 'rdid' => $rdid])->decrement('integral', $number);
            $log = [
                'token' => $token,
                'number' => $number,
                'rdid' => $rdid,
                'description' => $text,
                'type' => 1,
                'operation' => 0,
            ];
            IntegralLog::create($log);
        }
    }

}
