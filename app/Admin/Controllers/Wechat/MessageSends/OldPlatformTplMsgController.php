<?php

namespace App\Admin\Controllers\Wechat\MessageSends;

use App\Api\Helpers\ApiResponse;
use App\Jobs\OldPlatformTplMsg;
use App\Models\Wechat\OtherConfig;
use App\Models\Wechat\TplMsgThird;
use App\Models\Wechat\Wechatapp;
use App\Models\WechatApi\GroupList;
use App\Models\WechatApi\TemplateMesList;
use App\Models\Wxuser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OldPlatformTplMsgController extends AdminController
{
    use ApiResponse;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '群发模板列表（代发旧平台）';

    public function index(Content $content)
    {
        Admin::script($this->gridScript());
        return parent::index($content);
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TplMsgThird);
        $grid->model()->orderBy('id', 'desc');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('template_id', '模板ID');
            $filter->equal('token', 'token');
            $filter->like('title', '标题');
        });
        $grid->disableCreateButton();

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
            $actions->disableEdit();
            if ($actions->row->status === 3) {
                $actions->prepend('<a class="btn btn-xs btn-default sendTplMsg" data-toggle="tooltip"  href="javascript:void(0)" data-original-title="重新发送" data-id="' . $actions->row->id . '"><i class="fa fa-send-o"></i></a>');
            }
        });

        $grid->column('token');

        $grid->column('模版信息')->display(function () {
            $str = '';
            if ($this->template_id) {
                $str .= "<strong>模版id: </strong><i>{$this->template_id}</i><br />";
            }
            if ($this->title) {
                $str .= "<strong>模版标题: </strong><i>{$this->title}</i>";
            }
            return $str;
        });

        $grid->column('te1_da', __('内容预览'))->display(function ($value) {
            $str = "<dt>{$this->title}</dt>";
            if (empty($value)) return $str;
            $str .= $this->tpl_content;
            foreach ($value as $k => $v) {
                $str = preg_replace("/(?:\{\{)$k(?:\.DATA\}\})/i", $v, $str);
            }
            return preg_replace("/\n/i", '<br>', $str);
        });
        $grid->column('redirect_type', __('点击交互'))->display(function ($value) {
            $filter = [
                0 => '无',
                1 => $this->redirect_url,
                2 => 'appid：' . $this->mini_appid . '<br> path：' . $this->mini_path,
            ];
            return Arr::get($filter, $value, '');
        });

        $grid->column('send_type', __('群发目标'))->using([
            1 => '分组',
            2 => '全部粉丝',
            3 => '绑定用户',
            4 => '指定粉丝'])
            ->label([
                1 => 'default',
                2 => 'warning',
                3 => 'primary',
                4 => 'info',
            ]);;
        $grid->column('created_at', __('创建时间'))->sortable();

        $grid->column('status', '发送状态')->using([
            0 => '未执行',
            1 => '发送完毕',
            2 => '等待发送',
            3 => '发送中',
        ])->dot([
            0 => 'default',
            1 => 'success',
            2 => 'warning',
            3 => 'danger',
        ]);
        $grid->column('发送详情')->display(function () {
            $da = [];
            if ($this->status === 3) {
                $da[] = "<strong>统计时间: </strong><i> {$this->updated_at} </i>";
                $da[] = "<strong>成功数量: </strong><i> {$this->reality_n} </i>";
                $da[] = "<strong>失败数量: </strong><i style='color: red'> {$this->failure_n} </i>";
            }
            if ($this->status === 1) {
                $da[] = "<strong>执行时间: </strong><i> {$this->sended_at} </i>";
                $da[] = "<strong>结束时间: </strong><i> {$this->updated_at} </i>";
                $da[] = "<strong>成功数量: </strong><i> {$this->reality_n} </i>";
                $da[] = "<strong>失败数量: </strong><i style='color: red'> {$this->failure_n} </i>";
            }
            return $da;
        })->implode('<br />');
        return $grid;
    }

    public function resendTplMsg(Request $request)
    {
        $msgId = $request->input('msgId');

        $tplMsgData = TplMsgThird::find($msgId);
        if (empty($tplMsgData['template_id'])) {
            return $this->message('发送失败,模版ID不能为空', 'error');
        }

        if (!array_filter($tplMsgData['te1_da'])) {
            return $this->message('发送失败,模版的内容不能为空', 'error');
        }

        $tplMsgData->status = 2;
        $tplMsgData->sended_at = date('Y-m-d H:i:s');
        $tplMsgData->save();

        OldPlatformTplMsg::dispatch($tplMsgData['token'], $tplMsgData['id'], true)->onQueue('disposable');

        return $this->message('群发任务已加入发送队列');
    }

    public function gridScript()
    {
        $sendUrl = route('oldPlatformTplMsgData.resendTplMsg');
        return <<<SCRIPT
            
            $('.sendTplMsg').on('click', function () {
                let id = $(this).data('id');
                swal({
                    type: 'question',
                    text: '确定要重新发送吗？',
                    showCancelButton: true,
                    confirmButtonText: "确定", 
                    cancelButtonText: "取消",
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: function() {
                    return new Promise(function(resolve, reject) {
                        $.ajax({
                            url: "{$sendUrl}",
                            type: "post",
                            dataType: "json",
                            data:{'msgId':id, _token: LA.token},
                            success: function (data) {
                                if(data.status=='success'){
                                 swal({html:data.message,type:"success",showConfirmButton:true});
                                 setTimeout(function(){ 
                                     $.pjax.reload('#pjax-container');
                                  }, 2000);
                                }else{
                                   swal({html:data.message,type:"error",showConfirmButton:true});
                                }
                            },
                            error:function(){
                               swal("哎呦……", "出错了！","error");
                               setTimeout(function(){ 
                                     //$.pjax.reload('#pjax-container');
                                  }, 2000);
                            }
                        });
                    });
                    },
                }).then(function(email) {
                    console.log('Ajax请求完成！')
                }).catch(swal.noop);
            });
SCRIPT;

    }
}
