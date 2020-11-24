<?php

namespace App\Admin\Controllers\Wechat\InfoWall;

use App\Admin\Extensions\Tools\BackButton;
use App\Models\InfoWall\InfoWallDanMuTpl;
use App\Http\Controllers\Controller;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;

class TplShareController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        Admin::script($this->script());
        return $content
            ->header('弹幕模板')
            ->description('共享')
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $request = request();
        $l_id = $request->input('l_id');
        $is_share = $request->input('is_share');
        $grid = new Grid(new InfoWallDanMuTpl);
        $grid->model()->where(['is_share' => 1])->where('token', '<>', session('wxtoken'))->with('parent');
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->like('title', '模板类名');
        });
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(route('infowall.config'), '返回活动'));
        });
        $grid->column('p_name', '模板类名');
        // 通过关联表后将关联数据进行整合，避免sql(n+1)的情况
        $grid->column('parent', '添加状态')->display(function ($parent) {
            if ($parent) {
                return "<span class='label label-success'>已添加</span>";
            } else {
                return "<span class='label label-warning'>未添加</span>";
            }
        });
        $grid->actions(function ($actions) use($l_id,$is_share){
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            // 如果已经添加了，则不需要显示按钮
            if (!$actions->row->parent['id']) {
                $actions->append("<button class='btn btn-xs btn-success margin-r-5 addTpl' data-id='{$actions->row->id}' data-l_id='{$l_id}'
 data-sid = '{$is_share}'>添加</button>");
            }
        });
        return $grid;
    }

    public function addTpl(Request $request)
    {
        $id = $request->input('id');
        $l_id = $request->input('l_id');
        $is_share = $request->input('sid');
        // 先查询该分享id在当前馆下是否已经添加过了
        $where = [
            's_id' => $id,
            'token' => session('wxtoken')
        ];
        $exists = InfoWallDanMuTpl::where($where)->exists();
        if ($exists) {
            $re = ['status' => true, 'mes' => '添加成功!'];
            return $re;
        }
        // 查出模板表中的模板数据
        $res = InfoWallDanMuTpl::where('id', $id)->first();
        // 添加到表中
        $data = [
            'p_name' => $res['p_name'],
            's_name' => $res['s_name'],
            'token' => session('wxtoken'),
            'l_id' => $l_id,
            'is_share' => $is_share,
            's_id' => $id,
            'created_at' => date('Y-m-d H:i:s', time()),
            'updated_at' => date('Y-m-d H:i:s', time())
        ];
        $res = InfoWallDanMuTpl::insert($data);
        if ($res) {
            $re = ['status' => true, 'mes' => '添加成功!'];
        } else {
            $re = ['status' => false, 'mes' => '添加失败!'];
        }
        return $re;
    }

    protected function script()
    {
        $addTplUrl = route('tplShare.addTpl');
        return <<<SCRIPT
$('.addTpl').on('click', function () {
    var id=$(this).data('id');
    var l_id=$(this).data('l_id');
    var sid=$(this).data('sid');
    swal({
         title: '确定要添加模板?',
         type: 'question',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$addTplUrl}",
                        type: "post",
                        data: {"_token": LA.token, "id":id, "l_id":l_id, "sid":sid},
                        dataType: "json",
                        success: function (data) {
                          var type="error";
                          if(data.status==true){
                            type="success";
                            $.pjax.reload('#pjax-container');
                          }
                          swal('',data.mes,type);
                        },
                        error:function(){
                         swal("哎呦……", "出错了！","error");
                        }
                    });
            
                });
              },
         }).then(result => {
         console.log(result)
    })

});
SCRIPT;
    }

}
