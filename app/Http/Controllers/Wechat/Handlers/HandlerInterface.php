<?php


namespace App\Handlers;

interface HandlerInterface
{

    /**
     * @param $message
     * @return mixed
     */
    public static function run($message);

}
