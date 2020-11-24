<?php

namespace App\Admin\Controllers\Wechat\CollectCard;

use App\Admin\Extensions\Tools\BackButton;
use App\Models\CollectCard\CardConfig;
use App\Http\Controllers\Controller;
use App\Models\CollectCard\CollectCard;
use App\Models\CollectCard\CollectTask;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class CardConfigController extends Controller
{
    use HasResourceActions;

    protected $cardType = [
        '0' => '普通卡',
        '1' => '万能卡',
        '2' => '沾沾卡',
    ];

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        if (CollectCard::where('token', \request()->session()->get('wxtoken'))->doesntExist()) {
            return $content->withWarning('提示', '请先创建活动..');
        }
        return $content
            ->header('卡片管理')
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
            ->header('Detail')
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
        $token = \request()->session()->get('wxtoken');
        $list = CollectCard::where('token', $token)->pluck('title', 'id')->toArray();
        $grid = new Grid(new CardConfig);
        $grid->disableExport();
        $grid->expandFilter();
        $grid->model()->where('token', $token);

        $grid->filter(function (Grid\Filter $filter) use ($list) {
            $filter->disableIdFilter();
            $filter->like('text', '卡名');
            $filter->equal('a_id', '所属活动')->select($list);
        });
        $grid->actions(function ($actions) {
            $actions->disableView(false);
        });
        $grid->a_id('所属活动')->using($list)->label();
        $grid->order('排序')->sortable();
        $grid->text('卡名')->badge('danger');
        $grid->image('封面')->image('', 80, 80);
        $grid->type('类型')->using([
            '0' => '<span class="badge bg-green">普通卡</span>',
            '1' => '<span class="badge bg-red">万能卡</span>',
            '2' => '<span class="badge bg-yellow">沾沾卡</span>',
        ]);
        $grid->prob('概率(%)');
        $grid->number('卡片数量(库存)');
        $grid->get_number('被收集数量');

        $grid->status('状态')->switch();

        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/wechat/collectCard/index'), '返回活动'));
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
        $find = CardConfig::findOrFail($id);
        if ($find['token'] != request()->session()->get('wxtoken')) {
            return admin_error('提示', '非法访问');
        }
        $taskList = CollectTask::where('a_id', $find['a_id'])->get(['title', 'id', 'origin_type', 'origin_id']);
        $show = new Show($find);
        $show->showTip('注意:')->as(function () {
            return '当前的数据存在30分钟的缓存';
        })->label();
        $cacheKey = 'cCardData:c_id:' . $id;
        $cache = Cache::get($cacheKey);
        if (empty($cache)) {
            $cache = $find->hasManyLog()->get(['c_id', 'id', 'origin_type', 'origin_id', 'isValid', 'giver']);
            Cache::put($cacheKey, $cache, 30);
        }
        $num = $cache->count();
        $show->diy1('被收集总次:')->as(function () use ($num) {
            return $num;
        })->badge();
        $num = $cache->where('isValid', 1)->count();
        $show->diy2('实际拥有数:')->as(function () use ($num) {
            return $num;
        })->badge();
        $num = $cache->where('isValid', 1)->where('giver', null)->count();
        $show->diy3('通过赠送获得:')->as(function () use ($num) {
            return $num;
        })->badge();
        $show->divider();
        foreach ($taskList as $k => $v) {
            $text = 'task' . $k;
            if ($v['origin_type'] == 1) {
                $num = $cache->where('origin_type', 1)->where('origin_id', $v['origin_id'])->count();
                $show->$text('内部:' . $v['title'])->as(function () use ($num) {
                    return $num;
                });
            } else {
                $num = $cache->where('origin_type', 2)->where('origin_id', $v['id'])->count();
                $show->$text('外部:' . $v['title'])->as(function () use ($num) {
                    return $num;
                });
            }
        }

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $token = \request()->session()->get('wxtoken');
        $list = CollectCard::where('token', $token)->pluck('title', 'id');
        $form = new Form(new CardConfig);
        $form->tools(function (Form\Tools $tools) {
            $tools->disableList();
            $tools->disableView();
        });
        $form->disableViewCheck();
        $form->disableEditingCheck();

        $form->select('a_id', '所属活动')->options($list)->rules('required');
        $form->switch('status', '状态');
        $form->text('text', '卡名');
        $form->number('order', '排序');
        $form->image('image', '封面')->move(materialUrl())->uniqueName();
        $form->radio('type', '类型')->options($this->cardType);
        $form->rate('prob', '概率')->rules('required');

        $form->switch('first_sw', '首次参与')->help('是否属于首次参与活动可抽卡片!');
//        $form->switch('subscribe_sw', '关注参与')->help('关注参与活动可抽类型！');
        $form->switch('subscribe_sw', '关注参与')->help('关注参与活动可抽类型！');
        $form->number('number', '卡片数量')->help('为当前卡片多剩余数量！');
        $form->number('p_number', '个人最多')->help('个人最多获取当前同一张卡片的数量');
//        $form->number('get_number', '已领取数量');
        $form->hidden('token')->default($token);
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

        $form->saved(function (Form $form) {
            admin_toastr('保存成功', 'success');
            return redirect(route('cardConfig.index', ['a_id' => $form->model()->a_id]));
        });
        return $form;
    }
}
