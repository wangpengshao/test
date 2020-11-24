<?php

namespace App\Http\Controllers\Web\LuckyDraw;

use App\Http\Controllers\Controller;

class BaseController extends Controller
{
    public function getTypeImage($prize)
    {
        $type = $prize;
        $image = '';
        if (!is_numeric($prize)) {
            $type = $prize['type'];
            $image = $prize['image'];
        }

        if (empty($image)) {
            switch ($type) {
                case 1:
                    $image = asset('wechatWeb/LuckyDraw/common/image/new/integral.png');
                    break;
                case 2:
                    $image = asset('wechatWeb/LuckyDraw/common/image/new/redPack.png');
                    break;
            }
        }
        return $image;
    }


}
