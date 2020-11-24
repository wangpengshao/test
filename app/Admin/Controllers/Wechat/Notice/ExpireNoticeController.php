<?php

namespace App\Admin\Controllers\Wechat\Notice;

use App\Models\Notice\ExpireNotice;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;
use Encore\Admin\Form as SubmitForm;
use Illuminate\Routing\Controller;

class ExpireNoticeController extends Controller
{
    use HasResourceActions;

    /**
     * Title for current resource.
     *
     * @var string
     */
//    protected $title = 'App\Models\Notice\ExpireNotice';
    protected $title = '催还模版消息通知';

    protected function index(Content $content)
    {
        $token = request()->session()->get('wxtoken');
        $first = ExpireNotice::where('token', $token)->first();
        $form = new SubmitForm(new ExpireNotice);
        $url = ($first) ? route('wechat.expire-notices.up', $first->id) : route('wechat.expire-notices.add');
        $form->setAction($url);

        $form->disableEditingCheck();
        $form->disableCreatingCheck();
        $form->disableViewCheck();
        $form->tools(function (SubmitForm\Tools $tools) {
            $tools->disableDelete();
            $tools->disableView();
            $tools->disableList();
        });

        $form->setTitle(' ');

        $form->html('<div class="box-body"><ol>
                <li class="text-yellow">openlib的版本 >= V3.5.1.20200113,如不符合请安排升级再使用该功能</li>
                <li class="text-yellow">公众号必须拥有发送模版消息功能的权限</li>
              </ol></div>', '用前须知');

        $states = [
            'on' => ['value' => 1, 'text' => '启用', 'color' => 'success'],
            'off' => ['value' => 0, 'text' => '停用', 'color' => 'info'],
        ];
        $form->switch('status', '功能状态')->states($states);
        $form->number('day_n', '到期天数 ')->min(0)->default(0)->required()->help('值为距离多少天之后过期');
        $form->text('libcode', '分馆代码')->required();

        $form->time('time_at', '通知时间')->format('HH:mm')->required()
            ->help('为了保证发送的稳定性,请尽量将通知时间设置在07:00~09:00之间,避免在高峰时段发送通知');

        $form->templateData('template_id', '模版列表')->setJsonColumn('te1_da')
            ->help('微信公众号后台"已申请"的模版消息,可使用 "占位符",占位符的位置会替换成真实数据');

        $form->embeds('te1_da', '模版内容:', function ($form) {
        });
        $form->html('<div class="box-body">
              <dl class="dl-horizontal">
                <dt>book_mark</dt>
                <dd>将超期书籍拼接文本例:《西游记》,《三国志》......</dd>
                <dt>rdid_mark</dt>
                <dd>读者的证号</dd>
                <dt>name_mark</dt>
                <dd>读者姓名</dd>
                <dt>expire_time_mark</dt>
                <dd>借阅过期时间</dd>
              </dl>
            </div>', '模版占位符:');
        $form->url('redirect_url', '跳转链接')->help('模版跳转链接,为空则不进行跳转,占位符:{token}');

        $form->hidden('token')->default($token);

        $form->display('created_at', '创建时间');
        $form->display('updated_at', '编辑时间');

        if ($first) {
            $form = $form->edit($first->id);
        }

        $content->title($this->title)->description('配置');
        return $content->body($form);
    }


    protected function form()
    {
        $form = new SubmitForm(new ExpireNotice);
        $form->hidden('token');

        $form->switch('status', '功能状态');
        $form->number('day_n', '到期天数 ')->min(0)->default(0)->required()->help('值为距离多少天之后过期');
        $form->text('libcode', '分馆代码')->required();
        $form->time('time_at', '通知时间')->format('HH:mm')->required();

        $form->templateData('template_id', '模版列表')->setJsonColumn('te1_da')->help('微信后台申请的模版消息ID')
            ->placeholder('必填')->required();
        $form->embeds('te1_da', '内容:', function ($form) {
        });
        $form->saved(function () {
            admin_toastr('操作成功', 'success');
            return back();
        });
        return $form;
    }
}
