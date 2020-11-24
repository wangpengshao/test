<?php

namespace App\Models\WechatApi;

use App\Models\Wechat\Wechatapp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

class FansList extends Model
{
    public function paginate()
    {
        $app = Wechatapp::initialize(session('wxtoken'));

        $perPage = Request::get('per_page', 20);

        $page = Request::get('page', 1);

        $kind = Request::get('kind', 'all');

        $start = ($page - 1) * $perPage;

        $findOpenid = Request::get('openid');

        $userList = [];
        $total = 0;

        if ($findOpenid) {
            dd($findOpenid);
            $userList = $app->user->select([$findOpenid]);
            $userList = $userList['user_info_list'];
            $kind = null;
        }

        if ($kind == 'all') {
            $list = $app->user->list();

            $total = $list['total'];

            $openids = $list['data']['openid'];

            $target = array_slice($openids, $start, $perPage);

            $userList = $app->user->select($target);

            $userList = $userList['user_info_list'];

        } elseif (isset($kind) && $kind != 'all') {

            $user_tag = $app->user_tag->usersOfTag($kind, $nextOpenId = '');

            $total = $user_tag['count'];
            if ($total > 0) {

                $openids = $user_tag['data']['openid'];

                $target = array_slice($openids, $start, $perPage);

                $userList = $app->user->select($target);

                $userList = $userList['user_info_list'];
            }

        }


        $users = static::hydrate($userList);

        $paginator = new LengthAwarePaginator($users, $total, $perPage);

        $paginator->setPath(url()->current());

        return $paginator;
    }

    public static function with($relations)
    {
        return new static;
    }



}
