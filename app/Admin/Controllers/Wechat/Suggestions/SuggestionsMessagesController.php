<?php

namespace App\Admin\Controllers\Wechat\Suggestions;

use App\Admin\Extensions\Tools\IconButton;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use App\Models\Suggestions\SuggestionsMessages;
use Illuminate\Support\Facades\DB;

class SuggestionsMessagesController extends Controller
{
    use HasResourceActions;

    protected $is_reading = ['0' => '未读', '1' => '已读'];

    /**
     * Index interface.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function index(Content $content)
    {
        return $content->header('最新回复')
            ->description('...')
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new SuggestionsMessages);
        // 只显示读者回复的信息
        $grid->model()->where(['token' => session('wxtoken'), 'r_id' => 1])->orderBy('created_at', 'Desc');
        $grid->expandFilter();
        $grid->disableCreateButton();
        $grid->filter(function ($filter) {
            $filter->disableIdFilter();
            $filter->equal('is_reading', '是否已读')->select($this->is_reading);

            // 获取留言建议类型表中的类型数据
            $suggestions_type = DB::table('w_suggestions_t')->get(['id', 'title'])->toArray();
            // 整合类型数据
            $type = [];
            foreach ($suggestions_type as $key => $value) {
                $type[$value->id] = $value->title;
            }
            $filter->equal('s_id', '留言类型')->select($type);
        });

        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
            $url = url('admin/wechat/suggestions/details') . '?m_id=' . $actions->row->id . '&s_id=' . $actions->row->s_id;
            $actions->prepend(new IconButton($url, '留言详情', 'fa-commenting'));
        });

        $grid->column('hasOneType.title', '留言类型');
        $grid->column('hasOneSuggestions.title', '留言标题');
        $grid->column('r_reply', '读者回复信息内容')->limit(20);;
//        $grid->column('administrator_reply', '管理员回复信息内容')->limit(20);;
        $grid->column('is_reading', '是否已读')->using([
            0 => '未读',
            1 => '已读',
        ])->dot([
            0 => 'info',
            1 => 'success',
        ])->sortable();
        $grid->column('created_at', '回复信息时间');
        return $grid;
    }

}


