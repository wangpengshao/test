<?php

namespace App\Admin\Controllers\Wechat\IntegralExchange;

use App\Models\IntegralExchange\IntegralExchangePrize;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class IntegralExchangeController extends Controller
{
    use HasResourceActions;

    protected $typeArr = ['0' => '不限制', '1' => '按月数算', '2' => '按周算', '3' => '按天算'];

    protected $prizeArr = ['0' => '实物', '1' => '现金红包'];

    protected $rewardArr = ['0' => '线下', '1' => '线上快递'];

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
            ->header('积分奖品列表')
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
        $grid = new Grid(new IntegralExchangePrize);

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('prize_type', '奖品类型')->select($this->prizeArr);
        });
        $grid->column('title', '名称');
        $grid->column('prize_type', '奖品类型')->using([
            0 => '实物',
            1 => '现金红包',
        ])->dot([
            0 => 'success',
            1 => 'info',
        ])->sortable();
        $grid->image('奖品图')->image('', 100, 100);
        $grid->column('inventory', '库存')->sortable();
        $grid->column('integral', '消耗积分');
        $grid->column('money', '金额');
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
        $show = new Show(IntegralExchangePrize::findOrFail($id));
        $show->id('Id');
        $show->title('名称');
        $show->updated_at('最后编辑时间');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $request = request();
        $form = new Form(new IntegralExchangePrize);
        $form->text('title', '名称')->rules('required');
        $form->switch('status', '兑奖状态');
        $form->radio('prize_type', '奖品类型')->options($this->prizeArr)
            ->help('说明:现金红包需公众号开通商户号,并已开通好现金红包接口权限!')->stacked();
        $form->text('display', '奖品说明');
        $form->datetimeRange('start_at', 'end_at', '可兑奖时间');
        $form->image('image', '兑奖图片')->move(materialUrl())->uniqueName()->removable()
            ->help('兑奖图片如不上传将使用默认图片!');
        $form->number('inventory', '库存')->default(0);

        $form->radio('type', '次数类型')->options($this->typeArr)
            ->help('说明:可以根据"月","周","天"为单位，也可以不限制，默认为不限制')->stacked();

        $form->number('number', '可兑换次数')->help('如按天算,这里参数就是用户每天可兑换次数,如按月数算，则是当前用户当月可兑换次数')->default(0);

        $form->number('all_number', '可兑换总次数')->help('奖品可兑换的总次数')->default(0);

        $form->number('integral', '消耗积分')->default(0)
            ->help('说明:需要消耗多少积分才能兑换!');

        $form->radio('reward_way', '兑奖方式')->options($this->rewardArr)
            ->help('说明:兑奖方式线下及线上快递')->stacked();

        $form->currency('money', '红包金额')->symbol('￥')->default(0.00);

        $form->text('qq', '联系QQ(兑奖)');
        $form->text('phone', '联系电话(兑奖)');

        $token = $request->session()->get('wxtoken');

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

        return $form;
    }
}
