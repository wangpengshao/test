<?php

namespace App\Admin\Controllers\Wechat\Merchants;

use App\Admin\Controllers\CustomView\GridHeadA;
use App\Admin\Extensions\Button\EditBalance;
use App\Admin\Extensions\Tools\BackButton;
use App\Admin\Extensions\Tools\IconButton;
use App\Api\Helpers\ApiResponse;
use App\Models\Merchants\SparkPayer;
use App\Models\Merchants\SparkPayerLog;
use App\Models\Merchants\SparkPayerUpLog;
use Carbon\Carbon;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * 星火商户号
 * Class SparkPayerController
 * @package App\Admin\Controllers\Wechat\Merchants
 */
class SparkPayerController extends AdminController
{
    use ApiResponse;
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '星火商户号-运营接口';

    /**
     * @var array
     */
    protected $description = [
        'index' => '授权列表',
        'show' => 'Show',
        'edit' => '编辑',
        'create' => '创建',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SparkPayer);
        $grid->actions(function ($actions) {
            $actions->append(new EditBalance($actions->row->id));
//            $actions->disableDelete();
            $actions->disableView();
            $url = route('spark-payers.up.log', ['c_id' => $actions->row->id]);
            $actions->append(new IconButton($url, '金额编辑记录', 'fa-wpforms'));
            $url = route('spark-payers.use.log', ['pay_token' => $actions->row->pay_token]);
            $actions->append(new IconButton($url, '接口使用记录', 'fa-reorder'));
        });
        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->expand();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('pay_token', __('Pay token'));
                $filter->like('name', __('Name'));
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('type', '授权类型')->radio([
                    1 => '零钱提现',
                    2 => '现金红包',
                ]);
                $filter->group('money', '余额', function ($group) {
                    $group->gt('大于');
                    $group->lt('小于');
                    $group->nlt('不小于');
                    $group->ngt('不大于');
                    $group->equal('等于');
                })->currency();
            });
        });
        $grid->model()->orderBy('id', 'desc');

        $grid->column('id', '#编号');
        $grid->column('name', __('Name'));
        $grid->column('status', '授权状态')->bool(['1' => true, '0' => false]);
        $grid->column('pay_token', __('Pay token'))->copyable();
//        $grid->column('secret', __('Secret'))->copyable();
        $grid->column('type', '授权类型')->using([
            1 => '<span class="label label-success">零钱提现</span>',
            2 => '<span class="label label-danger">现金红包</span>'
        ]);

        $grid->column('money', '余额')->display(function ($money) {
            return '¥ ' . $money;
        })->sortable();
        $grid->column('expiration_at', '授权时间')->sortable()->display(function ($text) {
            $dt = Carbon::parse($text);
            $now = Carbon::now();
            $days = $dt->diffInDays($now);
            if ($dt->lt($now)) {
                return "<span class='badge bg-red'>已过期 {$dt->diffForHumans($now,true)}</span>";
            }
            if ($days <= 10) {
                return "<span class='badge bg-yellow'>$text</span>";
            }
            return $text;
        });

        $grid->column('created_at', __('Created at'));
//        $grid->column('updated_at', __('Updated at'));
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
        $show = new Show(SparkPayer::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('name', __('Name'));
        $show->field('pay_token', __('Pay token'));
        $show->field('secret', __('Secret'));
        $show->field('status', __('Status'));
        $show->field('type', __('Type'));
        $show->field('money', __('Money'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));
        $show->field('ips', __('Ips'));
        $show->field('expiration_at', __('Expiration at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new SparkPayer);
        $isCreating = $form->isCreating();

        if (!$isCreating) {
            $form->display('pay_token', __('Pay token'));
            $form->display('secret', __('Secret'));
        }
        $form->text('name', __('Name'))->required();
        $form->switch('status', '授权状态');
        $form->radio('type', '授权类型')->options([
            1 => '零钱提现',
            2 => '现金红包'
        ])->stacked()->default(1);

        if ($isCreating) {
            $form->currency('money', '余额')->default(0.00)->symbol('￥');
        } else {
            $form->display('money', '余额');
        }

        $form->datetime('expiration_at', '授权时间')->default(date('Y-m-d H:i:s'));
        $form->textarea('ips', __('Ips'))->help('ip白名单,留空则不限制');

        $form->saving(function (Form $form) {
            $model = $form->model();
            if (empty($model->pay_token) && empty($model->secret)) {
                $model->pay_token = 'PAYER' . Str::uuid()->getNodeHex();
                $model->secret = Str::uuid()->getHex();
            }
        });
        return $form;
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return mixed
     */
    public function editBalance(Request $request)
    {
        if (!$request->filled('id', 'number', 'desc')) {
            return $this->failed('缺少必填参数');
        }
        $id = $request->input('id');
        $number = $request->input('number');
        $desc = $request->input('desc');
        $find = SparkPayer::find($id);
        if (empty($find)) {
            return $this->failed('非法访问');
        }
        $create = [
            'c_id' => $id,
            'current_money' => $find->money,
            'desc' => $desc,
            'number' => $number,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $status = SparkPayerUpLog::create($create);
        if ($status) {
            if ((int)$number > 0) {
                $find->increment('money', $number);
                return $this->message('提额成功', true);
            } else {
                $number = $number * -1;
                $find->decrement('money', $number);
                return $this->message('减额成功', true);
            }
        }
        return $this->failed('服务器异常请稍后再试');
    }

    /**
     * @param \Illuminate\Http\Request     $request
     * @param \Encore\Admin\Layout\Content $content
     * @return \Encore\Admin\Layout\Content
     */
    public function showUpLog(Request $request, Content $content)
    {
        $c_id = $request->input('c_id');
        $grid = new Grid(new SparkPayerUpLog);
        $grid->disableFilter();
        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/spark-payers'), '返回授权列表'));
        });

        $grid->model()->orderBy('id', 'desc');
        if ($c_id) {
            $grid->model()->where('c_id', $c_id);
        }
        $grid->column('id', '#编号');
        $grid->column('current_money', '#编辑前金额')->display(function ($money) {
            return '¥ ' . $money;
        });
        $grid->column('number', '#金额变化')->display(function ($number) {
            if ($number > 0) {
                return "<strong class='text-green'>+{$number}</strong>";
            }
            return "<strong class='text-danger'>{$number}</strong>";
        });
        $grid->column('#编辑后金额')->display(function () {
            $money = $this->current_money + $this->number;
            return '¥ ' . $money;
        });
        $grid->column('desc', '操作描述');
        $grid->column('created_at', '操作时间');
        return $content->title('金额编辑记录')->description('列表')->body($grid);
    }

    /**
     * @param \Illuminate\Http\Request     $request
     * @param \Encore\Admin\Layout\Content $content
     * @return \Encore\Admin\Layout\Content
     */
    public function showUseLog(Request $request, Content $content)
    {
        $pay_token = $request->input('pay_token');
        $grid = new Grid(new SparkPayerLog);
        $grid->paginate(10);
        $grid->filter(function ($filter) {
//            $filter->expand();
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('openid');
                $filter->equal('desc');
                $filter->equal('type', '授权类型')->radio([
                    1 => '零钱提现',
                    2 => '现金红包',
                ]);
            });
            $filter->column(1 / 2, function ($filter) {
                $filter->equal('payment_no', '微信付款单号');
                $filter->equal('partner_trade_no', '商户单号');
            });
        });

        $grid->disableActions();
        $grid->disableBatchActions();
        $grid->disableCreateButton();
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/spark-payers'), '返回授权列表'));
        });
        if ($pay_token) {
            $grid->header(function ($query) use ($pay_token) {
                $data = SparkPayerLog::where([
                    'pay_token' => $pay_token,
                    'return_code' => 'SUCCESS'
                ])->select(DB::raw("sum(`amount`) as all_amount"), 'result_code', DB::raw("count(1) as count"))
                    ->groupby('result_code')->get()->keyBy('result_code')->toArray();
                $list = [
                    [
                        'title' => '成功提现总额',
                        'icon' => 'check-circle',
                        'color' => 'green',
                        'info' => '0',
                    ],
                    [
                        'title' => '成功提现次数',
                        'icon' => 'pie-chart',
                        'color' => 'green',
                        'info' => '0',
                    ],
                    [
                        'title' => '失败提现总额',
                        'icon' => 'times-circle',
                        'color' => 'gray',
                        'info' => '0',
                    ],
                    [
                        'title' => '失败提现次数',
                        'icon' => 'pie-chart',
                        'color' => 'gray',
                        'info' => '0',
                    ]
                ];

                if ($data) {
                    $list[0]['info'] = $data['SUCCESS']['all_amount'] / 100;
                    $list[1]['info'] = $data['SUCCESS']['count'];
                    $list[2]['info'] = $data['FAIL']['all_amount'] / 100;
                    $list[3]['info'] = $data['FAIL']['count'];
                }
                return new Box("授权: " . $pay_token, new GridHeadA($list));
            });
        }


        $grid->model()->orderBy('id', 'desc');
        if ($pay_token) {
            $grid->model()->where('pay_token', $pay_token);
        }
        $grid->column('id', '#编号');

        $grid->column('type', '#类型')->using([
            1 => '<span class="label label-success">零钱提现</span>',
            2 => '<span class="label label-danger">现金红包</span>'
        ]);

        $grid->column('amount', '#金额')->display(function ($amount) {
            return '¥ ' . $amount / 100;
        })->sortable();

        $grid->column('openid');
        $grid->column('订单详情')->display(function () {
            $da = [];
            $da[] = "<strong>通信标识: </strong><i> {$this->return_code} </i>";
            $da[] = "<strong>业务标识: </strong><i> {$this->result_code} </i>";
            $da[] = "<strong>微信付款单号: </strong><i> {$this->payment_no} </i>";
            $da[] = "<strong>商户单号: </strong><i> {$this->partner_trade_no} </i>";
            $da[] = "<strong>desc: </strong><i> {$this->desc} </i>";
            return $da;
        })->implode('<br />');

        $grid->column('created_at', '操作时间')->sortable();

        return $content->title('接口使用记录')->description('列表')->body($grid);
    }
}
