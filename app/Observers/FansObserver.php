<?php

namespace App\Observers;

use App\Models\Wechat\Fans;

class FansObserver
{
    public function creating(Fans $fans)
    {
        $fans->username = $fans->openid;
        $fans->password = bcrypt(md5($fans->openid));
    }

}
