<?php

namespace App\Admin\Controllers\Wechat\Vote;

use App\Admin\Extensions\Tools\BackButton;
use App\Models\Vote\VoteBlacklist;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class VoteBlacklistController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('黑名单管理')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
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
        $grid = new Grid(new VoteBlacklist);
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/wechat/vote/config'), '返回活动'));
        });
        $grid->model()->where('token', \request()->session()->get('wxtoken'));
        $grid->id('编号');
        $grid->ip('Ip');
        $grid->column('openid', 'openid');
        $grid->column('fansInfo.nickname', '微信昵称');
        $grid->column('fansInfo.headimgurl', '微信头像')->image('', 50, 50);
        $grid->created_at('拉黑时间');
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
        $show = new Show(VoteBlacklist::findOrFail($id));

        $show->id('Id');
//        $show->token('Token');
//        $show->ip('Ip');
        $show->created_at('拉黑时间');
//        $show->openid('Openid');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new VoteBlacklist);
        $form->hidden('token')->default(\request()->session()->get('wxtoken'));
//        $form->text('token', 'Token');
        $form->ip('ip', 'Ip');
        $form->text('openid', '用户openid');
        return $form;
    }
}
