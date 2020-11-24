<?php

namespace App\Services;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Processor\WebProcessor;

class ApiLogService
{
    public function recordGetToken($user_id, $info)
    {
        $logger = new Logger('auth');
        $logger->pushHandler(new RotatingFileHandler(storage_path('logs/api/getToken/date.log')));
        $logger->pushProcessor(new WebProcessor(null, ['ip']));
        $logger->info($user_id, $info);
    }

}
