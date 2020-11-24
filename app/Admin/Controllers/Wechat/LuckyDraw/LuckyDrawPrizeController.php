<?php

namespace App\Admin\Controllers\Wechat\LuckyDraw;

use App\Admin\Extensions\Tools\BackButton;
use App\Admin\Extensions\Tools\HeadTitle;
use App\Models\LuckyDraw\LuckyDraw01;
use App\Models\LuckyDraw\LuckyDraw02;
use App\Models\LuckyDraw\LuckyDraw03;
use App\Models\LuckyDraw\LuckyPrize;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class LuckyDrawPrizeController extends Controller
{
    use HasResourceActions;

    protected $typeArr = ['0' => '实物', '1' => '积分', '2' => '微信红包'];
    protected $tagsArr = ['01' => '幸运大转盘', '02' => '拼人品老虎机', '03' => '砸金蛋'];

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
            ->header('奖品列表')
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
        $request = request();
        if ($request->filled('lucky_type')) {
            $request->session()->put('gridWhere.lucky_type', $request->input('lucky_type'));
        }
        if ($request->filled('l_id')) {
            $request->session()->put('gridWhere.l_id', $request->input('l_id'));
        }
        $gridWhere = $request->session()->get('gridWhere');
        $token = $request->session()->get('wxtoken');

        $lucky_type = Arr::get($gridWhere, 'lucky_type');
        $l_id = Arr::get($gridWhere, 'l_id');
        switch ($lucky_type) {
            case 1:
                $title = LuckyDraw01::where('id', $l_id)->where('token', $token)->value('title');
                $header = '幸运大转盘 -- ' . $title;
                $routeName = 'luckyDraw.type01';
                $dbTable = 'wechat_luckydraw_01_list';
                break;
            case 2:
                $title = LuckyDraw02::where('id', $l_id)->where('token', $token)->value('title');
                $header = '老虎机 -- ' . $title;
                $routeName = 'luckyDraw.type02';
                $dbTable = 'wechat_luckydraw_02_list';
                break;
            case 3:
                $title = LuckyDraw03::where('id', $l_id)->where('token', $token)->value('title');
                $header = '砸金蛋 -- ' . $title;
                $routeName = 'luckyDraw.type03';
                $dbTable = 'wechat_luckydraw_03_list';
                break;
            default:
                $routeName = '';
                $header = '';
                $dbTable = '';
        }
        //中奖次数统计
        $groupNumber = [];
        if ($dbTable) {
            $groupNumber = DB::table($dbTable)->select(DB::raw('count(1) as winNum'), 'prize_id')
                ->where(['l_id' => $l_id, 'is_winning' => 1, 'token' => $token])->groupBy('prize_id')
                ->pluck('winNum', 'prize_id')->toArray();
        }

        $grid = new Grid(new LuckyPrize);
        $grid->header(function () use ($header) {
            return new HeadTitle($header);
        });

        $grid->tools(function ($tools) use ($routeName) {
            $tools->append(new BackButton(route($routeName), '返回活动'));
        });
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('type', '类型')->select($this->typeArr);
        });
        $grid->model()->where('token', $token);
        $grid->model()->where($gridWhere);
        $grid->column('title', '名称');
        $grid->column('type', '奖品类型')->using([
            0 => '实物',
            1 => '积分',
            2 => '微信红包',
        ])->dot([
            0 => 'success',
            1 => 'info',
            2 => 'danger',
        ])->sortable();
        $grid->image('奖品图')->image('', 100, 100);

        $grid->column('已中奖数量')->display(function () use ($groupNumber) {
            return Arr::get($groupNumber, $this->id, 0);
        });
        $grid->column('inventory', '当前库存')->sortable();
        $grid->weight('概率')->display(function ($weight) {
            return $weight . '%';
        })->label('primary')->help('单个奖品的真实的概率为: 单个奖品概率 / (全部奖品概率 + 活动不中奖概率)');
        $grid->column('integral', '积分');
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
        $show = new Show(LuckyPrize::findOrFail($id));
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
        if ($request->filled('lucky_type')) {
            $request->session()->put('gridWhere.lucky_type', $request->input('lucky_type'));
        }
        if ($request->filled('l_id')) {
            $request->session()->put('gridWhere.l_id', $request->input('l_id'));
        }
        $gridWhere = $request->session()->get('gridWhere');
        $token = $request->session()->get('wxtoken');
        $lucky_type = Arr::get($gridWhere, 'lucky_type');
        $l_id = Arr::get($gridWhere, 'l_id');

        $form = new Form(new LuckyPrize);
        $form->text('title', '名称')->rules('required');
        $form->number('inventory', '库存')->default(0);

        $form->radio('type', '奖品类型')->options($this->typeArr)
            ->help('说明:积分为微门户平台读者积分,微信红包需公众号开通商户号,并已开通好现金红包接口权限!')->stacked();

        $form->rate('weight', '概率')->default(0);

        $form->image('image', '奖品图')->move(materialUrl())->uniqueName()->removable()
            ->help('积分类型、红包类型如不上传图片将使用默认图片!');

        $form->number('integral', '积分')->default(0);
        $form->currency('money', '红包金额')->symbol('￥')->default(0.00);

        $form->hidden('token')->default($token);
        $form->hidden('lucky_type')->default($lucky_type);
        $form->hidden('l_id')->default($l_id);

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
