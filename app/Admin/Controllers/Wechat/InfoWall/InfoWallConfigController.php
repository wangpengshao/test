<?php

namespace App\Admin\Controllers\Wechat\InfoWall;

use App\Admin\Extensions\Tools\IconButton;
use App\Models\InfoWall\InfoWallConfig;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class InfoWallConfigController extends Controller
{
    use HasResourceActions;

    protected $typeArr = ['0' => '按活动期算', '1' => '按天数算'];
    protected $showArr = ['0' => '发送时间', '1' => '随机'];
    protected $gatherArr = ['1' => '真实姓名', '2' => '手机号码', '3' => '物流地址'];

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
            ->header('消息上墙')
            ->description('活动配置')
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
        $grid = new Grid(new InfoWallConfig);
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->like('title', '活动标题');
        });
        $grid->model()->where('token', session('wxtoken'));
        $grid->title('活动标题');
        $grid->image('活动封面')->image('', 80, 80);
        $grid->type('次数类型')->using([
            '0' => '<span class="badge bg-green">按活动期算</span>',
            '1' => '<span class="badge bg-yellow">按天数算</span>',
        ]);
        $grid->column('活动地址')->display(function () {
            return route('infowall.index', ['token' => $this->token,'a_id' => $this->id]);
        })->urlWrapper();
        $grid->status('活动状态')->switch();
        $grid->start_at('活动时间')->display(function () {
            return $this->start_at . ' ~ ' . $this->end_at;
        })->label();

        $grid->actions(function ($actions) {
            $url = route('infowall.newsList', ['l_id' => $actions->row->id]);
            $actions->append(new IconButton($url, '当前活动消息列表', 'fa-list'));

            $url = route('danmuTpl.index', ['l_id' => $actions->row->id, 'is_share' => $actions->row->is_share]);
            $actions->append(new IconButton($url, '弹幕模板管理', 'fa-newspaper-o'));

            $url = route('tplShare.index', ['l_id' => $actions->row->id, 'is_share' => $actions->row->is_share]);
            $actions->append(new IconButton($url, '共享模板', 'fa-share-alt-square'));
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
        $show = new Show(InfoWallConfig::findOrFail($id));
        $show->id('大荧幕显示地址')->as(function ($id) {
            return route('infowall.largeScreen', ['token' => session('wxtoken'), 'a_id' => $id]);
        });
        $show->updated_at('最后编辑时间');
        $show->divider();
        // 发消息总人数(openid分组)
        $totalNumber = DB::table('w_infowall_userinfo')
            ->where(['l_id'=>$id, 'token'=>session('wxtoken')])
            ->select(DB::raw("count(*) as count"), 'openid')
            ->groupBy('openid')
            ->pluck('count', 'openid')
            ->count();
        $show->diy1('参与总人数:')->as(function () use ($totalNumber) {
            return $totalNumber;
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
        $form = new Form(new InfoWallConfig);
        $form->text('title', '活动名称')->required();
        $form->text('describe', '引导文案文本')->required();
        $form->editor('rule', '活动规则')->required();
        $form->switch('status', '活动状态');
        $form->datetimeRange('start_at', 'end_at', '活动时间')->required();
        $form->image('image', '活动封面')->move(materialUrl())->uniqueName()->removable();
        $form->radio('type', '可发弹幕类型')->options($this->typeArr);
        $form->radio('show_way', '弹幕展示方式')->options($this->showArr);
        $form->number('number', '可发次数')->help('如按天算,这里参数就是用户每天可发次数,如按期数算，则是当前活动用户可发次数')->default(10)->required();
        $form->switch('is_bind', '绑定读者?');
        $form->switch('is_subscribe', '需要关注?')->help('开启则会强制用户关注公众号才可进行正常发信息!');
        $form->switch('is_check', '需要审核?')->help('开启则后台人员会进行对弹幕的审核!');
        $form->switch('is_share', '弹幕是否共享?')->help('开启则当前馆设置的弹幕模板会成为共享资源!');
        $form->switch('is_custom', '是否可以自定义文本?')->help('开启则用户可以自定义弹幕文本!');
        $form->checkbox('gather', '需要收集信息?')->options($this->gatherArr);
        $form->table('addgather', '自定文本字段', function ($table) {
            $table->text('value', '字段名称(中文)')->help('必填');
            $table->text('key', '键值')->help('必填,键值为 字段名称 的拼音形式,如 性别 则键值填 xingbie');
        });
        $form->hidden('token')->default(session('wxtoken'));

        /* 素材库 上传图片 例子 start */
        /* 素材库 上传图片 例子 start */
        $form->hidden(config('materialPR') . 'image');
        $imgArray = [config('materialPR') . 'image'];
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
