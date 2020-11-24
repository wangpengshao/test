<?php

namespace App\Admin\Controllers\Wechat;

use App\Admin\Controllers\CustomView\TagList;
use App\Http\Controllers\Controller;
use App\Models\Wechat\Wechatapp;
use App\Models\WechatApi\FansList;
use App\Models\WechatApi\GroupList;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;

class FansController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        Admin::script($this->script());
        return Admin::content(function (Content $content) {

            $content->header('粉丝列表');
            $content->description('....');

            $content->body($this->grid());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(FansList::class, function (Grid $grid) {

//            $grid->filter(function ($filter) {
//                $filter->expand();
//                // 在这里添加字段过滤器
//                $filter->equal('openid');
//                $filter->disableIdFilter();
//            });

            $grid->nickname('昵称')->label('primary');
            $grid->openid()->badge('green');

            $grid->headimgurl('头像 ')->image('', 50, 50);

            $grid->sex('性别')->using([
                '2' => '<span style="color: #fa9289"><i class="fa fa-venus"></i></span>',
                '1' => '<span style="color: #3c8dbc"><i class="fa fa-mars-stroke-v"></i></span>']);

            $grid->column('subscribe_time', '关注时间')->display(function ($time) {
                return date('Y-m-d H:i:s', $time);
            });
            $grid->country('国家')->badge();
            $grid->province('省')->badge('danger');
            $grid->city('市')->badge('yellow');

            $grid->disableExport();
            $grid->disableCreation();
            $grid->disableFilter();
            $grid->tools(function ($tools) {
                $options = [
                    'all' => '全部',
                ];
                $groupModel = new GroupList();
                $options += $groupModel->getList();
                $tools->append(new TagList($options));
            });

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->disableView();
                $tagHtml = '<span data-openid="' . $actions->row->openid . '" class="tagClick" style="color: #0e7a75"><i class="fa fa-tag"></i></span>';
                $actions->append($tagHtml);
            });


        });
    }

    protected function script()
    {
        $groupModel = new GroupList();
        $options = $groupModel->getList();
        $options = json_encode($options);
        $url = route('wechat.fans.fansAddTag');

        return <<<SCRIPT
       $('.tagClick').click(function () {
       var openid=$(this).data('openid');
        swal({
            title: '为用户添加标签',
            input: 'select',
            inputOptions: {$options},
            inputPlaceholder: '请选择',
            showCancelButton: true,
            inputValidator: function (value) {
                return new Promise(function (resolve, reject) {
                          resolve();
                })
            }
        }).then(result => {
        console.log(result);
 if (result) {
           $.ajax({
            url: "{$url}",
            type: "post",
            data: {_token: LA.token,openid:openid,tagId:result},dataType: "json",
            success: function (data) {
                $.pjax.reload('#pjax-container');
                swal({title:data.message,type:"success",showConfirmButton:false,timer: 2000});
             
            },
            error:function(){
             swal("哎呦……", "出错了！","error");
            }
        });
  }
})
    })
        
        
SCRIPT;

    }

    public function fansAddTag(Request $request)
    {
        $openid = $request->input('openid');
        $tagId = $request->input('tagId');
        $app = Wechatapp::initialize(session('wxtoken'));
        $app->user_tag->tagUsers([$openid], $tagId);
        return ['status' => true, 'message' => '操作成功'];
    }


}
