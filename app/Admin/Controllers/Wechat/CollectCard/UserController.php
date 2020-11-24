<?php

namespace App\Admin\Controllers\Wechat\CollectCard;

use App\Admin\Controllers\CustomView\GridHeadA;
use App\Admin\Controllers\CustomView\OnlyInfo;
use App\Admin\Extensions\Tools\BackButton;
use App\Models\CollectCard\CollectCard;
use App\Models\CollectCard\CollectLog;
use App\Models\CollectCard\CollectRedPack;
use App\Models\CollectCard\CollectReward;
use App\Models\CollectCard\CollectTask;
use App\Models\CollectCard\CollectUsers;
use Carbon\Carbon;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Widgets\Table;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('集卡用户列表');
            $content->description('description');
            $doesntExist = CollectCard::whereToken(request()->session()->get('wxtoken'))
                ->where('id', request()->input('a_id'))->doesntExist();
            if ($doesntExist) {
                return $content->withWarning('提示', '抱歉，非法访问');
            }
            $content->body($this->grid());

        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $token = request()->session()->get('wxtoken');
        $a_id = request()->input('a_id');

        $taskList = CollectTask::where('a_id', $a_id)->get(['title', 'id', 'origin_type', 'origin_id']);
        $grid = new Grid(new CollectUsers());
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->model()->where('token', $token);
        $grid->model()->where('a_id', $a_id);
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/wechat/collectCard/index'), '返回活动'));
        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->disableView();
            $url = route('collectCard.userView', ['id' => $actions->row->id, 'a_id' => request()->input('a_id')]);
            $actions->append("<a href='{$url}'><i class='fa fa-credit-card'></i></a>");
//                if ($actions->row->has_one_reward) {
//                    $actions->append("<a href='{$url}'>查看奖品<i class='fa fa-credit-card'></i></a>");
//                }
        });
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->where(function ($query) {
                $input = $this->input;
                $query->whereHas('user', function ($query) use ($input) {
                    $query->where('nickname', 'like', '%' . $input . '%');
                });
            }, '微信昵称', 'nickname')->inputmask([], $icon = 'wechat');

            $filter->where(function ($query) {
                $query->whereHas('hasOneReader', function ($query) {
                    $query->where('rdid', 'like', "%{$this->input}%");
                });
            }, '读者证号');

            $filter->where(function ($query) {
                $query->whereHas('hasOneReader', function ($query) {
                    $query->where('name', 'like', "%{$this->input}%");
                });
            }, '读者姓名');

            $filter->equal('collect_all', '是否集齐')->select([0 => '否', '1' => '是']);

        });
        $grid->column('hasOneFansInfo.nickname', '微信昵称');
        $grid->hasOneFansInfo()->headimgurl('头像')->image('', '50', '50');

        $grid->column('hasOneReader.rdid', '证号');
        $grid->column('hasOneReader.name', '姓名');

        $grid->collect_all('是否集齐')->using([
            '0' => '<span class="badge bg-green">否</span>',
            '1' => '<span class="badge bg-yellow">是</span>',
        ]);
        $grid->ok_at('集齐时间')->sortable();
        $grid->created_at('参与时间')->sortable();
        $grid->column('hasOneReward.id', '是否中奖')->display(function ($a) {
            return ($a) ? '<span class="badge bg-yellow">是</span>' : '<span class="badge bg-green">否</span>';
        });

        $grid->column('origin_type', '来源')->display(function ($origin_type) use ($taskList) {
            if ($origin_type == 1) {
                return '微门户';
            } else {
                $find = $taskList->where('id', $this->origin_id)->first();
                if ($find) {
                    return $find['title'];
                }
                return '';
            }
        });
        return $grid;
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
            ->header('用户卡片详情')
            ->description('description')
            ->body($this->detail($id));
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
        $token = request()->session()->get('wxtoken');
        $a_id = request()->input('a_id');

        $taskList = CollectTask::where('a_id', $a_id)->get(['title', 'id', 'origin_type', 'origin_id']);
        $log = CollectLog::with('hasOneCard')
            ->where([
                'token' => $token,
                'user_id' => $id,
                'isValid' => 1
            ])->get();
        $rows = [];
        foreach ($log as $k => $v) {
            $type = ($v->hasOneCard['type'] == 1) ? '万能卡' : '普通卡';
            $name = $v->hasOneCard['text'];
            $get_at = $v['created_at'];
            $origin = '';
            if ($v['giver']){
                $origin.='(朋友赠送) ';
            }
            if ($v['origin_type'] == 1) {
                $find = $taskList->where('origin_type', 1)->where('origin_id', $v['origin_id'])->first();
                if ($find) {
                    $origin.= $find['title'];
                }
            } else {
                $find = $taskList->where('origin_type', 2)->where('id', $v['origin_id'])->first();
                if ($find) {
                    $origin.= $find['title'];
                }
            }
            $rows[] = [$k + 1, $type, $name, $origin, $get_at];
        }
        $headers = ['编号', '类型', '卡名', '获取途径', '获取时间'];
        $table = new Table($headers, $rows, ['success']);
        $backUrl = route('collectCard.userList', ['a_id' => $a_id]);
        $box = new Box(' ', new BackButton($backUrl) . $table);
        $box->style('info');
        return $box;
    }

    public function dataShow()
    {
        return Admin::content(function (Content $content) {
            $doesntExist = CollectCard::whereToken(\request()->session()->get('wxtoken'))
                ->where('id', request()->input('a_id'))->doesntExist();
            if ($doesntExist) {
                return $content->withWarning('提示', '抱歉，非法访问');
            }
            $content->header('数据中心');
            $content->description('from wechat');
            $content->row(function ($row) {
                $where = [
                    'token' => \request()->session()->get('wxtoken'),
                    'a_id' => request()->input('a_id')
                ];
                $userCount = CollectUsers::where($where)->count('id');
                $row->column(4, new OnlyInfo('参与活动用户总数', 'user', 'green', '', $userCount));

                $puserCount = CollectUsers::where($where)->whereNotNull('parent_id')->count('id');
                $row->column(4, new OnlyInfo('受邀请用户', 'user', 'yellow', '', $puserCount));

                $cardCount = CollectLog::where($where)->where('isValid', 1)->count('id');
                $row->column(4, new OnlyInfo('领卡数量', 'credit-card', 'red', '', $cardCount));

                $okCount = CollectUsers::where($where)->where('collect_all', 1)->count('id');
                $row->column(4, new OnlyInfo('已合成人数', 'users', 'purple', '', $okCount));

                $okCount = CollectUsers::where($where)->whereNotNull('parent_id')
                    ->distinct()->count('parent_id');
                $row->column(4, new OnlyInfo('转发人数', 'user', 'blue', '', $okCount));

                $okCount = CollectLog::where($where)->where('giver', '<>', 0)->distinct()->count('giver');
                $row->column(4, new OnlyInfo('赠卡人数', 'user', 'gray', '', $okCount));

                $cacheKey = 'dataShow:cCard:u:' . session('wxtoken') . ':' . $where['a_id'];
                $userCache = Cache::get($cacheKey);
                if (!$userCache) {
                    $userCache = CollectUsers::where($where)->get(['collect_all', 'created_at', 'parent_id', 'origin_id']);
                    Cache::put($cacheKey, $userCache, Carbon::tomorrow());
                }
                $box1 = $userCache->groupBy(function ($date) {
                    return Carbon::parse($date->created_at)->format('m-d'); // grouping by months
                });
                $labels = [];
                $labelData = [];
                foreach ($box1 as $k => $v) {
                    $labels[] = $k;
                    $labelData[] = count($v);
                }
                $barData = ['caption' => '', 'labelData' => json_encode($labelData), 'labels' => json_encode($labels)];
                $box1 = new Box('新用户图表', view('admin.Chart.data')->with($barData));
                $box1->removable();
                $box1->collapsable();
                $box1->style('info');
                $row->column(4, $box1);

                $type1Count = $userCache->where('parent_id', '>', 0)->count();
                $type2Count = $userCache->count() - $type1Count;
                $data = [
                    'data' => json_encode([$type1Count, $type2Count]),
                    'labels' => json_encode(['受邀用户', '普通用户']),
                    'id' => 'box2'
                ];
                $box2 = new Box('用户类型', view('admin.Chart.pie')->with($data));
                $box2->removable();
                $box2->collapsable();
                $box2->style('info');
                $row->column(4, $box2);

                $task = CollectTask::where($where)->where('origin_type', 2)->pluck('title', 'id');
                $labels = [];
                $data = [];
                $taskCount = 0;
                foreach ($task as $k => $v) {
                    $labels[] = $v;
                    $count = $userCache->where('origin_id', $k)->count();
                    $data[] = $count;
                    $taskCount += $count;
                }
                $labels[] = '微门户';
                $data[] = $userCache->count() - $taskCount;
                $data = [
                    'data' => json_encode($data),
                    'labels' => json_encode($labels),
                    'id' => 'box3'
                ];
                $box3 = new Box('用户来源', view('admin.Chart.pie')->with($data));
                $box3->removable();
                $box3->collapsable();
                $box3->style('info');
                $row->column(4, $box3);
            });

        });
    }

    public function rewardData(Request $request, Content $content)
    {
        $token = $request->session()->get('wxtoken');
        $a_id = $request->input('a_id');
        $exists = CollectCard::where('id', $a_id)->where('token', $token)->exists();
        if (!$exists) {
            admin_error('提示', '非法访问');
            return redirect()->back();
        }
        $grid = new Grid(new CollectReward());
        $grid->disableCreateButton();
        $grid->disableExport();
        $grid->header(function ($query) use ($a_id) {
            $redpack_id = CollectReward::where('a_id', $a_id)->where('redpack_id', '>', '0')->pluck('redpack_id')->toArray();
            $info1 = CollectRedPack::whereIn('id', $redpack_id)->sum('money');
            $info2 = CollectRedPack::whereIn('id', $redpack_id)->where('status',1)->sum('money');
            $info3 = CollectRedPack::whereIn('id', $redpack_id)->where('status',1)->count();
            $list = [
                [
                    'title' => '已中奖金额',
                    'icon' => 'calendar-o',
                    'color' => 'red',
                    'info' => $info1,
                ],
                [
                    'title' => '已兑金额',
                    'icon' => 'check-circle',
                    'color' => 'blue',
                    'link' => 'blue',
                    'info' => $info2,
                ],
                [
                    'title' => '已兑人次',
                    'icon' => 'github-alt',
                    'color' => 'green',
                    'link' => 'blue',
                    'info' => $info3,
                ],
            ];
            return new Box(' ', new GridHeadA($list));
        });
        $grid->model()->where('token', $token);
        $grid->model()->where('a_id', $a_id);
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/wechat/collectCard/index'), '返回活动'));
        });
        $grid->column('user_id', '用户id');
        $grid->type('奖品类型')->using([
            '0' => '<span class="badge bg-green">实物</span>',
            '1' => '<span class="badge bg-yellow">普通红包</span>',
            '2' => '<span class="badge bg-red">拼手气红包</span>',
        ]);
        $grid->column('hasOnePrize.title', '奖品名称');
        $grid->column('created_at', '创建时间');

        $grid->is_get('是否抽取')->using([
            '0' => '<span class="badge bg-gray">未抽</span>',
            '1' => '<span class="badge bg-green">已抽</span>',
        ]);
        $grid->column('get_at', '抽取时间');

        $grid->column('hasOneRedPage.money', '红包金额');

        $grid->status('红包兑现')->using([
            '0' => '<span class="badge bg-gray">未兑</span>',
            '1' => '<span class="badge bg-green">已兑</span>',
        ]);

        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableDelete();
            $actions->disableView();
            $actions->append("<a ><i class='fa fa-hand-scissors-o'></i></a>");
        });
        return $content->header('中奖列表')->description('description')->body($grid);
    }


}
