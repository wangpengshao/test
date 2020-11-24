<?php

namespace App\Admin\Controllers\Wechat\InfoWall;

use App\Admin\Extensions\Tools\BackButton;
use App\Models\InfoWall\InfoWallDanMuTpl;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Arr;

class DanMuTplControlller extends Controller
{
    use HasResourceActions;

    protected $topicType = [
        '0' => '阅读',
        '1' => '未来',
        '2' => '温暖',
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
        return $content
            ->header('弹幕模板')
            ->description('管理')
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
        $request = request();
        if ($request->filled('l_id')) {
            $request->session()->put('gridWhere.l_id', $request->input('l_id'));
        }
        if ($request->filled('is_share')) {
            $request->session()->put('gridWhere.is_share', $request->input('is_share'));
        }
        $gridWhere = $request->session()->get('gridWhere');
        $l_id = Arr::get($gridWhere, 'l_id');

        $grid = new Grid(new InfoWallDanMuTpl);
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->like('title', '模板类名');
        });
        $grid->tools(function ($tools) {
            $tools->append(new BackButton(route('infowall.config'), '返回活动'));
        });
        $grid->model()->where(['token' => session('wxtoken'), 'l_id' => $l_id]);
        $grid->p_name('模板类名');

        return $grid;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $request = request();
        if ($request->filled('l_id')) {
            $request->session()->put('gridWhere.l_id', $request->input('l_id'));
        }
        $gridWhere = $request->session()->get('gridWhere');
        $l_id = Arr::get($gridWhere, 'l_id');
        $is_share = Arr::get($gridWhere, 'is_share');
        $form = new Form(new InfoWallDanMuTpl);
        $form->select('type', '模板话题类型')->options($this->topicType)->rules('required');
        $form->text('p_name', '#话题#')->required();
        $form->hidden('l_id')->default($l_id);
        $form->hidden('is_share')->default($is_share);
        $form->hidden('token')->default(session('wxtoken'));
        $form->textarea('s_name', '固定文本')->rows(10)->help('此处填写为话题类下心愿,每个心愿以enter换行结尾)');
        $form->footer(function ($footer) {
            // 去掉`重置`按钮
            $footer->disableReset();
            // 去掉`查看`checkbox
            $footer->disableViewCheck();
            // 去掉`继续编辑`checkbox
            $footer->disableEditingCheck();
            // 去掉`继续创建`checkbox
            $footer->disableCreatingCheck();
        });
        $form->tools(function ($tools) use ($l_id) {
            // 去掉`列表`按钮
            $tools->disableList();
            // 去掉`删除`按钮
            $tools->disableDelete();
            // 去掉`查看`按钮
            $tools->disableView();
            $url = route('danmuTpl.index', ['l_id' => $l_id]);
            $tools->append(new BackButton($url));
        });
        return $form;
    }

}
