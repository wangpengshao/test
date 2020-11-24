<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wechat\Reader;

class WebStateController extends Controller
{
    public function checkMysql()
    {
        Reader::first();
        return ['status' => true];
    }

}
