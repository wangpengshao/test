<?php

namespace App\Admin\Controllers\Wechat;

use App\Admin\Extensions\ExcelExporter\DepositExporter;
use App\Models\Wechat\Imagewechat;
use App\Models\Wechat\Release;
use App\Models\Wechat\ReleaseRelevance;
use App\Models\Wxuser;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class ReleaseController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = '微门户发布中心';
    protected $description = [
//        'index' => 'Index',
//        'show'   => 'Show',
//        'edit'   => 'Edit',
//        'create' => 'Create',
    ];

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $wxuserList = Wxuser::pluck('wxname', 'token')->toArray();
        $grid = new Grid(new Release);

        $grid->actions(function ($actions) {
            $actions->disableDelete();
            $actions->disableView();
        });
        $grid->model()->orderBy('created_at', 'desc');
        $grid->fixColumns(4, -2);
//        $grid->column('id', __('Id'));
        $grid->column('describe', '发布描述');
        $grid->column('type', '发布类型')->using([
            '1' => '<span class="label label-primary">首页轮播图</span>',
//            '2' => '<span class="label label-default"></span>',
//            '3' => '<span class="label label-default"></span>',
//            '4' => '<span class="label label-warning"></span>',
        ]);

        $grid->column('target_type', '目标类型')->using([
            1 => '全部馆',
            2 => '个别',
        ], '未知')->dot([
            1 => 'success',
            2 => 'info',
        ], 'warning');

        $grid->column('target_token', '筛选')->display(function ($target_token) use ($wxuserList) {
            $str = '';
            $target_token = array_filter($target_token);
            foreach ($target_token as $k => $v) {
                if ($this->target_type === 1 && $k === 0) {
                    $str .= '<span class="label label-default">排除以下馆:</span>&nbsp;';
                }
                if ($v && isset($wxuserList[$v])) {
                    $str .= '&nbsp;<span class="badge bg-brown">' . $wxuserList[$v] . '</span>&nbsp;';
                }
            }
            return $str;
        });
        $grid->column('content', __('Content'));
        $grid->column('img', __('Img'))->image('', 100, 100);
        $grid->column('url', __('Url'))->link()->width(20);
        $grid->column('updated_at', __('Updated at'))->sortable();
        $grid->column('created_at', __('Created at'))->sortable();
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
        $show = new Show(Release::findOrFail($id));

        $show->field('id', __('Id'));
        $show->field('type', __('Type'));
        $show->field('template_id', __('Template id'));
        $show->field('target_type', __('Target type'));
        $show->field('target_token', __('Target token'));
        $show->field('content', __('Content'));
        $show->field('url', __('Url'));
        $show->field('img', __('Img'));
        $show->field('created_at', __('Created at'));
        $show->field('updated_at', __('Updated at'));

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $type_options = [
            1 => '首页轮播图'
        ];
        $template_id_options = [
            0 => '模版一  [ 2018新版(绿色) ]',
            1 => '模版二  [ 九宫格 ]',
        ];
        $target_type_options = [
            1 => '全部馆',
            2 => '个别',
        ];
        $request = request();

        $form = new Form(new Release);
        $isCreating = $form->isCreating();
        $form->tools(function (Form\Tools $tools) {
            $tools->disableDelete();          // 去掉`删除`按钮
            $tools->disableView();           // 去掉`查看`按钮
        });

        $form->text('describe', '发布描述')->required();
        if ($isCreating) {
            $form->select('type', '发布类型')->options($type_options)->default(1)->required();
            $form->select('template_id', '主题模版')->options($template_id_options)->default(0)->required();
            $form->select('target_type', '目标类型')->options($target_type_options)->default(1)->required();
            $form->multipleSelect('target_token', '筛选目标')->options(function ($code) {
                if (is_array($code) && count($code) > 0) {
                    return Wxuser::pluck('wxname', 'token')->toArray();
                }
            })->ajax('/admin/apiuseradmin/wxUser/search', 'token', 'wxname')
                ->placeholder('输入 公众号名称或者token 进行搜索,可填写多个!');

        } else {
            $form->display('type', '发布类型')->with(function ($value) use ($type_options) {
                return Arr::get($type_options, $value);
            });
            $form->display('template_id', '主题模版')->with(function ($value) use ($template_id_options) {
                return Arr::get($template_id_options, $value);
            });
            $form->display('target_type', '目标类型')->with(function ($value) use ($target_type_options) {
                return Arr::get($target_type_options, $value);
            });
            $form->display('target_token', '筛选目标')->with(function ($value) {
                $value = array_filter($value);
                $str = ' ';
                if (is_array($value) && count($value) > 0) {
                    $wxuser_list = Wxuser::pluck('wxname', 'token')->toArray();
                    $only = Arr::only($wxuser_list, $value);
                    foreach ($only as $k => $v) {
                        $str .= '<span class="badge bg-brown">' . $v . '</span>&nbsp;';
                    }
                }
                return $str;
            });
            $form->radio('is_refresh', '强制更新')->options([1 => '是', 2 => '否'])->default(2)->stacked()
                ->help('注意:编辑内容时,此项为 <span style="color: red">是</span> 时将会对已发布的数据进行强制更新,请谨慎选择');
        }
        $form->divider();

        $form->text('content', __('Content'));
        $form->url('url', __('Url'));
        $form->number('order', '排序')->default(0);

        $form->image('img', __('Img'))->attribute('hideMaterial')->move('releaseImage')->creationRules(function () use ($request) {
            if ($request->input('type') == 1) {
                return 'required';
            }
        });

        $form->ignore('is_refresh');

        $form->saved(function (Form $form) use ($request) {

            if ($request->isMethod('post')) {
                $this->typeManage($form->model());
            }
            if ($request->isMethod('put') && $request->input('is_refresh') == 1) {
                $this->updateTypeManage($form->model());
            }
        });
        return $form;
    }

    public function updateTypeManage($model)
    {
        //首页轮播图
        if ($model->type == 1) {
            $releaseRelevance = ReleaseRelevance::where('r_id', $model->id)->get();
            $releaseRelevance->each(function ($item, $index) use ($model) {
                $model_url = $model->url;
                $url = str_replace('{token}', $item->token, $model_url);
                $updateData = [
                    'order' => $model->order,
                    'url' => $url,
                    'caption' => $model->content,
                    'image' => $model->img,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                Imagewechat::where('id', $item->data_id)->update($updateData);
                Cache::forget('vueIndex:img:' . $item->token);
            });
        }
    }

    public function typeManage($model)
    {
        //首页轮播图
        if ($model->type == 1) {
            $template_id = $model->template_id;
            $target_type = $model->target_type;
            $target_token = $model->target_token;
            $content = $model->content;
            $url = $model->url;
            $img = $model->img;
            $order = $model->order;
            $r_id = $model->id;

            $createImage = [
                'image' => $img,
                'caption' => $content,
                'status' => 1,
                'order' => $order
            ];
            //发布类型 => 全部
            $target_token = array_filter($target_token);
            $wxuser_list = [];
            $wxuserModel = Wxuser::where('template_id', $template_id);

            if ($target_type == 1) {
                if (count($target_token) > 0) {
                    $wxuser_list = $wxuserModel->whereNotIn('token', $target_token)->pluck('token')->toArray();
                } else {
                    $wxuser_list = $wxuserModel->pluck('token')->toArray();
                }
                //发送全部 且存在 排除馆
            } else {
                //发送类型 => 个别馆
                if (count($target_token) > 0) {
                    $wxuser_list = $wxuserModel->whereIn('token', $target_token)->pluck('token')->toArray();
                }
            }
            foreach ($wxuser_list as $k => $v) {
                $real_url = str_replace('{token}', $v, $url);
                $createImage['token'] = $v;
                $createImage['url'] = $real_url;
                $data = Imagewechat::create($createImage);
                if ($data) {
                    $createReleaseRelevance = [
                        'data_id' => $data->id,
                        'token' => $v,
                        'r_id' => $r_id,
                    ];
                    ReleaseRelevance::create($createReleaseRelevance);
                }
            }
        }
    }

}
