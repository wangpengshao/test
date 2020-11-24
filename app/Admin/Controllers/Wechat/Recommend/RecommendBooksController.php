<?php

namespace App\Admin\Controllers\Wechat\Recommend;

use App\Admin\Extensions\Tools\IconButton;
use App\Http\Controllers\Controller;
use App\Models\Recommend\Isbn;
use App\Models\Recommend\RecommendBooks;
use App\Models\Recommend\RecommendIsbn;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;

/**
 * 推荐书单
 * Class RecommendBooksController
 * @package App\Admin\Controllers\Wechat\Recommend
 */
class RecommendBooksController extends Controller
{
    use HasResourceActions;

    protected $states = [
        'on' => ['value' => 1, 'text' => '开启', 'color' => 'primary'],
        'off' => ['value' => 0, 'text' => '关闭', 'color' => 'default'],
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
        Admin::script($this->script());
        return $content
            ->header('书单列表')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new RecommendBooks);
        $grid->model()->where('token', session('wxtoken'))->orderBy('id', 'desc');
        $grid->filter(function (Grid\Filter $filter) {

            $filter->disableIdFilter();
            $filter->where(function ($query) {
                $query->where('title', 'like', "%{$this->input}%");
            }, '书单标题');

            $filter->between('created_at')->datetime();

            $filter->between('updated_at')->datetime();

        });
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableDelete();
            $url = route('message.details', [
                'm_id' => $actions->row->id
            ]);
            $actions->prepend(new IconButton($url, '留言详情', 'fa-commenting'));
            $urlt = route('bookList.index', [
                's_id' => $actions->row->id,
                'token' => $actions->row->token
            ]);
            $actions->prepend(new IconButton($urlt, '书籍列表', 'fa-book'));

            // 获取手动添加的isbn
            $isbn = $actions->row->isbn;
            $a_status = $actions->row->a_status; // 添加状态
            $isbn = json_encode($isbn); // 将数组重新json化
            // 判断该isbn是否已添加到isbn表库中
            if (!empty($isbn) && $a_status != 1) {
                $actions->append("<button class='btn btn-xs btn-success margin-r-5 addIsbn' data-oid='{$actions->row->id}' data-token='{$actions->row->token}'
 data-isbn='{$isbn}'>添加至书库</button>");
            }
        });
        $grid->column('title', '名称');
        $grid->image('封面图')->image('', 100, 100);
        $grid->column('stage_id', '期数')->display(function ($stage_id) {
            return "<span class='label label-success'>第&nbsp;{$stage_id}&nbsp;期</span>";
        });
        $grid->status('分享状态')->switch($this->states);
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
        $grid->column('created_at', '创建时间')->sortable();
        $grid->column('updated_at', '更新时间')->sortable();
        $grid->tools(function (Grid\Tools $tools) {
            $tools->append(new ImportBooks());
        });
        return $grid;
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
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        $form = new Form(new RecommendBooks);
        $form->hidden('token')->default(session('wxtoken'));
        // 判断表单提交是create方式还是edit，若是create方式，则有默认期数
        if ($form->isCreating()) {
            // 获取当前管内最新一期书单id值
            $id = RecommendBooks::where('token', session('wxtoken'))->count('id');
            ++$id;
            $form->number('stage_id', '书单期数')->default($id);
        } else {
            $form->number('stage_id', '书单期数');
        }
        $form->text('title', '书单名称')->required();
        $form->text('intro', '推荐简介')->required();
        $form->switch('status', '分享状态')->states($this->states);
        $form->image('image', '书单封面')->move(materialUrl())->uniqueName();
        // 判断表单提交是create方式还是edit，若是create方式，则有默认期数

        // 手动添加isbn
        $form->table('isbn', '手动新增isbn', function ($table) {
            $table->text('key', 'isbn')->help('isbn(必填)');
            $table->text('value', 'reason')->help('推荐理由(必填)');
        });

//            $form->editor('agreement', '协议内容');
        // 两个时间显示
        $form->date('created_at', '创建时间');
        $form->date('updated_at', '修改时间');
        /* 素材库 上传图片 例子 start */
        $form->hidden(config('materialPR') . 'logo');
        $imgArray = [config('materialPR') . 'logo'];
        $form->ignore($imgArray);
        /* 素材库 上传图片 例子 end */
        $form->saving(function (Form $form) use ($imgArray) {
            foreach ($imgArray as $k => $v) {
                if (\request()->input($v)) {
                    $imgName = substr($v, strlen(config('materialPR')));
                    $form->model()->$imgName = \request()->input($v);
                }
            }
            unset($k, $v);
        });
        $form->tools(function (Form\Tools $tools) {
            // 去掉`删除`按钮
            $tools->disableDelete();
            // 去掉`查看`按钮
            $tools->disableView();
        });
        return $form;
    }

    /**
     * time  2019.12.2.
     *
     * @content  收藏书单
     *
     * @author  wsp
     */
    protected function saveIsbn(Request $request)
    {
        $data = $request->all();
        foreach ($data['isbn'] as $key => $value) {
            // 判断需要添加的Isbn是否已经在于isbn表库中
            $exists = Isbn::where(['token' => session('wxtoken'), 'isbn' => $value['key']])->exists();
            if (!$exists) {
                // 若不存在了该书籍的信息，则将该书籍添加到isbn表库中
                $insertOne[] = [
                    's_id' => $data['id'],
                    'token' => session('wxtoken'),
                    'isbn' => $value['key'],
                    'reason' => $value['value'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            $insert[] = [
                's_id' => $data['id'],
                'token' => session('wxtoken'),
                'isbn' => $value['key'],
                'reason' => $value['value'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        if (!empty($insertOne)) {
            Isbn::insert($insertOne);
        }
        $res = RecommendIsbn::insert($insert);
        // 添加书籍数据
        if ($res) {
            $re = ['status' => true, 'mes' => '添加成功'];
            // 更新添加状态
            $state = [
                'a_status' => 1
            ];
            RecommendBooks::where(['id' => $data['id'], 'token' => session('wxtoken')])->update($state);
        } else {
            $re = ['status' => false, 'mes' => '收藏失败'];
        }
        return $re;
    }

    protected function script()
    {
        $saveIsbnUrl = route('saveIsbn');
        return <<<SCRIPT
$('.addIsbn').on('click', function () {
    var id=$(this).data('oid');
    var isbn=$(this).data('isbn');
    swal({
         title: '添加书籍到书库中?',
         type: 'question',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$saveIsbnUrl}",
                        type: "post",
                        data: {"_token": LA.token,"id":id,"isbn":isbn},
                        dataType: "json",
                        success: function (data) {
                          var type="error";
                          if(data.status==true){
                            type="success";
                          }
                          swal('',data.mes,type);
                          $.pjax.reload('#pjax-container');
                        },
                        error:function(){
                         swal("哎呦……", "出错了！","error");
                        }
                    });
            
                });
              },
         }).then(result => {
         console.log(result)
    })
});

SCRIPT;
    }

}