<?php

namespace App\Admin\Controllers\Wechat\Suggestions;

use App\Admin\Extensions\Tools\IconButton;
use App\Http\Controllers\Controller;
use App\Models\Suggestions\SuggestionsTypes;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class SuggestionsTypeController extends Controller
{
    use HasResourceActions;

    protected $gatherArr = ['1' => '手机号码', '2' => '姓名', '3' => '邮箱', '4' => '图片'];

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
            ->header('留言类型管理')
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
        $grid = new Grid(new SuggestionsTypes);
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->expand();
            $filter->like('title', '类型名称');
        });
        $grid->model()->where('token', session('wxtoken'));
        $grid->title('类型名称');
        $states = [
            'on' => ['value' => 1, 'text' => '显示', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'default'],
        ];
        $grid->is_bind('是否绑定')->using([
            '0' => '<span class="badge bg-green">无需绑定</span>',
            '1' => '<span class="badge bg-yellow">需要绑定</span>',
        ]);
        $grid->column('gather', '收集信息')->map(function ($item) {
            if (is_numeric($item)) return $this->gatherArr[$item];
        })->label();

        $grid->column('addgather', '额外收集')->map(function ($item) {
            if ($item) {
                return $item['value'] . ':' . $item['key'];
            }
        })->label('primary');

        $grid->created_at('创建时间');
        $grid->updated_at('最后编辑时间');
        $grid->status('状态')->switch($states);

        $grid->actions(function ($actions) {
            $actions->disableView();
            $url = route('suggestions-list', ['id' => $actions->row->id]);
            $actions->prepend(new IconButton($url, '留言详情', 'fa-list'));
        });
        return $grid;
    }

    /*
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(SuggestionsTypes::findOrFail($id));
        $show->title('类型名称');
        $sta = SuggestionsTypes::where('id', $id)->first(['status']);
        switch ($sta['status']) {
            case 0 :
                $status = '关闭';
                break;
            default :
                $status = '开启';
                break;
        }
        $show->diy1('开启状态:')->as(function () use ($status) {
            return $status;
        })->badge();
        $show->created_at('创建时间');
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
        $form = new Form(new SuggestionsTypes);
        $form->text('title', '类型名称')->required();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableView();
        });

        $states = [
            'on' => ['value' => 1, 'text' => '显示', 'color' => 'primary'],
            'off' => ['value' => 0, 'text' => '隐藏', 'color' => 'default'],
        ];

        $form->switch('status', '状态')->default(0)->states($states);

        $form->switch('is_bind', '绑定读者')->help('必须绑定读者证才可留言');

        $form->checkbox('gather', '收集信息')->options($this->gatherArr)
            ->stacked()->help('图片为留言内容相关图片的补充!');

        $form->table('addgather', '自定文本字段', function ($table) {
            $table->text('value', '字段名称(中文)')->help('必填');
            $table->text('key', '键值')->help('必填,键值为 字段名称 的拼音形式,如 性别 则键值填 xingbie');
        });

        $form->hidden('token')->default(session('wxtoken'));
        return $form;
    }
}
