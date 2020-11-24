<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Wechat\TcContent;
use App\Models\Wxuser;
use Encore\Admin\Controllers\Dashboard;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('Dashboard');
            $content->description('Description...');

            $content->row(Dashboard::title());

            $content->row(function (Row $row) {

                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::environment());
                });

                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::extensions());
                });

                $row->column(4, function (Column $column) {
                    $column->append(Dashboard::dependencies());
                });
            });
        });
    }

    public function accreditToken(Request $request, $token)
    {
        $wxname = Wxuser::where(['token' => $token])->pluck('wxname')->first();
        $request->session()->put('wxtoken', $token);
        $request->session()->put('wxname', $wxname);
        $request->session()->save();
        return ['status' => true, 'message' => '授权完成!!'];
    }

    public function news()
    {
        return Admin::content(function (Content $content) {

            $content->header('Index');
            $content->description('...');

            $content->row(view('admin.Custom.newsTitle'));

            $content->row(function (Row $row) {
                $row->column(4, function (Column $column) {
                    $title = '新功能发布';
                    $where = [
                        'status' => 1,
                        'type' => 1
                    ];
                    $list = TcContent::where($where)->orderBy('created_at', 'desc')->limit(10)->get(['title', 'description', 'created_at']);
                    $envs = [];
                    $list->each(function ($v) use (&$envs) {
                        $envs[] = ['name' => "<a href='#'>{$v['title']}</a>", 'value' => $v['created_at']];
                    });
                    $column->append(view('admin.Custom.newsPublish', compact('envs', 'title')));
                });

                $row->column(4, function (Column $column) {
                    $title = '时事中心';
                    $where = [
                        'status' => 1,
                        'type' => 2
                    ];
                    $list = TcContent::where($where)->orderBy('created_at', 'desc')->limit(10)->get(['title', 'description', 'created_at']);
                    $envs = [];
                    $list->each(function ($v) use (&$envs) {
                        $envs[] = ['name' => "<a href='#'>{$v['title']}</a>", 'value' => $v['created_at']];
                    });
                    $column->append(view('admin.Custom.newsPublish', compact('envs', 'title')));
                });

                $row->column(4, function (Column $column) {
                    $title = '业务通知';
                    $where = [
                        'status' => 1,
                        'type' => 3
                    ];
                    $list = TcContent::where($where)->orderBy('created_at', 'desc')->limit(10)->get(['title', 'description', 'created_at']);
                    $envs = [];
                    $list->each(function ($v) use (&$envs) {
                        $envs[] = ['name' => "<a href='#'>{$v['title']}</a>", 'value' => $v['created_at']];
                    });
                    $column->append(view('admin.Custom.newsPublish', compact('envs', 'title')));
                });
            });
        });
    }

}
