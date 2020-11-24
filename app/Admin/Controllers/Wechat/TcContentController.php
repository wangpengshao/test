<?php

namespace App\Admin\Controllers\Wechat;

use App\Models\Wechat\TcContent;
use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Show;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class TcContentController extends Controller
{
    use HasResourceActions;

    protected $typeName = [1 => '功能发布', 2 => '时事中心', '3' => '业务通知'];

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
            ->header('内容管理')
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
        $grid = new Grid(new TcContent);
        $grid->model()->orderBy('id', 'desc');
        $grid->id('Id');
        $grid->type('类型')->using($this->typeName);
//        $options = [1 => 'on', 0 => 'off'];
        $grid->status('状态')->switch();
        $grid->title('标题');
        $grid->description('说明');

        $grid->user_id('编辑者')->display(function ($user_id) {
            return Administrator::find($user_id)->value('name');
        });
        $grid->views('查看数');

        $grid->created_at('创建时间');
        $grid->updated_at('跟新时间');


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
        $show = new Show(TcContent::findOrFail($id));

        $show->id('Id');
        $show->type('类型')->using($this->typeName);

        $show->user_id('发布者')->as(function ($user_id) {
            return Administrator::find($user_id)->value('name');
        });

        $show->content('内容');

        $show->views('浏览次数');
        $show->title('标题');
        $show->description('说明');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new TcContent);
        $form->select('type', '类型')->options($this->typeName)->rules('required');

        $form->switch('status', '显示');

        $form->text('title', '标题');
        $form->text('description', '说明');
        $form->editor('content', '内容');
        $form->hidden('user_id')->default(Admin::user()->id);
        return $form;
    }

    public function showContent(Request $request, Content $content)
    {
        $id = $request->route('id');
        $model = new TcContent();
        $detail = $model->where([
            'id' => $id,
            'status' => 1
        ])->first();
        $detail->increment('views');
        if (empty($detail)) {
            return back()->with(admin_toastr('非法访问', 'error'));
        }
        $content->header(Arr::get($this->typeName, $detail['type'], ''));

        $where = [
            'status' => 1,
            'type' => $detail['type']
        ];
        $list = $model->where($where)->orderBy('id', 'desc')->limit(10)->get(['id', 'title']);

        //        // 获取 “上一篇” 的 ID
        $previousId = $model->where($where)->where('id', '<', $id)->max('id');
        $previousUrl = $previousId ? route('tcContent-show', ['id' => $previousId]) : 'javascript:volid(0);';
//        // 同理，获取 “下一篇” 的 ID
        $nextId = $model->where($where)->where('id', '>', $id)->min('id');
        $nextUrl = $nextId ? route('tcContent-show', ['id' => $nextId]) : 'javascript:volid(0);';
        $compact = [
            'list' => $list,
            'detail' => $detail,
            'previousUrl' => $previousUrl,
            'nextUrl' => $nextUrl,
        ];
//
//        dd($previousId,$nextId);
        $content->row(view('admin.Custom.showContent', $compact));

        return $content;
    }

    public function home()
    {
        return Admin::content(function (Content $content) {

            $content->header('Index');
            $content->description('...');

            $content->row(view('admin.Custom.newsTitle'));

            $content->row(function (Row $row) {
                $getField = ['id', 'title', 'description', 'created_at'];
                $list1 = [
                    'title' => $this->typeName[1],
                    'envs' => []
                ];
                $list = TcContent::where([
                    'status' => 1,
                    'type' => 1
                ])->orderBy('id', 'desc')->limit(10)->get($getField);
                foreach ($list as $k => $v) {
                    $url = route('tcContent-show', ['id' => $v['id']]);
                    $list1['envs'][] = [
                        'name' => "<a href='{$url}'>{$v['title']}</a>",
                        'value' => Str::limit($v['created_at'], 10, '')
                    ];
                }
                $row->column(4, function (Column $column) use ($list1) {
                    $column->append(view('admin.Custom.newsPublish', $list1));
                });

                $list2 = [
                    'title' => $this->typeName[2],
                    'envs' => []
                ];
                $list = TcContent::where([
                    'status' => 1,
                    'type' => 2
                ])->orderBy('id', 'desc')->limit(10)->get($getField);
                foreach ($list as $k => $v) {
                    $url = route('tcContent-show', ['id' => $v['id']]);
                    $list2['envs'][] = [
                        'name' => "<a href='{$url}'>{$v['title']}</a>",
                        'value' => Str::limit($v['created_at'], 10, '')
                    ];
                }

                $row->column(4, function (Column $column) use ($list2) {
                    $column->append(view('admin.Custom.newsPublish', $list2));
                });

                $list3 = [
                    'title' => $this->typeName[3],
                    'envs' => []
                ];
                $list = TcContent::where([
                    'status' => 1,
                    'type' => 3
                ])->orderBy('id', 'desc')->limit(10)->get($getField);
                foreach ($list as $k => $v) {
                    $url = route('tcContent-show', ['id' => $v['id']]);
                    $list3['envs'][] = [
                        'name' => "<a href='{$url}'>{$v['title']}</a>",
                        'value' => Str::limit($v['created_at'], 10, '')
                    ];
                }

                $row->column(4, function (Column $column) use ($list3) {
                    $column->append(view('admin.Custom.newsPublish', $list3));
                });

            });

        });
    }

}
