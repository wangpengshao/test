<?php

namespace App\Admin\Controllers\Wechat\LuckyDraw;

use App\Admin\Extensions\Tools\IconButton;
use App\Models\LuckyDraw\LuckyDraw01;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class LuckyDraw01Controller extends Controller
{
    use HasResourceActions;

    protected $typeArr = ['0' => '按活动期算', '1' => '按天数算'];
    protected $gatherArr = ['1' => '真实姓名', '2' => '手机号码', '3' => '身份证号'];
    protected $unitType = ['0' => '微信标识', '1' => '证号'];

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('幸运大转盘')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed   $id
     * @param Content $content
     *
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
     * @param mixed   $id
     * @param Content $content
     *
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
     *
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
        $grid = new Grid(new LuckyDraw01);
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->like('title', '活动名称');
        });
        $grid->model()->where('token', session('wxtoken'));
        $grid->title('活动名称');
        $grid->image('活动封面')->image('', 80, 80);
        $grid->type('次数类型')->using([
            '0' => '<span class="badge bg-green">按活动期算</span>',
            '1' => '<span class="badge bg-yellow">按天数算</span>',
        ]);
        $grid->count('抽奖次数');
        $grid->start_at('开始时间');
        $grid->end_at('结束时间');
        $grid->status('活动状态')->switch();

        $grid->actions(function ($actions) {
            $url = route('type01List.index', ['l_id' => $actions->row->id]);
            $actions->append(new IconButton($url, '当前活动抽奖列表', 'fa-list'));

            $url = route('luckyDraw.prize', ['lucky_type' => 1, 'l_id' => $actions->row->id]);
            $actions->append(new IconButton($url, '奖品管理', 'fa-gift'));
        });

        return $grid;
    }

    /*
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(LuckyDraw01::findOrFail($id));
        $show->id('抽奖地址')->as(function ($id) {
            return route('LuckyDraw01::home', ['token' => session('wxtoken'), 'l_id' => $id]);
        });
        $show->updated_at('最后编辑时间');
        $show->divider();
        // 抽奖总人数(openid分组)
        $totalNumber = DB::table('wechat_luckydraw_01_list')
            ->where(['l_id'=>$id, 'token'=>session('wxtoken')])
            ->select(DB::raw("count(*) as count"), 'openid')
            ->groupBy('openid')
            ->pluck('count', 'openid')
            ->count();
        $show->diy1('抽奖总人数:')->as(function () use ($totalNumber) {
            return $totalNumber;
        })->badge();
        $totalFrequency = DB::table('wechat_luckydraw_01_list')
            ->where(['l_id'=>$id, 'token'=>session('wxtoken')])
            ->count();
        $show->diy2('抽奖总次数:')->as(function () use ($totalFrequency) {
            return $totalFrequency;
        })->badge();
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new LuckyDraw01);
        $form->text('title', '活动名称')->required();
        $form->switch('status', '活动状态');
        $form->datetimeRange('start_at', 'end_at', '活动时间')->required();
        $form->image('image', '活动封面')->move(materialUrl())->uniqueName()->removable();
        $form->rate('no_weight', '不中奖概率')->default(0);
//        $form->radio('unit_type', '单位计算')->options($this->unitType)->stacked()
//            ->help('用于抽取次数记录计算,微信标识则是微信用户openid做单位计算,如证号单位请设置好绑定读者才能抽奖');
        $form->radio('type', '抽奖类型')->options($this->typeArr);
        $form->number('number', '可抽次数')->help('如按天算,这里参数就是用户每天可抽次数,如按期数算，则是当前活动用户可抽次数')->default(0);
        $form->number('all_number', '可抽总次数')->help('这个总次数,仅对天数类型的时候起作用!')->default(0);
        $form->number('all_winning', '可中奖次数')->help('中奖达到设置的次数之后将不会再中奖,0为不限制次数!')->default(0);
        $form->switch('is_subscribe', '需要关注?')->help('开启则会强制用户关注公众号才可进行正常抽奖!');
        $form->text('sub_tip', '未关注提示')->help('例:请长按识别关注xxxx公众号,回复xx关键字即可参与抽奖');
        $form->checkbox('gather', '需要收集信息?')->options($this->gatherArr);
        $form->switch('is_bind', '绑定读者?');
        $form->number('integral', '消耗积分')->help('每次抽奖需要消耗积分,不消耗则填0(注意:需要开启绑定读者证)')->default(0);
        $form->hidden('token')->default(session('wxtoken'));
        $form->text('tip', '提示');
        $form->text('qq', '联系QQ(兑奖)');
        $form->text('phone', '联系电话(兑奖)');
        $form->url('check_url', '抽奖检查接口')->help('抽奖资格检查接口,由第三方提供');
        $form->editor('describe', '活动规则');

        $form->divider();
        $form->text('share_title', '分享标题');
        $form->text('share_desc', '分享描述');
        $form->image('share_img', '分享封面')->move(materialUrl())->uniqueName();

        /* 素材库 上传图片 例子 start */
        $form->hidden(config('materialPR') . 'image');
        $form->hidden(config('materialPR') . 'share_img');
        $imgArray = [config('materialPR') . 'image', config('materialPR') . 'share_img'];
        $form->ignore($imgArray);
        /* 素材库 上传图片 例子 end */
        $form->saving(function (Form $form) use ($imgArray) {
            foreach ($imgArray as $k => $v) {
                if (\request()->input($v)) {
                    $imgName = substr($v, strlen(config('materialPR')));
                    $form->model()->$imgName = \request()->input($v);
                }
            }
            unset($k, $v);
        });

        return $form;
    }
}
