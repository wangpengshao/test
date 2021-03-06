<?php

namespace App\Admin\Controllers\Wechat\MessageSends;

use App\Api\Helpers\ApiResponse;
use App\Jobs\WechatCustomMsg;
use App\Models\Wechat\CusMsgData;
use App\Models\Wechat\OtherConfig;
use App\Models\Wechat\Wechatapp;
use App\Models\WechatApi\GroupList;
use App\Models\Wxuser;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use EasyWeChat\Kernel\Messages\Text;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CreateCustomMsgController extends AdminController
{
    use ApiResponse;

    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '客服消息';

    public function index(Content $content)
    {
        Admin::script($this->gridScript());
        return parent::index($content); // TODO: Change the autogenerated stub
    }

    public function create(Content $content)
    {
        Admin::script($this->formScript());
        return parent::create($content); // TODO: Change the autogenerated stub
    }

    public function edit($id, Content $content)
    {
        Admin::script($this->formScript());
        return parent::edit($id, $content); // TODO: Change the autogenerated stub
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new CusMsgData);
        $grid->model()->where('token', session('wxtoken'));
        $grid->actions(function ($actions) {
            $actions->disableView();
            if($actions->row->status == 1){
                $actions->disableEdit();
            }else{
                $actions->prepend('<a class="btn btn-xs btn-default sendCustomMsg" data-toggle="tooltip" title="" href="javascript:void(0)" data-original-title="发送" data-id="' . $actions->row->id . '">
                                    <i class="fa fa-send-o"></i></a>');
                $actions->prepend('<a class="btn btn-xs btn-default previewCustomMsg" data-toggle="tooltip" title="" href="javascript:void(0)" data-original-title="预览" data-id="' . $actions->row->id . '">
                                    <i class="fa fa-eye"></i></a>');
            }
        });

        $grid->column('send_type', __('群发方式'))->using(['0' => '全部', '1'=> '分组', '2'=>'绑定用户', '3'=>'指定粉丝']);
        $grid->column('msg_type', __('消息类型'))->using(['text' => '文本消息', 'image'=> '图片消息', 'news'=>'图文消息一', 'mpnews'=>'图文消息二']);
        $grid->column('', __('内容'))->display(function (){
            switch ($this->msg_type){
                case 'text':
                    return $this->text_data['content'];
                    break;
//                case 'image':
//                    return '';
//                    break;
                case 'news':
                    $str = 'tittle：' . $this->news_data['title'] . "<br>" .
                        'description：' . $this->news_data['description'] . "<br>" .
                        'picurl：' . Storage:: disk('admin')->url($this->news_data['picurl']) . "<br>" .
                        'url：' . $this->news_data['url'];
                    return $str;
                    break;
                default:
                    return '';
                    break;
            }
        });
        $grid->column('status', __('发送状态'))->using(['0' => '未发送', '1'=> '已发送']);
        $grid->column('sended_at', __('发送时间'));
        $grid->column('created_at', __('创建时间'));


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
        $show = new Show(CusMsgData::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('token', __('Token'));
        $show->field('send_type', __('Send type'));
        $show->field('msg_type', __('Msg type'));
        $show->field('msg_data', __('Msg data'));
        $show->field('status', __('Status'));
        $show->field('sended_at', __('Sended at'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('deleted_at', __('Deleted at'));

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

        $form = new Form(new CusMsgData);

        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
            $tools->disableDelete();
        });

        $msg_type = [
            'text' => '文本消息',
//            'image'=> '图片消息',
            'news'=>'图文消息一(跳转外链)',
//            'mpnews'=>'图文消息二（跳转图文消息）'
        ];
        $form->radio('msg_type', '消息类型')->options($msg_type)->default('text');
        $form->embeds('text_data', ' ', function ($form) {
            $form->textarea('content', '内容')->help('必填，文字加超链接范例：< href="http://baidu.com">百度< /a>');
        });

        $form->embeds('news_data', ' ', function ($form) {
            $form->text('title', '标题')->help('必填');
            $form->textarea('description', '文本描述')->help('必填');
            $form->image('picurl', '图片')->disk('admin')->move(materialUrl() . '/newsMsg')->attribute('hideMaterial')->removable()->help('必填，支持JPG、PNG格式，较好的效果为大图640*320，小图80*80');
            $form->url('url', '跳转链接')->help('必填，包含 http 或者 http 协议头');
        });

        $form->radio('send_type', '群发方式')->options(['0' => '全部', '1'=> '分组', '2'=>'绑定用户', '3'=>'指定粉丝'])->default(0);
        $form->multipleSelect('group_tag', '分组选择')->options($groupList);
        $form->textarea('openids', '指定粉丝')->help('提示：多个粉丝openid请换行输入');

        $form->saving(function (Form $form){
            $form->model()->token = session('wxtoken');
        });

        return $form;
    }

    public function previewCustomMsg(Request $request)
    {
        $msgId = $request->input('msgId');
        $openid = $request->input('openid');

        if(!$request->filled(['msgId', 'openid'])){
            return $this->message('发送预览失败','error');
        }

        $msgData = CusMsgData::find($msgId);
        $msg = null;
        if($msgData->msg_type == 'text'){
            $msg = new Text($msgData->text_data['content']);
        }
        elseif ($msgData->msg_type == 'news'){
            $items = [
                new NewsItem([
                    'title'       => $msgData->news_data['title'],
                    'description' => $msgData->news_data['description'],
                    'url'         => $msgData->news_data['url'],
                    'image'       => Storage::disk('admin')->url($msgData->news_data['picurl']),
                ]),
            ];
            $msg = new News($items);
        }
        else{
            return $this->message('不允许的消息类型','error');
        }

        $app = Wechatapp::initialize(session('wxtoken'));
        $res = $app->customer_service->message($msg)->to($openid)->send();
        if($res['errcode'] == 0 && $res['errmsg'] == 'ok'){
            return $this->message('发送预览成功');
        }else{
            return $this->message($res['errcode'] . '：' . $res['errmsg'],'error');
        }

    }

    public function sendCustomMsg(Request $request)
    {
        $msgId = $request->input('msgId');

        $wxuser = Wxuser::getCache(session('wxtoken'));
        $otherConfig = OtherConfig::where('wxuser_id', $wxuser->id)->first();
//        $count = CusMsgData::where(['token'=>session('wxtoken'), 'status'=>1])->count();
//        if($otherConfig->custommsg_group_num > 0 && $count >= $otherConfig->custommsg_group_num){
//            return $this->message('剩余群发次数不足，请联系管理员添加！', 'error');
//        }
        if($otherConfig->custommsg_group_num < 1){
            return $this->message('剩余群发次数不足，请联系管理员添加！', 'error');
        }

        $customMsgData = CusMsgData::find($msgId);
        $customMsgData->status = 1;
        $customMsgData->sended_at = date('Y-m-d H:i:s');
        $customMsgData->save();

        OtherConfig::where('wxuser_id', $wxuser->id)->decrement('custommsg_group_num', 1);
        WechatCustomMsg::dispatch($customMsgData)->onQueue('disposable');

        return $this->message('群发任务已加入发送队列');
    }

    public function formScript()
    {
        return <<<SCRIPT

        // 消息类型切换
        let changeMsgType = function(currObj){console.log(currObj)
            $('hr').css('display','none');
            if(currObj == 'text'){
                $('#embed-news_data').css('display','none');
                $('#embed-text_data').css('display','block');
            }
            else if(currObj == 'news'){
                $('#embed-text_data').css('display','none');
                $('#embed-news_data').css('display','block'); 
            }
            else{
                
            }
        }
        $('input[name="msg_type"]').on('ifChecked', function(event){
            changeMsgType($(this).val()) 
        }); 
        changeMsgType($('input[name="msg_type"]:checked').val())

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
        $previewUrl = route('customMsgData.previewCustomMsg');
        $sendUrl = route('customMsgData.sendCustomMsg');
        return <<<SCRIPT
            $('.previewCustomMsg').on('click', function () {
                let id = $(this).data('id');
                swal({
                    html: '请输入粉丝 openid',
                    input: 'text',
                    inputPlaceholder: '粉丝 openid',
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
            
            $('.sendCustomMsg').on('click', function () {
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
