<?php

namespace App\Admin\Controllers\Wechat\Recommend;

use App\Admin\Extensions\Tools\IconButton;
use App\Models\Recommend\MessageList;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class MessageListController extends Controller
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
        return $content->header('留言')
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
        $token = $request->session()->get('wxtoken');

        $grid = new Grid(new MessageList);
        $grid->disableCreateButton();

        $grid->model()->where(['token' => $token, 'r_id' => 1])->orderBy('id', 'desc');
        $grid->filter(function (Grid\Filter $filter) {

            $filter->disableIdFilter();
            $filter->where(function ($query) {
                $query->whereHas('hasOneBook', function ($query) {
                    $query->where('title', 'like', "%{$this->input}%");
                });
            }, '书单标题');

            $filter->equal('openid');

            $filter->between('created_at')->datetime();

            $filter->between('updated_at')->datetime();

        });
        $grid->actions(function ($actions) {
            $actions->disableEdit();
            $actions->disableView();
            $actions->disableDelete();
//            $url = url('admin/wechat/suggestions/details') . '?m_id=' . $actions->row->id . '&s_id=' . $actions->row->s_id;
            $url = route('message.details', [
                'm_id' => $actions->row->m_id
            ]);
            $actions->prepend(new IconButton($url, '留言详情', 'fa-commenting'));
        });
        $grid->column('hasOneBook.title', '书单标题');
        $grid->column('openid', 'openid')->style('max-width:140px;word-break:break-all;');
        $grid->column('name', '留言人');
        $grid->column('r_reply', '读者留言内容')->limit(20)->style('max-width:150px;word-break:break-all;');
        $grid->column('created_at', '留言时间')->sortable();

        $grid->column('is_reading', '是否已读')->display(function ($is_reading) {
            if ($is_reading) {
                return "<span class='label label-success'>已读</span>";
            } else {
                return "<span class='label label-warning'>未读</span>";
            }
        });
        $states = [
            'on' => ['value' => 1, 'text' => '已完结', 'color' => 'default'],
            'off' => ['value' => 0, 'text' => '进行中', 'color' => 'primary'],
        ];
        $grid->status('状态')->switch($states)->sortable();
        return $grid;
    }

}
