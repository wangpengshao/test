<?php

namespace App\Admin\Controllers\Wechat\Vote;

use App\Admin\Extensions\Tools\BackButton;
use App\Admin\Extensions\Tools\HeadTitle;
use App\Admin\Extensions\Tools\IconButton;
use App\Models\Vote\VoteConfig;
use App\Models\Vote\VoteGroup;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class VoteGroupController extends Controller
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
            ->header('分组管理')
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
        $request = \request();
        if ($request->filled('a_id')) {
            $request->session()->put('gridWhere.a_id', $request->input('a_id'));
        }
        $gridWhere = $request->session()->get('gridWhere');
        $a_id = Arr::get($gridWhere, 'a_id');
        $token = $request->session()->get('wxtoken');
        $title = VoteConfig::where('id', $a_id)->value('title');

        $grid = new Grid(new VoteGroup);
        $grid->model()->where('token', $token);
        $grid->model()->where('a_id', $a_id);

        $grid->header(function () use ($title) {
            return new HeadTitle($title);
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            $url = route('vote.top', ['g_id' => $actions->row->id]);
            $actions->prepend(new IconButton($url, '排行榜', 'fa-sort-numeric-asc'));

            $url = url('admin/wechat/vote/message') . '?g_id=' . $actions->row->id;
            $actions->prepend(new IconButton($url, '留言管理', 'fa-comments-o'));

            $url = url('admin/wechat/vote/items') . '?g_id=' . $actions->row->id;
            $actions->prepend(new IconButton($url, '作品管理', 'fa-codiepie'));
        });
        $grid->id('编号');
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/wechat/vote/config'), '返回活动'));
        });


        $grid->title('分组名称')->badge('info');
        $grid->sort('排序')->sortable();
        $grid->column('分组地址')->display(function () {
            return route('Vote::index', ['a_id' => $this->a_id, 'token' => $this->token, 'g_id' => $this->id]);
        })->urlWrapper();
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
        $show = new Show(VoteGroup::findOrFail($id));

        $show->id('Id');
//        $show->a_id('A id');
//        $show->token('Token');
//        $show->title('Title');
//        $show->view_n('View n');
//        $show->item_n('Item n');
//        $show->voting_n('Voting n');
//        $show->sort('Sort');
//        $show->created_at('Created at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $request = \request();
        $gridWhere = $request->session()->get('gridWhere');
        $token = $request->session()->get('wxtoken');
        $a_id = Arr::get($gridWhere, 'a_id');
        $doesntExist = VoteConfig::where(['token' => $token, 'id' => $a_id])->doesntExist();
        if ($doesntExist) {
            return admin_error('警告', '非法访问');
        }
        $form = new Form(new VoteGroup);
        $form->text('title', '分组名称')->rules('required');
        $form->number('sort', '排序')->default(0);
        $form->hidden('a_id')->default($a_id);
        $form->hidden('token')->default($token);

        $form->hasMany('fields', '分组字段', function (Form\NestedForm $form) {
            $options = [
                0 => '文本',
                1 => '单选',
                2 => '多选',
                4 => '图片',
            ];
            $form->text('name', '字段名称')->rules('required');
            $form->select('type', '字段类型')->options($options)->rules('required');
            $form->switch('required_sw', '是否必填');
            $form->switch('show_sw', '是否显示');
            $form->text('data', '数据')->help('单选或多选类型需在此输入选项并用"|"号区分,如图片则输入图片需收集的张数!');
        });

        $form->saved(function (Form $form) {
            $cacheKey = 'vote:group:' . $form->model()->token . ':' . $form->model()->a_id;
            Cache::forget($cacheKey);
        });

        return $form;
    }

}
