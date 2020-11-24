<?php

namespace App\Models\WechatApi;

use App\Models\Wechat\Wechatapp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request;

class GroupList extends Model
{

    public function paginate()
    {
        $perPage = Request::get('per_page', 20);

        $page = Request::get('page', 1);

        $start = ($page - 1) * $perPage;

        $app = Wechatapp::initialize(session('wxtoken'));
        $tagList = $app->user_tag->list();
        $tagList = $tagList['tags'];

        $total = count($tagList);

        $tags = static::hydrate($tagList);

        $paginator = new LengthAwarePaginator($tags, $total, $perPage);

        $paginator->setPath(url()->current());

        return $paginator;
    }

    public static function with($relations)
    {
        return new static;
    }

    public function findOrFail($id)
    {
        $app = Wechatapp::initialize(session('wxtoken'));
        $tagList = $app->user_tag->list();
        $tagList = $tagList['tags'];
        $tag = array_first($tagList, function ($value) use ($id) {
            return $value['id'] == $id;
        });
        return static::newFromBuilder($tag);
    }

    public function save(array $options = [])
    {
        $data = $this->getAttributes();
        $app = Wechatapp::initialize(session('wxtoken'));

        if (array_only($data, 'id')) {
            ['id' => $tagId, 'name' => $name] = $data;
            $app->user_tag->update($tagId, $name);
        } else {
            $app->user_tag->create($data['name']);
        }
    }

    public static function deleteIds(array $ids = [])
    {
        $app = Wechatapp::initialize(session('wxtoken'));
        $state = true;
        foreach ($ids as $k => $v) {
            $status = $app->user_tag->delete($v);
            if ($status['errcode'] != 0) {
                $state = false;
            }
        }
        return $state;
    }

    public function getList()
    {
        $app = Wechatapp::initialize(session('wxtoken'));
        $tagList = $app->user_tag->list();
        $tagList = array_get($tagList, 'tags', []);
        return array_column($tagList, 'name', 'id');
    }


}
