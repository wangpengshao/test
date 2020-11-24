<?php

namespace App\Admin\Controllers\Wechat;

use App\Admin\Controllers\CustomView\OnlyInfo;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Reader;
use App\Models\Wechat\Wechatapp;
use Carbon\Carbon;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Facades\Cache;

class ShowdataController extends Controller
{
    private $app;

    public function showA()
    {
        $this->initApp();

        return Admin::content(function (Content $content) {
            $content->header('基础数据');
            $content->description('from wechat');
            $content->row(function ($row) {

                $row->column(4, new OnlyInfo('当前公众号粉丝数', 'users', 'green', '', $this->getFansCount()));
                $row->column(4, new OnlyInfo('绑定读者数', 'book', 'yellow', '', $this->getReaderCount(1)));
                $row->column(4, new OnlyInfo('证存储数', 'credit-card', 'red', '', $this->getReaderCount()));
                $row->column(12, new Box('一周粉丝累计', new Table(['日期', '关注途径/数量', '取关数量', '粉丝数'], $this->getUserCumulate())));

            });

        });
    }

    private function getFansCount()
    {
        $users = $this->app->user->list();
        return $users['total'];
    }

    private function getReaderCount($is_bind = 0)
    {
        return Reader::where([
            'token' => session('wxtoken'),
            'is_bind' => $is_bind
        ])->count('id');
    }
//ref_date	数据的日期
//user_source	用户的渠道，数值代表的含义如下： 0代表其他合计 1代表公众号搜索 17代表名片分享 30代表扫描二维码 43代表图文页右上角菜单
//51代表支付后关注（在支付完成页） 57代表图文页内公众号名称 75代表公众号文章广告 78代表朋友圈广告
//new_user	新增的用户数量
//cancel_user	取消关注的用户数量，new_user减去cancel_user即为净增用户数量
//cumulate_user	总用户量
    private function getUserCumulate()
    {
        return Cache::remember('getUserCumulate_' . session('wxtoken'), 360, function () {
            $userCumulate = $this->app->data_cube->userCumulate(
                Carbon::now()->subDay(7)->toDateString(),
                Carbon::now()->subDay(1)->toDateString()
            );
            $userSummary = $this->app->data_cube->userSummary(
                Carbon::now()->subDay(7)->toDateString(),
                Carbon::now()->subDay(1)->toDateString()
            );
            $array = [];
            foreach ($userCumulate['list'] as $k => $v) {
                $cumulate_user = isset($v['cumulate_user']) ? $v['cumulate_user'] : '0';
                $new_user = '';
                $cancel_user = 0;
                foreach ($userSummary['list'] as $key => $val) {
                    if ($val['ref_date'] == $v['ref_date']) {
                        if ($val['new_user'] > 0) {
                            $new_user .= $this->getSourceType($val['user_source']) . '/' . $val['new_user'] . '个' . "\n";
                        }
                        if ($val['cancel_user'] > 0) {
                            $cancel_user += $val['cancel_user'];
                        }
                    }
                }
                $array[] = [$v['ref_date'], $new_user, $cancel_user, $cumulate_user];

            }
            return $array;
        });

    }


    public function initApp()
    {
        $this->app = Wechatapp::initialize(session('wxtoken'));
    }

    public function getSourceType($source)
    {
        $type = '';
        switch ($source) {
            case 0:
                $type = '其他合计';
                break;
            case 1:
                $type = '公众号搜索';
                break;
            case 17:
                $type = '名片分享';
                break;
            case 30:
                $type = '扫描二维码';
                break;
            case 43:
                $type = '图文页右上角菜单';
                break;
            case 51:
                $type = '支付后关注(在支付完成页)';
                break;
            case 57:
                $type = '图文页内公众号名称';
                break;
            case 75:
                $type = '公众号文章广告';
                break;
            case 78:
                $type = '朋友圈广告';
                break;
        }
        return $type;
    }


}
