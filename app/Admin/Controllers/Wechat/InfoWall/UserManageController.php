<?php

namespace App\Admin\Controllers\Wechat\InfoWall;

use App\Admin\Extensions\Tools\BackButton;
use App\Admin\Extensions\Tools\IconButton;
use App\Admin\Extensions\ExcelExporter\UserListExporter;
use App\Http\Controllers\Controller;
use App\Models\InfoWall\InfoWallUserInfo;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class UserManageController extends Controller
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
            ->header('用户管理')
            ->description('description')
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
        $token = $request->session()->get('wxtoken');
        $grid = new Grid(new InfoWallUserInfo);

        $grid->disableCreateButton();
        $grid->exporter(new UserListExporter());
        $grid->disableExport(false);
        $grid->model()->where('token', $token);
        $grid->model()->orderBy('id', 'desc');
        if ($request->filled('l_id')) {
            $grid->model()->where('l_id', $request->get('l_id'));
        }
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->between('created_at', '参与时间')->datetime();
                $filter->equal('name', '姓名');
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->where('nickname', 'like', '%' . $input . '%');
                }, '微信昵称')->inputmask([], $icon = 'wechat');
            });
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            if ($actions->row->status == 1) {
                $actions->append("<button class='btn btn-xs btn-success pull-black' data-id='{$actions->row->id}'>拉黑</button>");
            }
            $url = route('infowall.newsList', ['user_id' => $actions->row->id]);
            $actions->append(new IconButton($url, '留言列表', 'fa-list'));
        });

        $grid->tools(function ($tools) {
            $tools->append(new BackButton(route('infowall.config'), '返回活动'));
        });
        $grid->column('rdid', '读者证');
        $grid->column('username', '姓名');
        $grid->column('nickname', '微信昵称');
        $grid->column('headimgurl', '微信头像')->image('', 50, 50);
        $grid->column('phone', '手机号码');
        $grid->column('status', '状态')->display(function ($status) {
            if ($status == 2) {
                $str = '<span class="badge bg-black">被拉黑</span>';
            } else {
                $str = '<span class="badge bg-green">正常</span>';
            }
            return $str;
        });
        $grid->column('created_at', '参与时间')->sortable();

        return $grid;
    }

    /**
     * time  2020.4.1.
     *
     * @content  拉黑用户
     *
     * @author  wsp
     */
    protected function pullBlack()
    {
        $request = request();
        $id = $request->input('id');
        $res = InfoWallUserInfo::where('id', $id)->update(['status' => 2]);
        if ($res) {
            return ['status' => true, 'mes' => '操作成功'];
        } else {
            return ['status' => false, 'mes' => '操作失败'];
        }
    }

    protected function script()
    {
        $url = route('userManage.pullBlack');
        return <<<SCRIPT
        
$('.pull-black').on('click', function () {
    let id=$(this).data('id');
    let status = 2;
    swal({
         title: '确定要拉黑吗',
         type: 'question',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$url}",
                        type: "post",
                        data: {"_token": LA.token,"id":id},
                        dataType: "json",
                        success: function (data) {
                          var type="error";
                          if(data.status==true){
                            type="success";
                          }
                         $.pjax.reload('#pjax-container');
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
    }).catch(swal.noop)

});

SCRIPT;
    }

}
