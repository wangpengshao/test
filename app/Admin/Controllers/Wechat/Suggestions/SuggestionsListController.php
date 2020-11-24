<?php

namespace App\Admin\Controllers\Wechat\Suggestions;

use App\Admin\Extensions\Tools\IconButton;
use App\Models\Suggestions\SuggestionsList;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\DB;

class SuggestionsListController extends Controller
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
        return $content->header('读者留言')
            ->description('管理')
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $request = \request();
        $s_id = $request->input('id', '');
        $token = $request->session()->get('wxtoken');

        $grid = new Grid(new SuggestionsList);
        $grid->disableCreateButton();

        $grid->model()->where('token', $token);
        if (!empty($s_id)) {
            $grid->model()->where('s_id', $s_id)->orderBy('id', 'desc');
        } else {
            $grid->model()->orderBy('id', 'desc');
        }

        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            // 获取留言建议类型表中的类型数据
            $suggestions_type = DB::table('w_suggestions_t')->get(['id', 'title'])->toArray();
            // 整合类型数据
            $type = [];
            foreach ($suggestions_type as $key => $value) {
                $type[$value->id] = $value->title;
            }
            $filter->equal('s_id', '类型')->select($type);
        });

        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
//            $url = url('admin/wechat/suggestions/details') . '?m_id=' . $actions->row->id . '&s_id=' . $actions->row->s_id;
            $url = route('suggestions-details', [
                'm_id' => $actions->row->id,
                's_id' => $actions->row->s_id,
            ]);
            $actions->prepend(new IconButton($url, '留言详情', 'fa-commenting'));
        });

        $grid->column('hasOneSuggestionType.title', '类型');
        $grid->column('title', '标题');
        $grid->column('openid', 'openid');
        $grid->column('name', '留言人');
        $grid->column('info', '留言内容')->limit(20);
        $grid->column('created_at', '留言时间')->sortable();

        // 通过关联表后将关联数据进行整合，避免sql(n+1)的情况
        $grid->column('hasManyMessages', '未读消息数')->display(function ($hasManyMessages) {
            $count = 0; // 初始化数值
            // 筛选出所有评论中未读的信息数量
            foreach ($hasManyMessages as $value) {
                if ($value['is_reading'] == 0) {
                    $count += 1;
                }
            }
            return "<span class='label label-warning'>{$count}</span>";
        });
        $states = [
            'on' => ['value' => 1, 'text' => '已完结', 'color' => 'default'],
            'off' => ['value' => 0, 'text' => '进行中', 'color' => 'primary'],
        ];
        $grid->status('状态')->switch($states)->sortable();
        return $grid;
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

    /*
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(SuggestionsList::findOrFail($id));
        $show->title('标题');
        $show->rdid('读者证号');
        $show->openid('openid');
        $show->info('留言内容');
        $show->img('img');
        $show->email('邮箱');
        $show->tel('手机号码');
        $show->created_at('创建时间');
        $show->updated_at('最后编辑时间');
        $show->panel()
            ->tools(function ($tools) use ($id) {
                $tools->disableEdit();
                $tools->disableDelete();
            });
        return $show;
    }

}
