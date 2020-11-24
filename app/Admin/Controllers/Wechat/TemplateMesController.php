<?php

namespace App\Admin\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Models\WechatApi\TemplateMesList;
use App\Models\Wxuser;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class TemplateMesController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('模版消息列表');
            $content->description('....');
            $type = Wxuser::whereToken(session('wxtoken'))->value('type');
            if ($type != 1) {
                return $content->withWarning('提示', '抱歉，此功能需要公众号类型为服务号才能使用..');
            }

            $content->body($this->grid());
        });
    }


    public function grid()
    {
        return Admin::grid(TemplateMesList::class, function (Grid $grid) {

            $grid->tools(function ($tools) {
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });

            $grid->disableFilter();
            $grid->disableExport();
            $grid->disableCreateButton();
            $grid->disableActions();
            $grid->paginate(25);
            $grid->perPages([25]);
            $grid->template_id('模版ID');
            $grid->title('标题');
            $grid->primary_industry('一级行业');
            $grid->deputy_industry('二级行业');
            $grid->content('详细内容')->display(function ($str) {
                return ' <div class="jumbotron">' . $str . '</div>';
            })->style('white-space: pre-line');
//            $grid->content('详细内容')->badge()->style('white-space: pre-line');
            $grid->example('例子')->style('white-space: pre-line');


        });
    }


}
