<?php

namespace App\Admin\Controllers\Wechat\Notice;

use App\Admin\Extensions\Tools\BackButton;
use App\Admin\Extensions\Tools\IconButton;
use App\Api\Helpers\ApiResponse;
use App\Jobs\GatherTask;
use App\Models\Notice\EsNoticeRecord;
use App\Models\Notice\NoticeRecord;
use App\Models\Notice\NoticeTask;
use Carbon\Carbon;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;

class NoticeTaskController extends AdminController
{
    use ApiResponse;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '催还通知任务';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        Admin::script($this->script());
        $token = request()->session()->get('wxtoken');

        $grid = new Grid(new NoticeTask);
        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->disableExport();

        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->date('created_at', '发起时间');
        });

        $grid->actions(function (Grid\Displayers\Actions $actions) {
//            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
            if ($actions->row->total_n > 0) {
                //存在数据的时候才可以进行核查
                $url = route('wechat.notice-tasks.query', $actions->row->id);
                $actions->append(new IconButton($url, '核对记录', 'fa-search'));
            }
            if ($actions->row->status == -1) {
                $actions->append("<button class='btn btn-xs btn-warning margin-r-5 retry' data-id='{$actions->row->id}'
 data-token='{$actions->row->token}'>异常重试</button>");
            }
        });

        $grid->tools(function ($tools) {
            $tools->append(new BackButton(route('wechat.expire-notices'), '返回配置中心'));
        });

        $grid->model()->where('token', $token)->orderBy('id', 'desc');

        $grid->column('id', '编号#');
        $grid->column('created_at', '发起时间')->sortable()->date('Y-m-d')->label('primary');

        $grid->column('conf_data', '应还时间(借阅)')->display(function ($conf) {
            $conf = json_decode($conf, true);
            if (isset($conf['day_n'])) {
                return Carbon::parse($this->created_at)->addDays($conf['day_n'])->toDateTimeString();
            }
            return '';
        })->date('Y-m-d')->label('primary');

        $grid->column('total_n', '催还数量')->badge('red');
        $grid->column('valid_n', '有效数量(已绑定)')->badge('black');
        $grid->column('success_n', '发送成功数量')->badge('green');
        $grid->column('is_retry', '是否重试')->bool(['1' => true, '0' => false]);
        $grid->column('status', '任务状态')->using([
            -1 => '异常',
            0 => '创建',
            1 => '数据采集中',
            2 => '等待执行',
            3 => '执行中',
            4 => '执行完成',
        ])->dot([
            -1 => 'danger',
            0 => 'default',
            1 => 'warning',
            2 => 'info',
            3 => 'primary',
            4 => 'success',
        ]);
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(NoticeTask::findOrFail($id));
        $show->panel()->tools(function ($tools) {
            $tools->disableEdit();
//                $tools->disableList();
            $tools->disableDelete();
        });
        $show->field('id', '编号#');
        $show->field('token', __('Token'));
//        $show->field('last_openid', __('Last openid'));
        $show->field('last_page', __('Last page'));
        $show->field('last_id', __('Last id'));
        $show->field('status', '任务状态')->using([
            -1 => '异常',
            0 => '创建',
            1 => '数据采集中',
            2 => '等待执行',
            3 => '执行中',
            4 => '执行完成',
        ])->dot([
            -1 => 'danger',
            0 => 'default',
            1 => 'warning',
            2 => 'info',
            3 => 'primary',
            4 => 'success',
        ]);
        $show->field('total_n', '催还数量');
        $show->field('valid_n', '有效数量(已绑定)');
        $show->field('success_n', '成功数量');
        $show->field('conf_data', '详细')->json();
        $show->field('is_retry', '是否重试');
        $show->field('retry_at', '重试时间');
        $show->field('exception_info', '异常信息');
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    public function queryRecord(Request $request)
    {
        $token = $request->session()->get('wxtoken');
        $t_id = $request->route('id');
        $task = NoticeTask::where('token', $token)->find($t_id);
        if (!$task) {
            admin_error('提示', '非法访问!');
            return back();
        }
        $content = new Content();
        $content->title('催还推送记录');
        $content->description('列表');
        $model = $task->is_migrate !== 1 ? new NoticeRecord() : new EsNoticeRecord();
        $grid = new Grid($model);
        $grid->model()->where('token', $token)->where('t_id', $t_id);
        $grid->paginate(10);
        $grid->perPages([10]);
        $grid->disableCreateButton();
        $grid->disableRowSelector();
        $grid->disableExport();
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->expand();
            $filter->equal('openid', 'openid');
            $filter->equal('rdid', '读者证号');
        });
        $grid->disableActions();
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(route('wechat.notice-tasks'), '返回任务列表'));
        });

        if ($task->is_migrate !== 1) {
            $grid->column('id', '编号#');
        } else {
            $grid->column('_id', '编号#');
        }

        $grid->column('rdid', '读者证号');
        $grid->column('openid', 'openid');

        $grid->column('info', '超期详细信息')->table([
            'title' => '题名',
            'barcode' => 'barcode(条码号)',
            'loanday' => '借阅天数',
            'overday' => '超期天数',
            'loandate' => '借出日期',
            'returndate' => '应还日期',
        ])->hide();

        $grid->column('status', '发送状态')->display(function ($status) {
            if ($status == 1) {
                return '<span class="label label-success">发送成功</span>';
            }
            if ($this->is_bind == 1) {
                return '<span class="label label-danger">发送失败</span>';
            }
            return '<span class="label label-warning">尚未绑定</span>';
        });

        $grid->column('send_at', '发送时间');
        $grid->column('created_at', '创建时间');

        return $content->body($grid);
    }

    public function retry(Request $request)
    {
        $token = request()->session()->get('wxtoken');
        if (!$request->filled(['token', 'id']) || $token != $request->input('token')) {
            return $this->failed('缺少必填参数');
        }

        $first = NoticeTask::where([
            'token' => $token,
            'id' => $request->input('id')
        ])->first();
        if (!$first) {
            return $this->failed('非法访问');
        }
        $first->status = 0;
        $first->is_retry = 1;
        $first->save();
        GatherTask::dispatch('perform', $first->id);
        return [
            'mes1' => '操作成功',
            'mes2' => '任务进入队列执行中',
            'status' => true
        ];
    }

    protected function script()
    {
        $retryURL = route('wechat.notice-tasks.retry');
        return <<<SCRIPT
$('.retry').on('click', function () {
    var id=$(this).data('id');
    var token = $(this).data('token');
    swal({
         title: '确认要手动重启任务吗？',
         type: 'warning',
         text:'提示:请先确认openlib是否正常运行！',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$retryURL}",
                        type: "post",
                        data: 
                        {"_token": LA.token,
                        "id":id,
                        "token":token
                        },
                        dataType: "json",
                        success: function (data) {
                           if(data.status==true){
                            swal(data.mes1,data.mes2,'success');
                            $.pjax.reload('#pjax-container');
                          }else{
                            swal(data.mes1,data.mes2,'error');
                          }
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
