<?php

namespace App\Admin\Controllers\Wechat\MessageSends;

use App\Api\Helpers\ApiResponse;
use App\Jobs\WechatTemplateMsg;
use App\Models\Wechat\OtherConfig;
use App\Models\Wechat\TplMsgData;
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

class CreateTplMsgController extends AdminController
{
    use ApiResponse;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '群发模板列表';

    public function index(Content $content)
    {
        Admin::script($this->gridScript());

        $wxuser = Wxuser::getCache(session('wxtoken'));
        if ($wxuser->type != 1) {
            $content->withInfo('友情提示', '目前模板消息仅支持认证服务号使用...');
        }
        return parent::index($content);
    }

    public function create(Content $content)
    {
        Admin::script($this->formScript());

        $content->header('模板消息')->description('设置模板消息');

        $content->row(function (Row $row) {
            $row->column(3, function (Column $column) {
                $html = view('admin.diy.tplPreview')->render();
                $column->append(new Box("预览", $html));
            });
            $row->column(8, function (Column $column) {
                $column->append(new Box(" ", $this->form()));
            });
        });

        return $content;
    }

    public function edit($id, Content $content)
    {
        Admin::script($this->formScript());

        $content->row(function (Row $row) use ($id) {
            $row->column(3, function (Column $column) {
                $html = view('admin.diy.tplPreview')->render();
                $column->append(new Box("预览", $html));
            });
            $row->column(8, function (Column $column) use ($id) {
                $column->append(new Box(" ", $this->form()->edit($id)));
            });
        });

        return $content;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new TplMsgData);
        $grid->model()->where('token', session('wxtoken'))->orderBy('id', 'desc');
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('template_id', '模板ID');
            $filter->like('title', '标题');
        });

        $grid->header(function () {
            return "<div class='callout callout-success'><h4><i class='icon fa fa-warning'></i> 注意</h4>
<p>请合理使用模版消息群发功能，严格遵守 
<a target='_blank' href='https://developers.weixin.qq.com/doc/offiaccount/Message_Management/Template_Message_Operation_Specifications.html'>官方模版消息运营规范</a> ，
否则一经\"微信官方\"发现将会受到严厉惩处，轻则封接口处理、删模版，重则封号处理。</p>
<h5>发送失败原因说明:</h5>
<p><small>1.发送过程中用户取消关注公众号</small></p>
<p><small>2.微信官方接口请求超时</small></p>
<p><small>3.如存在少量发送失败的用户可忽略，如存在大量发送失败请联系工作人员进行故障排查</small></p>
<h5>群发之前务必先进行预览测试!!!确认无误再执行发送，测试步骤如下:</h5>
<p><small>1.点击操作栏 <span class='btn btn-xs btn-default'><i class='fa fa-eye'></i></span>按钮</small></p>
<p><small>2.输入接收者的 openid </small></p>
<p><small>个人微信获取 openid 的方法 : 在公众号里回复\" reopenid \" 即可获取个人专属 openid</small></p>
</div>";
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
            if ($actions->row->status === 0) {
                $actions->prepend('<a class="btn btn-xs btn-default sendTplMsg" data-toggle="tooltip"  href="javascript:void(0)" data-original-title="发送" data-id="' . $actions->row->id . '">
                                    <i class="fa fa-send-o"></i></a>');
                $actions->prepend('<a class="btn btn-xs btn-default previewSend" data-toggle="tooltip" href="javascript:void(0)" data-original-title="预览" data-id="' . $actions->row->id . '">
                                    <i class="fa fa-eye"></i></a>');
            } else {
                $actions->disableEdit();
            }
        });
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

        $grid->column('redirect_type', __('点击交互'))->display(function ($value, $column) {
            if ($value === 1) {
                return "<a href='{$this->redirect_url}' target='_blank'>网页链接</a>";
            }
            $filter = [
                0 => '无',
                1 => $this->redirect_url,
                2 => 'appid：' . $this->mini_appid . '<br> path：' . $this->mini_path,
            ];
            return Arr::get($filter, $value, '');
        });

        $grid->column('send_type', __('群发目标'))->using([
            0 => '全部粉丝',
            1 => '分组',
            2 => '绑定用户',
            3 => '指定粉丝'])
            ->label([
                0 => 'default',
                1 => 'warning',
                2 => 'primary',
                3 => 'info',
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

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(TplMsgData::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('token', __('Token'));
        $show->field('tpl_id', __('Tpl id'));
        $show->field('template_id', __('Template id'));
        $show->field('title', __('Title'));
        $show->field('content', __('Content'));
        $show->field('created_at', __('Created at'));
        $show->field('redirect_param', __('Redirect param'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $groupModel = new GroupList();
        $groupList = $groupModel->getList();

        $form = new Form(new TplMsgData);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
            $tools->disableDelete();
        });
        $form->templateData('template_id', '选择模板')->setJsonColumn('te1_da')->help('微信后台申请的模版消息ID')
            ->placeholder('必填');
        $form->embeds('te1_da', '内容:', function ($form) {
        });
        $form->radio('redirect_type', '模板跳转方式')->options(['0' => '不跳转', '1' => '网页链接', '2' => '小程序链接'])->default(0);
        $form->url('redirect_url', '外链地址')->help('示例：http://www.baidu.com');
        $form->text('mini_appid', '小程序appid')->help('小程序appid与发送模板消息的公众号必须是关联关系');
        $form->text('mini_path', '小程序页面path')->help('示例：pages/index');
        $form->divider();
        $form->radio('send_type', '群发方式')->options(['0' => '全部', '1' => '分组', '2' => '绑定用户', '3' => '指定粉丝'])->default(0);

        $form->multipleSelect('group_tag', '分组选择')->options($groupList);
        $form->textarea('openids', '指定粉丝')->help('提示：多个粉丝openid请换行输入');

        $form->saving(function (Form $form) {
            $token = session('wxtoken');
            $form->model()->token = $token;
            $originLists = TemplateMesList::getCache($token);
            foreach ($originLists['template_list'] as $k => $v) {
                if ($v['template_id'] == $form->template_id) {
                    $form->model()->title = $v['title'];
                    $form->model()->tpl_content = $v['content'];
                    break;
                }
            }
        });

        return $form;
    }

    public function previewTplMsg(Request $request)
    {
        $msgId = $request->input('msgId');
        $openid = $request->input('openid');

        if (!$request->filled(['msgId', 'openid'])) {
            return $this->message('发送预览失败', 'error');
        }
        $msgData = TplMsgData::find($msgId);
        if (empty($msgData['template_id'])) {
            return $this->message('发送失败,模版ID不能为空', 'error');
        }
        if (!array_filter($msgData['te1_da'])) {
            return $this->message('发送失败,模版的内容不能为空', 'error');
        }
        $sendData = [
            'touser' => $openid,
            'template_id' => $msgData['template_id'],
            'data' => $msgData['te1_da']
        ];
        if ($msgData['redirect_type'] == 1 && !empty($msgData['redirect_url'])) {
            $sendData['url'] = $msgData['redirect_url'];
        }
        if ($msgData['redirect_type'] == 2 && !empty($msgData['mini_appid']) && !empty($msgData['mini_path'])) {
            $sendData['miniprogram'] = [
                'appid' => $msgData['mini_appid'],
                'pagepath' => $msgData['mini_path'],
            ];
        }

        $app = Wechatapp::initialize(session('wxtoken'));
        $res = $app->template_message->send($sendData);
        if ($res['errcode'] == 0 && $res['errmsg'] == 'ok') {
            return $this->message('发送预览成功');
        } else {
            return $this->message($res['errcode'] . '：' . $res['errmsg'], 'error');
        }

    }

    public function sendTplMsg(Request $request)
    {
        $token = session('wxtoken');
        $msgId = $request->input('msgId');
        $wxuser = Wxuser::getCache($token);
        $otherConfig = OtherConfig::where('wxuser_id', $wxuser->id)->first();
        if (empty($otherConfig)) {
            return $this->message('暂无权限,请先联系工作人员添加功能权限!', 'error');
        }

        if ($otherConfig->tplmsg_group_num < 1) {
            return $this->message('剩余群发次数不足，请联系管理员添加！', 'error');
        }
        $tplMsgData = TplMsgData::find($msgId);
        if (empty($tplMsgData['template_id'])) {
            return $this->message('发送失败,模版ID不能为空', 'error');
        }

        if (!array_filter($tplMsgData['te1_da'])) {
            return $this->message('发送失败,模版的内容不能为空', 'error');
        }

        if ($tplMsgData['status'] !== 0) {
            return $this->message('状态异常，请稍后再试!', 'error');
        }

        $tplMsgData->status = 2;
        $tplMsgData->sended_at = date('Y-m-d H:i:s');
        $tplMsgData->save();

        OtherConfig::where('wxuser_id', $wxuser->id)->decrement('tplmsg_group_num', 1);
        WechatTemplateMsg::dispatch($token, $tplMsgData['id'])->onQueue('disposable');

        return $this->message('群发任务已加入发送队列');
    }

    public function formScript()
    {
        $originLists = TemplateMesList::getCache(session('wxtoken'));
        $template_list = [];
        foreach ($originLists['template_list'] as $k => $v) {
            $v['content'] = preg_replace("/\n/i", '<br>', $v['content']);
            $template_list[$v['template_id']] = $v;
        }
        $template_list = json_encode($template_list);

        return <<<SCRIPT
            const template_list= {$template_list};

            let changeTpl = function(tpl_id){
                $('#title').text(template_list[tpl_id]['title'])
                $('#default').css('display', 'none')
                $('#custom').html(template_list[tpl_id]['content'])
            }
            let init_template_id = $('#template_id').val();
            if(init_template_id){
               changeTpl(init_template_id)
            }
            $('#template_id').on('change', function(event){
                let val = $(this).val();
                if(val.length>0){
                    changeTpl(val)
                }
            });

            // 跳转方式切换
            let changeRedirectType = function(currObj){
                if(currObj == '0'){
                    $('#redirect_url').parents('div .form-group').css('display','none');
                    $('#mini_appid').parents('div .form-group').css('display','none');
                    $('#mini_path').parents('div .form-group').css('display','none');
                }
                else if(currObj == '1'){
                    $('#redirect_url').parents('div .form-group').css('display','block');
                    $('#mini_appid').parents('div .form-group').css('display','none');
                    $('#mini_path').parents('div .form-group').css('display','none');
                }
                else{
                    $('#redirect_url').parents('div .form-group').css('display','none');
                    $('#mini_appid').parents('div .form-group').css('display','block');
                    $('#mini_path').parents('div .form-group').css('display','block');
                }
            }
            $('input[name="redirect_type"]').on('ifChecked', function(event){
                changeRedirectType($(this).val()) 
            }); 
            changeRedirectType($('input[name="redirect_type"]:checked').val())
        
            // 群发方式切换
            let changeSendType = function(currObj){
                if(currObj == '1'){
                    $("select[name='group_tag[]']").parents('div.form-group').css('display','block');
                    $("textarea[name='openids']").parents('div.form-group').css('display','none');
                }
                else if(currObj == '3'){
                    $("select[name='group_tag[]']").parents('div.form-group').css('display','none');
                    $("textarea[name='openids']").parents('div.form-group').css('display','block');
                }
                else{
                    $("select[name='group_tag[]']").parents('div.form-group').css('display','none');
                    $("textarea[name='openids']").parents('div.form-group').css('display','none');
                }
            }
            $('input[name="send_type"]').on('ifChecked', function(event){
                changeSendType($(this).val()) 
            }); 
            changeSendType($('input[name="send_type"]:checked').val())
SCRIPT;
    }

    public function gridScript()
    {
        $previewUrl = route('templateMsgData.previewTplMsg');
        $sendUrl = route('templateMsgData.sendTplMsg');
        return <<<SCRIPT
            $('.previewSend').on('click', function () {
                let id = $(this).data('id');
                swal({
                    html: '请输入您的 openid',
                    input: 'text',
                    inputPlaceholder: '微信 openid',
                    showCancelButton: true,
                    confirmButtonText: "确定", 
                    cancelButtonText: "取消",
                    showLoaderOnConfirm: true,
                    allowOutsideClick: false,
                    preConfirm: function(openid) {
                    return new Promise(function(resolve, reject) {;
                        if (!openid) {
                          reject('别急呀，您还没有输入粉丝 openid呢……');
                          return;
                        }
                        $.ajax({
                            url: "{$previewUrl}",
                            type: "post",
                            dataType: "json",
                            data:{'msgId':id, 'openid':openid, _token: LA.token},
                            success: function (data) {
                                if(data.status=='success'){
                                 swal({html:data.message,type:"success",showConfirmButton:true});
                                }else{
                                   swal({html:data.message,type:"error",showConfirmButton:true});
                                }
                            },
                            error:function(){
                               swal("哎呦……", "出错了！","error");
                            }
                        });
                    });
                    },
                }).then(function(email) {
                    console.log('Ajax请求完成！')
                }).catch(swal.noop);
            });
            
            $('.sendTplMsg').on('click', function () {
                let id = $(this).data('id');
                swal({
                    type: 'question',
                    text: '确定要发送吗？',
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
