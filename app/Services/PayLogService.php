<?php

namespace App\Services;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

class PayLogService
{

    public function placeOrder($token, $type, $info)
    {
        (new Logger($type))
            ->pushHandler(new RotatingFileHandler(storage_path('logs/placeOrder/' . $token . '/' . $type . '.log')))
            ->info('==>', $info);
    }

    public function payOrder($token, $type, $info)
    {
        (new Logger($type))
            ->pushHandler(new RotatingFileHandler(storage_path('logs/payOrder/' . $token . '/' . $type . '.log')))
            ->info('==>', $info);
    }

    public function refund($token, $type, $info)
    {
        (new Logger($type))
            ->pushHandler(new RotatingFileHandler(storage_path('logs/refund/' . $token . '/' . $type . '.log')))
            ->info('==>', $info);
    }

    public function redPackAgent($token, $type, $info)
    {
        (new Logger($type))
            ->pushHandler(new RotatingFileHandler(storage_path('logs/redPackAgent/' . $token . '/' . $type . '.log')))
            ->info('==>', $info);
    }


}
