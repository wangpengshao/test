<?php

namespace App\Admin\Controllers\Wechat\Recommend;

use App\Admin\Extensions\Tools\IconButton;
use App\Models\Recommend\RecommendBooks;
use App\Models\Recommend\RecommendIsbn;
use App\Models\Recommend\Isbn;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 推荐书单
 * Class RecommendBooksController
 * @package App\Admin\Controllers\Wechat\Recommend
 */
class BookSquareController extends Controller
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
        Admin::script($this->script());
        return $content
            ->header('书单广场')
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
        $grid->model()->where('token', '<>', session('wxtoken'))->orderBy('id', 'desc');
        $grid->filter(function (Grid\Filter $filter) {

            $filter->disableIdFilter();
            $filter->where(function ($query) {
                $query->where('title', 'like', "%{$this->input}%");
            }, '书单标题');

            $filter->between('created_at')->datetime();

            $filter->between('updated_at')->datetime();

        });
        $grid->column('title', '名称');
        $grid->image('封面图')->image('', 100, 100);
        $grid->hasManyCol('收藏状态')->display(function () {
            $col = $this->hasManyCol;
            if (!empty($col['c_id'])) {
                $str = '<span class="badge bg-green">已收藏</span>';
            } else {
                $str = '<span class="badge bg-red">未收藏</span>';
            }
            return $str;
        });
        $grid->column('view_num', '书单查看数')->display(function ($view_num) {
            return "<span class='badge bg-blue'>{$view_num}</span>";
        });
        $grid->column('col_num', '书单收藏数')->display(function ($col_num) {
            return "<span class='badge bg-blue'>{$col_num}</span>";
        });

        $grid->column('created_at', '创建时间');
        $grid->column('updated_at', '更新时间');
        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();
            // 判断该书单是否已经被收藏了
            $c_status = $actions->row->c_status; // 收藏状态
            if (!$c_status) {
                $actions->append("<button class='btn btn-xs btn-success margin-r-5 collect' data-oid='{$actions->row->id}' data-token='{$actions->row->token}'
>收藏</button>");
            }
            $url = route('bookList.index', [
                's_id' => $actions->row->id,
                'token' => $actions->row->token
            ]);
            $actions->prepend(new IconButton($url, '书籍列表', 'fa-book'));
        });
        $grid->disableCreateButton();
        return $grid;
    }

    /**
     * time  2019.12.2.
     *
     * @content  收藏书单
     *
     * @author  wsp
     */
    protected function collect(Request $request)
    {
        $data = $request->all();
        $res = RecommendBooks::where(['id' => $data['id'], 'token' => $data['token']])->first();
        $res_book = RecommendIsbn::where(['s_id' => $data['id'], 'token' => $data['token']])->get()->toArray();
        // 排除该管书单对应书籍下为空的情况
        if (empty($res_book)) {
            $re = ['status' => false, 'mes' => '该书单下为空书籍'];
            return $re;
        }
        // 获取添加书单后的最新id值，此为isbn表数据中的关联字段值
        $s_id = RecommendBooks::where('token', session('wxtoken'))->orderBy('id', 'desc')->limit(1)->count(['id']);
        // 添加书单数据
        $create = [
            'title' => $res['title'],
            'token' => session('wxtoken'),
            'image' => strstr($res['image'], 'wechat'),// 指定字符串位置开始截取，实现封面的正常显示
            'status' => 1,
            'a_status' => 1,
            'c_status' => 1,
            'intro' => $res['intro'],
            'stage_id' => $s_id + 1
        ];
        $addone = RecommendBooks::create($create);
        foreach ($res_book as $value) {
            // 判断需要收藏的Isbn是否已经在于isbn表库中
            $exists = Isbn::where(['token' => session('wxtoken'), 'isbn' => $value['isbn']])->exists();
            if (!$exists) {
                // 若不存在了该书籍的信息，则将该书籍添加到isbn表库中
                $insertOne[] = [
                    's_id' => $s_id + 1,
                    'c_id' => $data['id'],
                    'token' => session('wxtoken'),
                    'isbn' => $value['isbn'],
                    'reason' => $value['reason'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            // 需要添加的书籍信息
            $create_books[] = [
                's_id' => $s_id + 1,
                'c_id' => $data['id'],
                'token' => session('wxtoken'),
                'isbn' => $value['isbn'],
                'reason' => $value['reason'],
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }
        // 添加书籍数据
        $addtwo = RecommendIsbn::insert($create_books);
        if (!empty($insertOne)) {
            Isbn::insert($insertOne);
        }
        if ($addone && $addtwo) {
            $re = ['status' => true, 'mes' => '添加成功'];
            // 更新添加状态
            $state = [
                'c_status' => 1
            ];
            RecommendBooks::where(['id' => $data['id'], 'token' => $data['token']])->update($state);
        } else {
            $re = ['status' => false, 'mes' => '收藏失败'];
        }
        return $re;
    }

    protected function script()
    {
        $collectUrl = route('bookSquare.collect');
        return <<<SCRIPT
$('.collect').on('click', function () {
    var id=$(this).data('oid');
    var token=$(this).data('token');
    swal({
         title: '收藏该馆当前的书单?',
         type: 'question',
         showCancelButton: true,
         confirmButtonText: "是的",
         cancelButtonText: "否",
         showLoaderOnConfirm: true,
         allowOutsideClick: false,
         preConfirm: function() {
         return new Promise(function(resolve, reject) {
             $.ajax({
                        url: "{$collectUrl}",
                        type: "post",
                        data: {"_token": LA.token,"id":id,"token":token},
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