<?php

namespace App\Admin\Controllers\Wechat;

use App\Admin\Controllers\CustomMethod\OssMediaManager;
use App\Http\Controllers\Controller;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;

class PersonalstuffController extends Controller
{
    /* 自单位素材库 */
    public function imgList()
    {
        $manager = new OssMediaManager('wechat/' . session('wxtoken'));
        $data = $this->arraySort($manager->ls(), 'name');
        return view('admin.wechat.material.imglist', ['ls' => $data]);
    }

    /* u微icon库 */
    public function uweiIconList()
    {
        $manager = new OssMediaManager('uweiIcon');
        $data = $this->arraySort($manager->ls(), 'name');
        return view('admin.wechat.material.imglist', ['ls' => $data]);
    }

    public function index(Request $request)
    {
        return Admin::content(function (Content $content) use ($request) {
            $content->header('公众号素材管理');
            $content->description('...');

            $path = 'wechat/' . session('wxtoken');

            $view = $request->get('view', 'list');

            $manager = new OssMediaManager($path);

            $content->body(view("admin.diy." . $view, [
                'list' => $manager->ls(),
                'nav' => $manager->navigation(),
                'url' => $manager->urls(),
            ]));
        });
    }

    protected function arraySort($array,$keys,$sort='asc') {
        $newArr = $valArr = array();
        foreach ($array as $key=>$value) {
            $valArr[$key] = $value[$keys];
        }

        //先利用keys对数组排序，目的是把目标数组的key排好序
        ($sort == 'asc') ?  asort($valArr) : arsort($valArr);
        reset($valArr);
        foreach($valArr as $key=>$value) {
            $newArr[$key] = $array[$key];
        }
        return $newArr;
    }
}
