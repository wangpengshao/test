<?php

namespace App\Admin\Controllers\Wechat\Vote;

use App\Admin\Extensions\Tools\IconButton;
use App\Models\Vote\VoteConfig;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class VoteConfigController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {

        return $content
            ->header('投票活动')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $request = \request();
        $grid = new Grid(new VoteConfig);
        $grid->model()->where('token', $request->session()->get('wxtoken'));
        $grid->id('编号');
        $grid->title('标题');
        $grid->actions(function ($actions) {
            $actions->disableView();
            $url = url('admin/wechat/vote/blacklist');
            $actions->prepend(new IconButton($url,'黑名单管理','fa-expeditedssl'));
            $url = url('admin/wechat/vote/group') . '?a_id=' . $actions->row->id;
            $actions->prepend(new IconButton($url,'分组管理','fa-group'));
        });
        $grid->s_time('报名时间')->display(function () {
            return $this->s_time . ' ~ ' . $this->e_time;
        })->label();
        $grid->s_date('投票时间')->display(function () {
            return $this->s_date . ' ~ ' . $this->e_date;
        })->label('info');

        $grid->column('活动地址')->display(function () {
            return route('Vote::index', ['a_id' => $this->id, 'token' => $this->token]);
        })->urlWrapper();

        $grid->status('活动状态')->switch();

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
        $show = new Show(VoteConfig::findOrFail($id));

        $show->id('Id');
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new VoteConfig);
        $token = \request()->session()->get('wxtoken');
        $form->tab('基础配置', function ($form) use ($token) {
            $form->hidden('token')->default($token);
            $form->text('title', '活动标题')->rules('required');
            $options = [
                1 => '模版一[粉色系]',
                3 => '模版三[草绿系]',
                4 => '模版四[淡蓝系]',
                5 => '模版五[土豪金]',
                6 => '模版六[黄色系]',
                7 => '模版七[深粉系]',
                8 => '模版八[清新系]',
                9 => '模板九[扁平化风格]',
            ];
            $form->select('template_id', '主题模版')->options($options)->default(1)->rules('required');
            $form->switch('status', '活动状态');
            $form->text('unit_title', '单位名称')->default('作品')->help('单位字段默认为作品,例:选手、书籍.....');
            $options = [
                0 => '活动限制',
                1 => '每天限制',
            ];
            $form->radio('rules_type', '投票类型')->options($options);
            $form->number('day_n', '可投票数')->default(1)->min(1)
                ->help('如按活动限制,该项为可投票总数,按每天限制,该项为每天可投票数');
            $form->number('unit_n', '单个作品可投数')->default(1)->min(1);
            $form->datetime('s_time', '报名开始时间')->default(date('Y-m-d H:i:s'));
            $form->datetime('e_time', '报名结束时间')->default(date('Y-m-d H:i:s'));
            $form->datetime('s_date', '投票开始时间')->default(date('Y-m-d H:i:s'));
            $form->datetime('e_date', '投票结束时间')->default(date('Y-m-d H:i:s'));
        });
        $imgArray = [];
        $form->tab('页面配置', function ($form) use (&$imgArray) {
            $form->switch('broadcast_sw', '投票广播开关');
            $form->text('top_tip', '顶部公告');
            $form->number('img_height', '轮播高度')->default(400);
            $form->text('explain_a', '活动说明一(标题)');
            $form->editor('explain_at', '活动说明一(描述)');
            $form->text('explain_b', '活动说明二(标题)');
            $form->editor('explain_bt', '活动说明二(描述)');
            $form->text('explain_c', '活动说明三(标题)');
            $form->editor('explain_ct', '活动说明三(描述)');
            $form->textarea('statistical_code', '统计代码')->help('第三方统计代码');
            $form->divider();
            $form->text('share_title', '分享标题');
            $form->text('share_desc', '分享描述');
            $form->image('share_img', '分享封面')->move(materialUrl())->uniqueName();
            /* 素材库 上传图片 例子 start */
            $form->hidden(config('materialPR') . 'share_img');
            $imgArray = [config('materialPR') . 'share_img'];
            $form->ignore($imgArray);
            /* 素材库 上传图片 例子 end */
        });

        $form->tab('规则开关', function ($form) {
            $form->switch('reader_sw', '读者报名')->help('需要绑定读者证才能报名');
            $form->switch('audit_sw', '报名审核开关');
            $form->switch('sub_sw', '关注投票')->help('需要关注公众号才能进行投票');
            $form->switch('rules_ip', '黑名单限制开关')->help('包括ip和微信用户openid');;
            $form->switch('notice_sw', '被投票通知开关')->help('避免发送消息太频繁,获得20的倍数的票才会通知!');
            $options = [
                0 => '关闭',
                1 => '开启',
                2 => '开启&&审核'
            ];
            $form->radio('comment_sw', '留言开关')->options($options)->default(0)->help('暂时关闭此功能');
            $form->switch('warning_sw', '开启防刷警告规则');
            $form->embeds('warning_rule', '警告规则', function ($form) {
                $form->number('min', '限定分钟')->default(0);
                $form->number('number', '票数')->default(0)->help('限定分钟内，被投票数超过即警告,注0为不限制');
            });
            $form->embeds('lock_rule', '锁定规则', function ($form) {
                $form->number('min', '限定分钟')->default(0);
                $form->number('number', '票数')->default(0)->help('限定分钟内，被投票数超过即锁定,注0为不限制');
            });
        });

        $form->tab('轮播设置', function ($form) {
            $form->multipleImage('img', '轮播图')->move(materialUrl())->uniqueName()->removable()->sortable();
        });

        $form->saving(function (Form $form) use ($imgArray) {
            foreach ($imgArray as $k => $v) {
                if (\request()->input($v)) {
                    $imgName = substr($v, strlen(config('materialPR')));
                    $form->model()->$imgName = \request()->input($v);
                }
            }
            unset($k, $v);
        });

        $form->saved(function (Form $form) use ($token) {
            $cacheKey = 'vote:conf:' . $token . ':' . $form->model()->id;
            Cache::forget($cacheKey);
        });


        return $form;
    }
}
