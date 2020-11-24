<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Wechat\EsIndexList;
use Encore\Admin\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Form;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class EsIndexController extends Controller
{

    public function index(Content $content, Request $request)
    {
        Admin::script($this->script());
        return $content->title('elasticsearch索引')->description('列表')
            ->row(function (Row $row) use ($request) {
                $row->column(10, function (Column $column) use ($request) {
                    $form = new Form();
                    $form->method('get');
                    $form->action($request->url());

                    $form->text('caption', '名称')->default($request->input('caption', ''));
                    $column->append((new Box('条件筛选', $form))->style('success'));
                });
                $row->column(10, $this->grid($request));
            });
    }

    protected function grid()
    {
        $grid = new Grid(new EsIndexList);
        $grid->tools(function ($tools) {
            $tools->batch(function ($batch) {
                $batch->disableDelete();
            });
        });
        $grid->disableFilter();
        $grid->disableCreateButton();
        $grid->disableExport(true);
        $grid->perPages = [50, 100];
        $grid->perPage = 20;
        $grid->column('name', '索引名称');
        $grid->column('health', '索引健康状态');
        $grid->column('count', '文档总数');
        $grid->column('size', '总数据大小');
        $grid->column('pri_size', '主分片数据大小');
        $grid->column('pri', '分片个数');
        $grid->column('rep', '从分片个数');
        $grid->column('status', '状态')->display(function ($status) {
            switch ($status) {
                case 1:
                    $str = '<span class="badge bg-green">开启</span>';
                    break;
                case 2:
                    $str = '<span class="badge bg-red">关闭</span>';
                    break;
                default:
                    $str = '<span class="badge bg-gray">开启</span>';
            }
            return $str;
        });
        $grid->actions(function ($actions) {
            if ($actions->row->status == 1) {
                $actions->append("<button class='btn btn-xs btn-warning margin-r-5 switch' data-name='{$actions->row->name}'
 data-type='2' data-token='{$actions->row->token}'>关闭</button>");
            } else {
                $actions->append("<button class='btn btn-xs btn-success margin-r-5 switch' data-name='{$actions->row->name}'
 data-type='1' data-token='{$actions->row->token}'>开启</button>");
            }
            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });
        return $grid;
    }

    // 索引开关控制
    protected function switchControl(Request $request)
    {
        //接收参数
        $name = $request->input('name');
        $type = $request->input('type');
        $hosts = config('search.hosts.0');
        // 根据条件去开关索引状态
        if ($type == 1) {
            $url = $hosts . '/' . $name . '/_open';
        } else {
            $url = $hosts . '/' . $name . '/_close';
        }
        $client = new Client();
        $response = $client->post($url);
        $response = json_decode((string)$response->getBody(), true);
        // 将表中的索引状态更新
        if ($response && $type == 1) {
            $re = ['status' => true, 'mes1' => '开启成功'];
        } elseif ($response && $type == 2) {
            $re = ['status' => true, 'mes1' => '关闭成功'];
        } else {
            $re = ['status' => false, 'mes1' => '操作失败'];
        }
        return $re;
    }

    protected function script()
    {
        $switchControl = route('es.index.operate');
        return <<<SCRIPT
        
$('.switch').on('click', function () {
    var name=$(this).data('name');
    var token = $(this).data('token');
    var type = $(this).data('type');
    if(type == 1) {
        status = 1;
        tip = '确定要开启索引状态吗?';
    } else {
        status = 2;
        tip = '确定要关闭索引状态吗?';
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
                        url: "{$switchControl}",
                        type: "post",
                        data: {"_token": LA.token,"name":name,"token":token,"type":type},
                        dataType: "json",
                        success: function (data) {
                          var type="error";
                          if(data.status==true){
                            type="success";
                          }
                          swal('',data.mes1,type);
                          $.pjax.reload('#pjax-container');
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
