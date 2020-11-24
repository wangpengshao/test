<?php

namespace App\Admin\Controllers\Wechat\Share;

use App\Api\Helpers\ApiResponse;
use App\Models\Wechat\UserArticlesStore;
use App\Models\Wechat\Articles;
use App\Models\Wxuser;
use Encore\Admin\Admin;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RegionalSharingController extends Controller
{
    use HasResourceActions, ApiResponse;
    protected $id;
    protected $type;

    public function __construct(Request $request)
    {
        $this->type = $request->route('type');
        $this->id = $request->route('id');
    }

    public function index(Content $content)
    {
        // 根据type值来区分省市区操作
        switch ($this->type) {
            case 1:
                $headerName = '省|';
                break;
            case 2:
                $headerName = '市|';
                break;
            default:
                $headerName = '区|';
        }
        $description = '共享';
        return $content->header($headerName)->description($description)->body($this->grid());

    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Articles);
        $where = [
            'token' => session('wxtoken'),
            'status' => 1
        ];
        switch ($this->type) {
            case 1:
                $where['is_province'] = 1;
                break;
            case 2:
                $where['is_city'] = 1;
                break;
            default:
                $where['is_district'] = 1;
        }
        $grid->model()->where($where);
        $grid->model()->orderBy('created_at', 'desc');

        $grid->column('hasOneCategories.title', '分类');
        $grid->title('标题');
        $grid->description('描述')->limit(50);
        $grid->picture('封面')->image('', 60, 60);
        $grid->created_at('创建时间');
        $grid->actions(function ($actions) {
            //关闭行操作
            $actions->disableEdit();
            $actions->disableDelete();
        });
        $grid->disableFilter();
        $grid->disableExport();
        $grid->disableRowSelector();
        $grid->disableColumnSelector();
        $grid->disableCreateButton();
        return $grid;
    }

    public function view(Content $content)
    {
        Admin::script($this->viewScript());
        $token = request()->session()->get('wxtoken');
        $where = [
            'token' => $token,
            'status' => 1,
            'id' => $this->id
        ];
        switch ($this->type) {
            case 1:
                $where['is_province'] = 1;
                break;
            case 2:
                $where['is_city'] = 1;
                break;
            default:
                $where['is_district'] = 1;
        }
        $article = Articles::where($where)->first();
        if (empty($article)) {
            return $content->withError('警告', '非法访问..');
        }
        $isStore = UserArticlesStore::where(['token' => $token, 'article_id' => $this->id])->first();
        $data = [
            'article' => $article,
            'author' => Wxuser::getCache($token)->value('wxname'),
            'backUrl' => route('share.regionalShare.index', ['type' => $this->type]),
            'isStore' => $isStore
        ];
        switch ($this->type) {
            case 1:
                $view = view('admin.share.proviceshare', $data);
                break;
            case 2:
                $view = view('admin.share.cityshare', $data);
                break;
            default:
                $view = view('admin.share.districtshare', $data);
        }
        return $content->header('文章预览')->body($view);
    }

    public function edit(Request $request)
    {
        $id = $request->route('id');
        $exists = UserArticlesStore::where('article_id', $id)->exists();
        //根据type类型判断出来需要更新的status值
        $data = array(
            'article_id' => $request->route('id'),
            'store_status' => $request->route('status'),
            'token' => session('wxtoken')
        );
        if ($exists == false) {
            $status = UserArticlesStore::insert($data);
        } else {
            $status = UserArticlesStore::where('article_id', $id)->update($data);
        }
        if ($status) {
            return $this->message('操作成功', true);
        }
        return $this->message('操作失败', false);
    }

    public function viewScript()
    {
        return <<<EOT
            $('.addPower').on('click', function () {
                var id = $(this).data('uid');
                var status = 1;
                swal({
                    title: "确认要该文章助力?",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "确认",
                    showLoaderOnConfirm: true,
                    cancelButtonText: "取消",
                    preConfirm: function() {
                        return new Promise(function(resolve) {
                            $.ajax({
                                method: 'post',
                                url: '/admin/share/regionalShare/eddit/'+id+'/'+status,
                                data: {
                                    _token:LA.token,
                                },
                                success: function (data) {
                                    if(data.status==true){
                                       swal({
                                          type:"success",
                                          text: data.message
                                        });
                                    }else{
                                       swal({
                                            type: "error",
                                            title: data.message
                                        });
                                    }
                                    $.pjax.reload('#pjax-container');
                                },error:function(){
                                }
                            });
                        });
                    }
                }).then(function(result) {

                });
        });
        $('.cancelPower').on('click', function () {
                var id = $(this).data('uid');
                var status = 0;
                swal({
                    title: "确认要取消助力?",
                    type: "info",
                    showCancelButton: true,
                    confirmButtonText: "确认",
                    showLoaderOnConfirm: true,
                    cancelButtonText: "取消",
                    preConfirm: function() {
                        return new Promise(function(resolve) {
                            $.ajax({
                                method: 'post',
                                url: '/admin/share/regionalShare/eddit/'+id+'/'+status,
                                data: {
                                    _token:LA.token,
                                },
                                success: function (data) {
                                    if(data.status==true){
                                       swal({
                                          type:"success",
                                          text: data.message
                                        });
                                    }else{
                                       swal({
                                            type: "error",
                                            title: data.message
                                        });
                                    }
                                    $.pjax.reload('#pjax-container');
                                },
                                error:function(){
                                
                                }
                            });
                        });
                    }
                }).then(function(result) {
                
                });
        });
EOT;
    }
}
