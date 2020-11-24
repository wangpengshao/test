<?php

namespace App\Admin\Controllers\Wechat\CollectCard;

use App\Admin\Extensions\Tools\BackButton;
use App\Models\CollectCard\CardConfig;
use App\Http\Controllers\Controller;
use App\Models\CollectCard\CollectCard;
use App\Models\CollectCard\CollectLog;
use App\Models\CollectCard\CollectTask;
use App\Models\CollectCard\CollectUsers;
use App\Models\Wechat\IndexMenu;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CardTaskController extends Controller
{
    use HasResourceActions;

    protected $origin_type = [
        '1' => '微门户内部',
        '2' => '设备端/PC端/移动端',
//        '3' => '移动端',
    ];
    protected $type = [
        '0' => '按活动期数限制 ',
        '1' => '按每天限制'
    ];
    protected $origin_id_arr = [
        '1' => '绑定读者证',
        '2' => '每日登录(集卡活动)',
        '3' => '每日登录(服务大厅)',
        '4' => '首次参与(集卡活动)',
        '5' => '办证',
        '6' => '预约图书',
        '7' => '预借图书',
        '8' => '续借图书',
        '9' => '交逾期费',
        '10' => '活动分享拉新',
        '11' => '报名(活动系统)',
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
        if (CollectCard::where('token', session('wxtoken'))->doesntExist()) {
            return $content->withWarning('提示', '请先创建活动..');
        }

        return $content
            ->header('任务管理')
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
            ->body($this->form($id)->edit($id));
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
        $grid = new Grid(new CollectTask());
        $grid->model()->where('token', $token);
        $grid->expandFilter();
        $grid->disableExport();
        $grid->disableCreateButton();
        $grid->filter(function (Grid\Filter $filter) use ($list) {
            $filter->like('title', '任务标题');
            $filter->equal('a_id', '所属活动')->select($list);
        });
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/wechat/collectCard/index'), '返回活动'));
            if (\request()->input('a_id')) {
                $tools->append(new BackButton(route('cardTask.create', ['a_id' => \request()->input('a_id')]), '新建任务'));
            }
        });
        $grid->actions(function ($actions) {
            $actions->disableView(false);
        });

        $grid->id('任务ID')->display(function ($id) {
            if ($this->origin_type == 2) {
                return $id;
            }
        })->label('default');
        $grid->a_id('所属活动')->using($list)->label();
        $grid->title('任务标题');
        $grid->type('限制方式')->using([
            '0' => '<span class="badge bg-green">活动期</span>',
            '1' => '<span class="badge bg-blue">每天</span>',
        ]);
        $grid->weight('概率(%)');
        $grid->day_n('每天(次)');
        $grid->max_n('最多完成(次)');
        $grid->origin_type('所属类型')->using([
            '1' => '<span class="badge bg-green">微门户</span>',
            '2' => '<span class="badge bg-yellow">外部</span>',
        ]);
        $grid->status('状态')->switch();
        $grid->is_show('是否显示')->switch();
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
        $find = CollectTask::findOrFail($id);
        if ($find['token'] != \request()->session()->get('wxtoken')) {
            admin_error('提示', '非法访问');
            return redirect()->back();
        }
        $show = new Show($find);
        if ($find['origin_type'] != 1) {
            $show->diy1('当前任务邀请人数:')->as(function () use ($find) {
                $where = [
                    'token' => $find['token'],
                    'a_id' => $find['a_id'],
                    'origin_type' => $find['origin_type'],
                    'origin_id' => $find['origin_id'],
                ];
                if ($find['origin_type'] == 2) {
                    $where['origin_id'] = $find['id'];
                }
                return CollectUsers::where($where)->count('id');
            });
        }

        $show->diy2('当前任务发卡数量:')->as(function () use ($find) {
            $where = [
                'token' => $find['token'],
                'a_id' => $find['a_id'],
                'origin_type' => $find['origin_type'],
                'origin_id' => $find['origin_id'],
                'giver' => 0
            ];
            if ($find['origin_type'] == 2) {
                $where['origin_id'] = $find['id'];
            }
            return CollectLog::where($where)->count('id');
        });

        $show->diy3('当前任务完成人数:')->as(function () use ($find) {
            $where = [
                'token' => $find['token'],
                'a_id' => $find['a_id'],
                'origin_type' => $find['origin_type'],
                'origin_id' => $find['origin_id'],
                'giver' => 0
            ];
            if ($find['origin_type'] == 2) {
                $where['origin_id'] = $find['id'];
            }
            return CollectLog::where($where)->distinct()->count('user_id');
        });

        $show->created_at('创建时间');
        return $show;
    }


    protected function form($id = '')
    {
        if (\request()->isMethod('put')) {
            $id = \request()->route('cardTask');
        }
        $a_id = ($id) ? CollectTask::where('id', $id)->value('a_id') : \request()->input('a_id');
        if (empty($a_id)) {
            return admin_error('提示', '非法访问');
        }
        $token = \request()->session()->get('wxtoken');
        $list = CollectCard::where(['token' => $token, 'id' => $a_id])->pluck('title', 'id');
        $cardList = CardConfig::where(['token' => $token, 'a_id' => $a_id])->pluck('text', 'id');
        $menu = IndexMenu::where('token', $token)->pluck('caption', 'id');

        $origin_id_arr = $this->origin_id_arr;
        $form = new Form(new CollectTask());

        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
            $tools->disableList();
        });
        $form->disableViewCheck();
        $form->tab('基础设置', function ($form) use ($list, $cardList, $menu, $origin_id_arr, $id, $token) {
            if ($id) {
                $form->display('a_id', '所属活动')->with(function ($value) use ($list) {
                    return array_get($list, $value);
                });
            } else {
                $form->select('a_id', '所属活动')->options($list)->rules('required');
            }
            $form->radio('type', '限制方式')->options($this->type)->default(1)->stacked();
            $form->switch('status', '状态');
            $form->switch('is_show', '是否显示')->help('开启则会显示在攻略页面！');
            $form->text('title', '文字')->rules('required')->help('攻略页面按钮显示的文字');
            $form->text('info', '任务描述')->rules('required')->help('攻略页面任务列表文字说明');
            $form->select('origin_type', '任务类型')->options($this->origin_type)->rules('required')
                ->load('origin_id', '/admin/wechat/collectCard/api/getUweiOriginType');
            $form->select('origin_id', '类型名称')->options(function ($id) use ($origin_id_arr) {
                if (array_get($origin_id_arr, $id)) {
                    return $origin_id_arr;
                }
                return [];
            })->help('非微门户内部可以忽略此项!')->default(0);
            $form->rate('weight', '可抽卡概率')->help('这个概率指的是完成任务可抽卡的概率!');
            $form->number('day_n', '一天最多完成(次)')->help('个人一天最多完成任务的次数,超过的将无法获得卡片,0为不限制');
            $form->number('max_n', '个人最多完成(次)')->help('个人最多完成当前任务的次数,超过的将无法获得卡片,0为不限制');
            $form->select('first_cid', '首次必得卡')->options($cardList)->help('设置此项，用户首次必得卡!');
            $form->select('menu_id', '关联菜单')->options($menu)->help('页面跳转菜单!');
            $form->text('get_tip', '抽中卡文字提示')->help('例:恭喜您办证成......');
            $form->hidden('token')->default($token);

        })->tab('邀请类型任务必填数据项', function ($form) {
            $form->embeds('sub_data', '级别列表', function ($form) {
                $form->number('lv1', '级别1');
                $form->divider();
                $form->number('lv2', '级别2');
                $form->divider();
                $form->number('lv3', '级别3');
                $form->divider();
                $form->number('lv4', '级别4');
                $form->divider();
                $form->number('lv5', '级别5');
                $form->divider();
                $form->number('lv6', '级别6');
                $form->divider();
                $form->number('lv7', '级别7');
                $form->divider();
                $form->number('lv8', '级别8');
                $form->divider();
                $form->number('lv9', '级别9');
                $form->divider();
                $form->number('lv10', '级别10');
                $form->divider();
                $form->number('lv11', '级别11');
                $form->divider();
                $form->number('lv12', '级别12');
                $form->divider();
                $form->number('lv13', '级别13');
                $form->divider();
                $form->number('lv14', '级别14');
                $form->divider();
                $form->number('lv15', '级别15');
            });
        });

        $form->footer(function ($footer) {
            $footer->disableEditingCheck();
        });
        $form->saved(function (Form $form) {
            Cache::forget('collectCard:task:' . $form->model()->token . ':' . $form->model()->a_id);
            admin_toastr('保存成功', 'success');
            return redirect(route('cardTask.index', ['a_id' => $form->model()->a_id]));
//            return back()->with(['a_id' => $form->model()->a_id]);
        });
        return $form;
    }


    public function getUweiOriginType(Request $request)
    {
        $q = $request->get('q');
        $uweiArray = [];
        if ($q == 1) {
            foreach ($this->origin_id_arr as $k => $v) {
                $uweiArray[] = ['id' => $k, 'text' => $v];
            }
        }
        return $uweiArray;
    }
}
