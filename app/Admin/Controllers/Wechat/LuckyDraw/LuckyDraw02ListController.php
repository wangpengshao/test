<?php

namespace App\Admin\Controllers\Wechat\LuckyDraw;

use App\Admin\Extensions\Tools\BackButton;
use App\Admin\Extensions\Tools\IsWinning;
use App\Models\LuckyDraw\LuckyDraw02List;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class LuckyDraw02ListController extends Controller
{
    use HasResourceActions;

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
            ->header('抽奖列表')
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
        $grid = new Grid(new LuckyDraw02List);
        $grid->disableExport();
        $grid->disableCreateButton();

        $grid->model()->isWinning(\request()->get('is_winning'));

        $grid->model()->where('token', session('wxtoken'));

        if (\request()->filled('l_id')) {
            $grid->model()->where('l_id', \request()->get('l_id'));
        }
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->between('created_at', '抽奖时间')->datetime();
            $filter->equal('rdid', '读者证');
            $filter->equal('code', '兑奖码');
            $filter->where(function ($query) {
                $input = $this->input;
                $query->whereHas('user', function ($query) use ($input) {
                    $query->where('nickname', 'like', '%' . $input . '%');
                });
            }, '微信昵称', 'nickname')->inputmask([], $icon = 'wechat');
        });

        $grid->actions(function ($actions) {
//            $actions->disableDelete();
            $actions->disableEdit();
            $actions->disableView();
        });

        $grid->tools(function ($tools) {
            $tools->append(new IsWinning());
            $tools->append(new BackButton(route('luckyDraw.type02'), '返回活动'));
        });

        $grid->column('luckyDraw.title', '活动名称');
        $grid->rdid('读者证');
        $grid->column('fansInfo.nickname', '微信昵称');
        $grid->column('fansInfo.headimgurl', '微信头像')->image('', 50, 50);
        $grid->column('prize.title', '奖品名称');
        $grid->column('prize.image', '奖品图片')->image();
        $grid->column('text', '中奖说明')->display(function ($text) {

            return $text;

        });

        $grid->code('兑奖码');
        $grid->status('状态')->using([
//            '0' => '<span class="badge bg-green">不中奖</span>',
            '1' => '<span class="badge bg-yellow">未发奖</span>',
            '2' => '<span class="badge bg-yellow">已发奖</span>',
        ]);;
        $grid->created_at('抽奖时间');

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
        $show = new Show(LuckyDraw02List::findOrFail($id));

        $show->id('Id');
        $show->rdid('Rdid');
        $show->openid('Openid');
        $show->is_winning('Is winning');
        $show->code('Code');
        $show->status('Status');
        $show->token('Token');
        $show->prize_id('Prize id');
        $show->created_at('Created at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new LuckyDraw02List);

        $form->text('rdid', 'Rdid');
        $form->text('openid', 'Openid');
        $form->switch('is_winning', 'Is winning');
        $form->text('code', 'Code');
        $form->switch('status', 'Status');
        $form->text('token', 'Token');
        $form->number('prize_id', 'Prize id');
//        $form->token

        return $form;
    }
}
