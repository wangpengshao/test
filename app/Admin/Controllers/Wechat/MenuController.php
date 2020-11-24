<?php

namespace App\Admin\Controllers\Wechat;

use App\Http\Controllers\Controller;
use App\Models\Wechat\Menu;
use App\Models\Wechat\Wechatapp;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;
use Illuminate\Support\Str;

class MenuController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        Admin::script($this->clickSendMenu());
        return Admin::content(function (Content $content) {
            $content->header('公众号自定义菜单');
            $content->row(function (Row $row) {
                $row->column(10, $this->tree()->render());
                $row->column(10, function (Column $column) {
                    $headers = ['菜单名称', '类型', '内容'];
                    $rows = Menu::CurrentMenu();
                    $table1 = new Table($headers, $rows);
                    $column->row((new Box('实时预览', $table1))->style('info')->solid());

                });
            });
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('编辑菜单');
            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {
            $content->header('创建菜单');
            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return tree
     */
    protected function tree()
    {
        return Menu::tree(function (Tree $tree) {
            $tree->tools->add('<a class="btn btn-sm btn-danger sendmunu"><i class="fa fa-bars"></i>&nbsp;&nbsp;生成菜单</a>');
            $tree->query(function ($model) {
                return $model->where('token', session('wxtoken'));
            });
            $tree->branch(function ($branch) {
                $status = ($branch['status']) ?
                    "<i class='fa  fa-circle' style='color:#3c8dbc'></i>" :
                    "<i class='fa  fa-circle' style='color:#d1d5d4'></i>";
                $str = '';
                switch ($branch['type']) {
                    case 1:
                        $str .= ' 关键字回复 => ' . Str::limit($branch['data'], 60);
                        break;
                    case 2:
                        $str .= ' URL链接 => ' . Str::limit($branch['data'], 60);
                        break;
                    case 3:
                        $str .= ' 菜单扫码 ';
                        break;
                    case 4:
                        $str .= ' 小程序 ';
                        break;
                }

                if (empty($branch['children'])) {
                    $status .= "&nbsp;&nbsp;" . $str;
                }
                return "-  {$branch['title']} &nbsp;&nbsp;&nbsp;&nbsp;" . $status;
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Menu());
        $form->select('parent_id', '父级')->options(Menu::getparent())->rules('required');
        $directors = [
            '1' => '关键字回复',
            '2' => 'URL链接',
            '3' => '菜单扫码',
            '4' => '小程序',
        ];
        $form->select('type', '类型')->options($directors)->rules('required');

        $form->text('title', '菜单名称')->rules('required');
        $states = [
            'on' => ['value' => 1, 'text' => '显示', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'danger'],
        ];
        $form->switch('status', '状态')->states($states);

        $form->textarea('data', '数据')->help('此处可填: 链接、扫码值、关键字、小程序(appid、备用网页、小程序跳转路径 按这里的顺序依次回车换行填写)');

        $form->display('created_at', 'Created At');
        $form->display('updated_at', 'Updated At');

        $form->saving(function (Form $form) {
            $model = $form->model();
            if (empty($model->token)) {
                $model->token = session('wxtoken');
            }
        });
        return $form;
    }

    public function clickSendMenu()
    {
        $action = route('wechat.request.menu');
        return <<<SCRIPT
$('.sendmunu').on('click', function () {
swal({
  title: '需要生成公众号菜单?', type: 'warning',
  showCancelButton: true,
  confirmButtonText: "是的", 
  cancelButtonText: "否",
  showLoaderOnConfirm: true,
  preConfirm: function() {
    return new Promise(function(resolve, reject) {
         $.ajax({
                url: "{$action}",
                type: "get",
                dataType: "json",
                success: function (data) {
                    $.pjax.reload('#pjax-container');
                  
                    if(data.status==true){
                     swal({title:data.message,type:"success",showConfirmButton:false});
                     setTimeout(function(){ 
                         window.location.reload();
                      }, 2000);
                    }else{
                       swal({title:data.message,type:"error",showConfirmButton:false});
                    }
                },
                error:function(){
                  swal("哎呦……", "出错了！","error");
                }
            });
   
    });
  },
  allowOutsideClick: false
}).then(function() {

})
});



SCRIPT;

    }

    public function sendMenu()
    {
        $app = Wechatapp::initialize(session('wxtoken'));

        $status = $app->menu->create(Menu::menuData());

        $re = ['status' => true, 'message' => '成功'];
        if ($status['errcode'] != 0) {
            $re = ['status' => false, 'message' => $status['errmsg']];
        }
        return $re;
    }


}
