<?php

namespace App\Admin\Controllers\Wechat\CollectCard;

use App\Admin\Extensions\Tools\IconButton;
use App\Jobs\CollectCardJob;
use App\Models\CollectCard\CollectCard;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    use HasResourceActions;

    protected $theme = [0 => '默认主题', 1 => '其它主题'];
    protected $type = [0 => '定时发奖(默认)', 1 => '即时发奖'];

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
            ->header('集卡活动')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     *
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('集卡活动')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
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
        $grid = new Grid(new CollectCard);
        $grid->model()->where('token', session('wxtoken'));
        $grid->disableExport();
        $grid->filter(function (Grid\Filter $filter) {
//            $filter->disableIdFilter();
            $filter->like('title', '活动标题');
//            $filter->equal('created_at')->datetime();
        });

        $grid->id('ID/活动编号')->label('default');
        $grid->type('次数类型')->using([
            '0' => '<span class="badge bg-green">统一发奖</span>',
            '1' => '<span class="badge bg-yellow">即时发奖</span>',
        ]);
        $grid->title('活动标题');
        $grid->column('活动链接')->display(function () {
            return route('CollectCard::index', ['token' => session('wxtoken'), 'a_id' => $this->id]);
        })->urlWrapper();
        $grid->start_at('开始时间');
        $grid->end_at('结束时间');
        $grid->status('状态')->switch();

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $url = route('collectCard.html', ['a_id' => $actions->row->id]);
            $actions->append(new IconButton($url,'页面文案配置','fa-file-text-o'));
            $url = url('admin/wechat/collectCard/cardConfig') . '?a_id=' . $actions->row->id;
            $actions->append(new IconButton($url,'卡片配置','fa-credit-card'));
            $url = url('admin/wechat/collectCard/cardTask') . '?a_id=' . $actions->row->id;
            $actions->append(new IconButton($url,'任务管理','fa-tasks'));
            $url = url('admin/wechat/collectCard/prize') . '?a_id=' . $actions->row->id;
            $actions->append(new IconButton($url,'奖品管理','fa-gift'));
            $url = route('collectCard.userList', ['a_id' => $actions->row->id]);
            $actions->append(new IconButton($url,'用户列表','fa-user'));
            $url = route('collectCard.dataShow', ['a_id' => $actions->row->id]);
            $actions->append(new IconButton($url,'数据中心','fa-database'));
            $url = route('collectCard.rewardData', ['a_id' => $actions->row->id]);
            $actions->append(new IconButton($url,'中奖数据','fa-cubes'));
        });
        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(CollectCard::findOrFail($id));

        $show->id('Id');
        $show->token('Token');

        $show->custom1('首页链接')->as(function () {
            return route('CollectCard::index', ['token' => session('wxtoken'), 'a_id' => $this->id]);
        });

        $show->custom2('我的卡片')->as(function () {
            return route('CollectCard::myCard', ['token' => session('wxtoken'), 'a_id' => $this->id]);
        });

        $show->divider();
        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new CollectCard);
        $form->hidden('token')->default(session('wxtoken'));
        $form->switch('status', '状态');
        $form->text('title', '活动标题');
        $form->select('theme', '活动主题')->options($this->theme);
        $form->radio('type', '发奖方式')->options($this->type)->default(1)->stacked();

        $form->switch('subscribe_sw', '强制关注')->help('需要关注公众号才能进入活动(抽卡)');
        $form->text('sub_text', '引导关注语')->placeholder('例:请先关注公众号才能参与活动');
//        $form->switch('reader_sw', '读者开关')->help('需要绑定读者证才能进入活动!');
        $form->datetime('start_at', '开始时间')->format('YYYY-MM-DD HH:mm')->default(date('Y-m-d H:i'));
        $form->datetime('end_at', '结束时间')->format('YYYY-MM-DD HH:mm')->default(date('Y-m-d H:i'))
            ->help('注:如发奖方式为定时发奖，活动结束后会进行20分钟的开奖倒计时，倒计时完成之后方可领奖');
        $form->switch('giving_sw', '赠送开关');
        $form->number('privilege_n', '先参与特权人数')->help('先参与的人可优先获得特殊任务的机会!');
        $form->editor('description', '活动规则');
        $form->text('share_title', '分享标题');
        $form->text('share_desc', '分享描述');
        $form->image('share_img', '分享封面')->move(materialUrl())->uniqueName();

        /* 素材库 上传图片 例子 start */
        $form->hidden(config('materialPR') . 'share_img');
        $imgArray = [config('materialPR') . 'share_img'];
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

        $form->saved(function (Form $form) {
            if (request()->isMethod('post')) {
                $creates = $this->initTask($form->model()->token, $form->model()->id);
                DB::table('w_collect_card_t')->insert($creates);
            }
            //更新缓存
            Cache::forget('collectCard:conf:' . $form->model()->token . ':' . $form->model()->id);
            //定时类型任务才加入队列
            if ($form->model()->type != 1 && $form->model()->is_prize != 1) {
                CollectCardJob::dispatch($form->model()->id, $form->model()->updated_at)
                    ->delay(now()->parse($form->model()->end_at))->onQueue('collect');
            }
        });

        return $form;
    }

    public function initTask($token, $aid)
    {
        $newTask = [
            [
                'status' => 1, 'title' => '绑定读者证', 'info' => '微门户绑定读者证', 'type' => 0, 'a_id' => $aid, 'token' => $token,
                'origin_type' => 1, 'origin_id' => 1, 'is_show' => 1, 'max_n' => 1, 'day_n' => 1, 'get_tip' => '绑定成功赠予你一张', 'weight' => '99.99'
            ],
            [
                'status' => 1, 'title' => '每日登录', 'info' => '每日登录(集卡活动)', 'type' => 0, 'a_id' => $aid, 'token' => $token,
                'origin_type' => 1, 'origin_id' => 2, 'is_show' => 0, 'max_n' => 0, 'day_n' => 1, 'get_tip' => '每日登录活动奖励', 'weight' => '99.99'
            ],
            [
                'status' => 1, 'title' => '首次参与', 'info' => '首次参与活动', 'type' => 0, 'a_id' => $aid, 'token' => $token,
                'origin_type' => 1, 'origin_id' => 4, 'is_show' => 0, 'max_n' => 1, 'day_n' => 1, 'get_tip' => '首次参与活动奖励', 'weight' => '99.99'
            ],
            [
                'status' => 1, 'title' => '办证', 'info' => '微门户办证', 'type' => 0, 'a_id' => $aid, 'token' => $token,
                'origin_type' => 1, 'origin_id' => 5, 'is_show' => 1, 'max_n' => 1, 'day_n' => 1, 'get_tip' => '办证成功奖励', 'weight' => '99.99'
            ],
            [
                'status' => 1, 'title' => '续借图书', 'info' => '微门户续借图书', 'type' => 0, 'a_id' => $aid, 'token' => $token,
                'origin_type' => 1, 'origin_id' => 8, 'is_show' => 1, 'max_n' => 0, 'day_n' => 1, 'get_tip' => '续借成功奖励', 'weight' => '99.99'
            ],
            [
                'status' => 1, 'title' => '交逾期费', 'info' => '微门户续借图书', 'type' => 0, 'a_id' => $aid, 'token' => $token,
                'origin_type' => 1, 'origin_id' => 9, 'is_show' => 1, 'max_n' => 0, 'day_n' => 1, 'get_tip' => '缴费成功奖励', 'weight' => '99.99'
            ],
            [
                'status' => 1, 'title' => '活动分享拉新', 'info' => '活动分享拉新', 'type' => 0, 'a_id' => $aid, 'token' => $token,
                'origin_type' => 1, 'origin_id' => 10, 'is_show' => 0, 'max_n' => 0, 'day_n' => 1, 'get_tip' => '分享拉新奖励', 'weight' => '99.99'
            ],
            [
                'status' => 1, 'title' => '活动报名', 'info' => '参与活动', 'type' => 0, 'a_id' => $aid, 'token' => $token,
                'origin_type' => 1, 'origin_id' => 11, 'is_show' => 0, 'max_n' => 0, 'day_n' => 1, 'get_tip' => '参与成功奖励', 'weight' => '99.99'
            ],

        ];
        return $newTask;
    }
}
