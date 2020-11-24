<?php

namespace App\Admin\Controllers\Mini;

use App\Admin\Extensions\Tools\BackButton;
use App\Models\Mini\RegistrationImg;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use Illuminate\Support\Facades\Cache;

class CefImgController extends Controller
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
            ->header('图片素材')
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
        if ($request->filled('miniToken')) {
            $request->session()->put('gridWhere.miniToken', $request->input('miniToken'));
        }
        $miniToken = $request->session()->get('gridWhere.miniToken');
        if (empty($miniToken)) {
            admin_error('提示', '非法访问');
            return redirect()->back();
        }

        $grid = new Grid(new RegistrationImg);

        $grid->tools(function ($tools) {
            $tools->append(new BackButton(url('admin/miniProgram/certificate/config'), '授权列表'));
        });
        $grid->model()->where('token', $miniToken);

        $grid->id('编号');
        $grid->column('title', '标题');
//        $grid->token('token');
        $grid->key('Key');
        $grid->img('图片')->image('', 80, 80);

        $grid->status('状态')->switch();
//        $grid->created_at('Created at');

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
        $show = new Show(RegistrationImg::findOrFail($id));

        $show->id('Id');
        $show->status('Status');
        $show->token('Token');
        $show->key('Key');
        $show->img('Img');
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
        $form = new Form(new RegistrationImg);
        $request = request();
        if ($request->filled('miniToken')) {
            $request->session()->put('gridWhere.miniToken', $request->input('miniToken'));
        }
        $miniToken = $request->session()->get('gridWhere.miniToken');
        if (empty($miniToken)) {
            admin_error('提示', '非法访问');
            return redirect()->back();
        }
        $form->text('title', '描述');

        $form->switch('status', '状态');
        $form->hidden('token')->default($miniToken);
        $form->text('key', 'Key');

        $file = '/miniCef/' . $miniToken;
        $form->image('img', '图片')->move($file)->uniqueName()->removable();

        $form->saved(function (Form $form) {
            Cache::forget('mini:register:' . $form->model()->token . ':c');
        });
        return $form;
    }
}
