<?php

namespace App\Models\Wechat;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'admin_wechat_menu';

    public function scopeCurrentMenu()
    {
        $app = Wechatapp::initialize(session('wxtoken'));
        $list = $app->menu->list();
        $menu = array_get($list, 'menu');
        $data = [];
        if (!empty($menu)) {
            foreach ($menu['button'] as $k => $v) {
                if (empty(array_get($v, 'type')) && count($v['sub_button']) > 0) {
                    $data[] = [$v['name']];
                    foreach ($v['sub_button'] as $key => $val) {
                        $data[] = $this->reTypeArray($val, true);
                    }
                    unset($key, $val);
                } else {
                    $data[] = $this->reTypeArray($v);
                }
            }
            unset($k, $v);
        }
        return $data;
    }

    public function scopeMenuData()
    {
        $data = $this->withQuery(function ($query) {
            return $query->whereToken(session('wxtoken'))->whereStatus(1);
        })->toTree();
        $menu = [];
        foreach ($data as $k => $v) {
            $children = array_get($v, 'children');

            if (empty($children)) {
                $menu[] = $this->reMenuData($v);
            } else {
                $sub_button = [];
                foreach ($children as $key => $val) {
                    $sub_button[] = $this->reMenuData($val);
                }
                unset($key, $val);
                $menu[] = ['name' => $v['title'], 'sub_button' => $sub_button];
            }
        }
        unset($k, $v);
        return $menu;
    }

    public function reMenuData($data)
    {
        if ($data['type'] == 1) {
            return [
                "type" => "click",
                "name" => $data['title'],
                "key" => $data['data']
            ];
        }
        if ($data['type'] == 2) {
            $url = filter_var($data['data'], FILTER_VALIDATE_URL);
            if (!$url) {
                $url = config('envCommon.APP_URL') . $data['data'];
            }
            $url = str_replace('{token}', session('wxtoken'), $url);
            return [
                "type" => "view",
                "name" => $data['title'],
                "url" => $url
            ];
        }
        if ($data['type'] == 3) {
            $data['data'] = ($data['data']) ?: '扫码';
            return [
                "type" => "scancode_waitmsg",
                "name" => $data['title'],
                "key" => $data['data']
            ];
        }
        if ($data['type'] == 4) {
            $result = preg_split('/[;\r\n]+/s', $data['data']);
            return [
                'type' => 'miniprogram',
                'name' => $data['title'],
                'appid' => array_get($result, 0),
                'url' => array_get($result, 1),
                'pagepath' => array_get($result, 2)
            ];
        }
    }

    public function reTypeArray($data, $child = null)
    {
        $array = [];
        $childstr = '';
        if ($child) {
            $childstr = '╚════> ';
        }
        $type = array_get($data, 'type');
        switch ($type) {
            case 'view':
                $array = [$childstr . $data['name'], 'URL链接', $data['url']];
                break;
            case 'scancode_waitmsg':
                $array = [$childstr . $data['name'], '菜单扫码', ''];
                break;
            case 'click':
                $array = [$childstr . $data['name'], '关键字回复 ', $data['key']];
                break;
            case 'miniprogram':
                $array = [$childstr . $data['name'], '小程序  ', ''];
                break;
        }
        return $array;
    }

    public function scopeGetparent($query)
    {
        return $query->where([
            'token' => session('wxtoken'),
            'parent_id' => 0
        ])->orderBy('order')->pluck('title', 'id')->prepend('root', 0)->toArray();

    }

}
