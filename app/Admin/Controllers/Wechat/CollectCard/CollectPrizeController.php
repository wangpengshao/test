<?php

namespace App\Admin\Controllers\Wechat\CollectCard;

use App\Admin\Extensions\Tools\BackButton;
use App\Models\CollectCard\CollectCard;
use App\Models\CollectCard\CollectPrize;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class CollectPrizeController extends Controller
{
    use HasResourceActions;

    protected $typeArr = ['0' => '实物', '1' => '普通红包', '2' => '拼手气红包'];

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
            ->header('奖品管理')
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
        $grid = new Grid(new CollectPrize());
        $grid->disableExport();
        $grid->actions(function ($actions) {
            $actions->disableView();
        });
        $grid->filter(function ($filter) use ($list) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
//            $filter->expand();
            $filter->like('title', '名称');
            $filter->like('a_id', '所属活动')->select($list);
            $filter->equal('type', '类型')->select($this->typeArr);
        });
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/wechat/collectCard/index'), '返回活动'));
        });
        $grid->model()->where('token', $token);
        $grid->title('名称');

        $grid->type('类型')->using([
            '0' => '<span class="badge bg-green">实物</span>',
            '1' => '<span class="badge bg-yellow">普通红包</span>',
            '2' => '<span class="badge bg-red">拼手气红包</span>',
        ]);
        $grid->image('奖品图')->image();
        $grid->inventory('库存');
        $grid->weight('概率')->display(function ($weight) {
            if ($weight) {
                return $weight . '%';
            }
        })->label('primary');
        $grid->money('金额');
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
        $show = new Show(CollectPrize::findOrFail($id));

        $show->id('Id');
        $show->title('名称');
//        $show->type('Type');
//        $show->image('Image');
//        $show->token('Token');
//        $show->inventory('Inventory');
//        $show->weight('Weight');
//        $show->integral('Integral');
//        $show->money('Money');
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
        $token = \request()->session()->get('wxtoken');
        $list = CollectCard::where('token', $token)->pluck('title', 'id')->toArray();
        $form = new Form(new CollectPrize());
        $form->disableEditingCheck();
        $form->disableViewCheck();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
            $tools->disableList();
        });
        $form->select('a_id', '所属活动')->options($list)->rules('required');
        $form->text('title', '奖品名称')->rules('required');

        $form->radio('type', '奖品类型')->options($this->typeArr)
            ->help('说明:积分为微门户平台读者积分,微信红包需公众号开通商户号,并配置好相关信息!')->stacked();

        $form->number('inventory', '库存')->help('注:如拼手气红包的话,库存的数量尽量与红包的个数保持一致!');
        $form->rate('weight', '概率')->help('注:实时开奖方式,概率将会影响到中奖的几率,统一开奖方式,该概率无作用');
        $form->image('image', '奖品图')->move(materialUrl())->uniqueName()->removable()
            ->help('积分类型、红包类型如不上传图片将使用默认图片!');

        $form->divider();
        $form->currency('money', '红包金额')->symbol('￥')->default(0.00)
            ->help('注:目前红包金额最大为万元!');
        $form->number('pack_n', '拼手气红包个数');
        $form->number('min_n', '最小金额')->default(1)->min(1);
        $form->number('max_n', '最大金额');

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
            return redirect(route('prize.index', ['a_id' => $form->model()->a_id]));
        });

        return $form;
    }
}
