<?php

namespace App\Admin\Controllers\Wechat\InfoWall;

use App\Admin\Extensions\Tools\BackButton;
use App\Admin\Extensions\Tools\CheckAction;
use App\Admin\Extensions\ExcelExporter\NewsListExporter;
use App\Models\InfoWall\InfoWallNewsList;
use App\Http\Controllers\Controller;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class NewsListController extends Controller
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
            ->header('消息列表')
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
        $grid = new Grid(new InfoWallNewsList);
        $grid->disableExport(false);
        $grid->exporter(new NewsListExporter());
        $grid->disableCreateButton();
        $grid->model()->where('token', $token);
        $grid->model()->orderBy('id', 'desc');
        if ($request->filled('l_id')) {
            $grid->model()->where('l_id', $request->get('l_id'));
        }
        if ($request->filled('user_id')) {
            $grid->model()->where('user_id', $request->get('user_id'));
        }
        // 如果不是活动页面提交过来的数据，则需要去关联活动表，去关联活动的审核状态
        $grid->model()->with('hasOneAct');
        $grid->model()->with('hasOneUser');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->between('created_at', '参与时间')->datetime();
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->where(function ($query) {
                    $input = $this->input;
                    $query->whereHas('hasOneUser', function ($query) use ($input) {
                        $query->where('nickname', 'like', '%' . $input . '%');
                    });
                }, '微信昵称')->inputmask([], $icon = 'wechat');
            });
        });

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
            // 如果is_check为空，则说明是从消息列表菜单进来的
            if ($actions->row->hasOneAct['is_check'] == 1 && $actions->row->status == 0) {
                $actions->append("<button class='btn btn-xs btn-success news-update' data-type='1' data-id='{$actions->row->id}'>通过</button>");
                $actions->append("<button class='btn btn-xs btn-yellow news-update' data-type='2' data-id='{$actions->row->id}'>不通过</button>");
            }
            if ($actions->row->is_shelf == 1) {
                $actions->append("<button class='btn btn-xs btn-danger is-shelf' data-id='{$actions->row->id}'>下架</button>");
            }
        });

        $grid->tools(function ($tools) {
            $tools->append(new BackButton(route('infowall.config'), '返回活动'));
            $tools->append(new BackButton(route('userManage.index'), '返回用户管理'));
            $tools->batch(function ($batch) {
                $batch->disableDelete();
                $batch->add('通过', new CheckAction(1));
                $batch->add('拒绝', new CheckAction(2));
            });
        });

        $grid->column('hasOneUser.username', '姓名');
        $grid->column('hasOneUser.nickname', '微信昵称');
//        $grid->column('hasOneUser.headimgurl', '微信头像')->image('', 50, 50);
        $grid->column('hasOneUser.phone', '手机号码');
//        $grid->column('hasOneUser.address', '地址');
        $grid->column('content', '消息内容');
        $grid->column('status', '审核状态')->display(function ($status) {
            if ($status == 1) {
                $str = '<span class="badge bg-green">已通过</span>';
            } elseif ($status == 2) {
                $str = '<span class="badge bg-red">未通过</span>';
            } else {
                $str = '<span class="badge bg-yellow">未审核</span>';
            }
            return $str;
        });
        $grid->column('is_shelf', '下架状态')->display(function ($is_shelf) {
            if ($is_shelf == 2) {
                $str = '<span class="badge bg-red">已下架</span>';
            } else {
                $str = '<span class="badge bg-yellow">未下架</span>';
            }
            return $str;
        });
        $grid->column('created_at', '参与时间')->sortable();

        return $grid;
    }

    /**
     * time  2020.4.20.
     *
     * @content  留言批量审核
     *
     * @author  wsp
     */
    public function check(Request $request)
    {
        $ids = $request->input('ids');
        $action = $request->input('action');
        $ids = explode(',', $ids);
        if (!empty($ids)) {
            if ($action == 1) {
                InfoWallNewsList::whereIn('id', $ids)->update(['status' => 1]);
            } else {
                InfoWallNewsList::whereIn('id', $ids)->update(['status' => 2]);
            }
        }
    }

    /**
     * time  2020.4.20.
     *
     * @content  留言单个审核
     *
     * @author  wsp
     */
    public function singleCheck(Request $request)
    {
        $id = $request->input('id');
        $status = $request->input('status');
        $res = InfoWallNewsList::where('id', $id)->update(['status' => $status]);
        if ($res) {
            $re = ['status' => true, 'mes' => '更新成功!'];
        } else {
            $re = ['status' => false, 'mes' => '更新失败!'];
        }
        return $re;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(InfoWallNewsList::findOrFail($id));

        $show->id('Id');
        $show->rdid('Rdid');
        $show->openid('Openid');
        $show->token('Token');
        $show->created_at('Created at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new InfoWallNewsList);
        $form->display('id', 'ID');
        $form->text('rdid', 'Rdid');
        $form->text('nickname', 'nickname');
        $form->text('openid', 'Openid');
        $form->text('content', '消息内容');
        $form->switch('status', 'Status');
        $form->text('token', 'Token');
        $form->number('gather_id', 'gather_id');
        $form->number('l_id', 'l_id');
        $form->display('created_at', 'Created At');
        $form->display('updated_at', 'Updated At');
        return $form;
    }

    /**
     * time  2020.4.1.
     *
     * @content  下架操作
     *
     * @author  wsp
     */
    protected function shelf()
    {
        $request = request();
        $id = $request->input('id');
        $res = InfoWallNewsList::where('id', $id)->update(['is_shelf' => 2]);
        if ($res) {
            return ['status' => true, 'mes' => '下架成功'];
        } else {
            return ['status' => false, 'mes' => '下架失败'];
        }
    }

    protected function script()
    {
        $url = route('newsList.singleCheck');
        $shelf_url = route('newsList.shelf');
        return <<<SCRIPT
        
$('.news-update').on('click', function () {
    let id=$(this).data('id');
    let type=$(this).data('type');
    let status = '';
    let tip = ''; // 操作提示
    if(type == 1) {
        status = 1;
        tip = '确认通过?';
    } else {
        status = 2;
        tip = '确认不通过?';
    }
    swal({
         title: tip,
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
                        data: {"_token": LA.token,"status":status,'id':id},
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

$('.is-shelf').on('click', function () {
    let id=$(this).data('id');
    swal({
         title: '确认下架吗',
         type: 'question',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$shelf_url}",
                        type: "post",
                        data: {"_token": LA.token,"is_shelf":2,'id':id},
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
