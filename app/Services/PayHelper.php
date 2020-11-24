<?php

namespace App\Services;


use Illuminate\Support\Str;

class PayHelper
{
    public function GenerateOrderNumber($key = 'uWei', $timeStr = 'YmdH', $randomLength = 3)
    {
        $random = strtoupper(Str::uuid()->getNodeHex() . str_random($randomLength));
        return $key . '-' . date($timeStr) . $random;
    }

    public function redPageNember()
    {
        return strtoupper(date('mdHis') . Str::uuid()->getNodeHex());
    }

    public function GenerateMsgId($key)
    {
        return md5($key . (string) Str::uuid());
    }
}
