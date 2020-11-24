<?php

namespace App\Admin\Controllers;

use App\Models\Relationusers;
use App\Models\RelationWxuser;
use App\Models\Wxuser;
use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Database\Permission;
use Encore\Admin\Auth\Database\Role;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;

class SecondaryuserController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('用户管理');
            $content->description('description');
            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     *
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {

            $relationData = Relationusers::where('parent_id', Admin::user()->id)->pluck('user_id');
            if (!$relationData->contains($id)) {
                return $content->withError('警告', '非法访问路径，请文明操作！');
            }

            $content->header('编辑');
            $content->body($this->form($id)->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('header');
            $content->description('description');

            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Administrator::class, function (Grid $grid) {

            $grid->filter(function ($filter) {
                // 去掉默认的id过滤器
                $filter->disableIdFilter();
                $filter->expand();
                $filter->column(1 / 2, function ($filter) {
                    $filter->equal('username', '用户名');
                });
                $filter->column(1 / 2, function ($filter) {
                    $filter->like('name', '名称');
                });
            });

            $relationData = Relationusers::where('parent_id', Admin::user()->id)->pluck('user_id');
            $grid->model()->whereIn('id', $relationData);
            $grid->id('ID')->sortable();
            $grid->username(trans('admin.username'));
            $grid->name(trans('admin.name'));
            $grid->roles(trans('admin.roles'))->pluck('name')->label();
//            $grid->column('创建者ID')->display(function () {
//                return $this->first_name . ' ' . $this->last_name;
//            });
            $grid->created_at(trans('admin.created_at'));
            $grid->updated_at(trans('admin.updated_at'));
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                if ($actions->getKey() == 1) {
                    $actions->disableDelete();
                }
                $actions->disableView();
            });
            $grid->tools(function (Grid\Tools $tools) {
                $tools->batch(function (Grid\Tools\BatchActions $actions) {
                    $actions->disableDelete();
                });
            });
        });
    }


    protected function form($id = null)
    {
        return Admin::form(Administrator::class, function (Form $form) use ($id) {

            $form->tools(function (Form\Tools $tools) {
                $tools->disableView();
            });

            $form->display('id', 'ID');
            $form->text('username', trans('admin.username'))->rules('required');
            $form->text('name', trans('admin.name'))->rules('required');
            $form->image('avatar', trans('admin.avatar'));
            $form->password('password', trans('admin.password'))->rules('required|confirmed');
            $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
                ->default(function ($form) {
                    return $form->model()->password;
                });

            $form->ignore(['password_confirmation']);
            $form->multipleSelect('roles', trans('admin.roles'))
                ->options(Role::whereIn('id', Admin::user()->roles->pluck('id')->except(0))
                    ->pluck('name', 'id'));

            $form->checkbox('permissions', trans('admin.permissions'))
                ->options(Permission::whereIn('id', Admin::user()->permissions->pluck('id'))->orderBy('id')
                    ->pluck('name', 'id'))->help('用户中心的权限非特殊要求,不要进行分配!')->stacked();

            $form->multipleSelect('wxusers.wxuser_id', '公众号授权')
                ->options($this->getWxuserOption($id, Admin::user()->id))
                ->ajax('/admin/newuser/wxUser/search?id=' . $id . '&admin_id=' . Admin::user()->id, 'id', 'wxname')
                ->default(RelationWxuser::where('user_id', $id)->pluck('wxuser_id')->toArray())
                ->help('输入 公众号名称或者token 进行搜索,可填写多个!');


            $form->display('created_at', trans('admin.created_at'));
            $form->display('updated_at', trans('admin.updated_at'));

            $form->saving(function (Form $form) {
                //如果新增用户 先进行判断用户账号是否已存在
                if (\request()->isMethod('post')) {
                    $exists = Administrator::where('username', $form->username)->exists();
                    if ($exists) {
                        $message = sprintf('抱歉,用户名为 %s 用户已存在！', $form->username);
                        $error = new MessageBag([
                            'title' => '提示',
                            'message' => $message,
                        ]);
                        return back()->with(compact('error'));
                    }
                }

                if ($form->password && $form->model()->password != $form->password) {
                    $form->password = bcrypt($form->password);
                }
            });

            $form->saved(function (Form $form) {

                $user_id = $form->model()->id;
                //判断是否创建过
                $where = [
                    'parent_id' => Admin::user()->id,
                    'user_id' => $form->model()->id,
                ];

                $exists = Relationusers::where($where)->exists();
                if ($exists) {
                    $user = Administrator::with(['roles:role_id', 'permissions:permission_id'])->find($user_id);
                    $roles = $user->roles->toArray();
                    $rolesArr = [];
                    foreach ($roles as $k => $v) {
                        $rolesArr[] = $v['role_id'];
                    }
                    unset($k, $v);

                    $permissions = $user->permissions->toArray();
                    $permissionsArr = [];
                    foreach ($permissions as $k => $v) {
                        $permissionsArr[] = $v['permission_id'];
                    }
                    unset($k, $v);
                    //权限缩小   删除权限
                    $geneArr = Relationusers::where('gene', 'like', '%,' . $user_id . ',%')->pluck('user_id');
                    $geneList = Administrator::with(['roles:role_id', 'permissions:permission_id'])->whereIn('id', $geneArr)->get();
                    foreach ($geneList as $k => $v) {
                        foreach ($v->roles as $key => $val) {
                            if (!in_array($val->role_id, $rolesArr)) {
                                $val->pivot->delete();
                            }
                        }
                        unset($key, $val);
                        foreach ($v->permissions as $key => $val) {
                            if (!in_array($val->permission_id, $permissionsArr)) {
                                $val->pivot->delete();
                            }
                        }
                        unset($key, $val);
                    }

                    $formWxuser = array_filter($form->wxusers['wxuser_id']);
                    $wxusers = RelationWxuser::where('user_id', $form->model()->id)->pluck('wxuser_id')->toArray();
                    //列出需要加的
                    $newWx = array_diff($formWxuser, $wxusers);
                    if (count($newWx) > 0) {
                        $insert = [];
                        foreach ($newWx as $k => $v) {
                            $insert[] = [
                                'user_id' => $user_id,
                                'wxuser_id' => $v
                            ];
                        }
                        unset($k, $v);
                        RelationWxuser::insert($insert);
                    }

                    //列出需要删的
                    $delWx = array_diff($wxusers, $formWxuser);
                    if (count($delWx) > 0) {
                        foreach ($delWx as $k => $v) {
                            $where = [
                                'user_id' => $user_id,
                                'wxuser_id' => $v
                            ];
                            RelationWxuser::where($where)->delete();
                            //下级删除
                            RelationWxuser::where('wxuser_id', $v)->whereIn('user_id', $geneArr)->delete();
                        }
                    }

                } else {
                    //加入基因
                    $gene = Relationusers::where(['user_id' => Admin::user()->id])->value('gene');
                    $where['gene'] = ($gene) ? $gene . Admin::user()->id . ',' : ',-1,' . Admin::user()->id . ',';
                    Relationusers::create($where);
                    $formWxuser = array_filter($form->wxusers['wxuser_id']);
                    if (count($formWxuser) > 0) {
                        $insert = [];
                        foreach ($formWxuser as $k => $v) {
                            $insert[] = [
                                'user_id' => $user_id,
                                'wxuser_id' => $v
                            ];
                        }
                        unset($k, $v);
                        RelationWxuser::insert($insert);
                    }
                }

            });

        });
    }

    public function getWxuserOption($id, $admin_id)
    {
        $childList = Relationusers::where('gene', 'like', '%,' . $admin_id . ',%')->pluck('user_id')->toArray();
        $wxuserList = RelationWxuser::when($id, function ($q) use ($id, $admin_id) {
            return $q->whereIn('user_id', [$id, $admin_id]);
        }, function ($q) use ($admin_id) {
            return $q->where('user_id', $admin_id);
        })->pluck('wxuser_id')->unique()->toArray();
        $response = Wxuser::whereIn('id', $wxuserList)->orWhereIn('user_id', $childList)->pluck('wxname', 'id')->unique();
        return $response;
    }

    public function searchWxuser(Request $request)
    {
        $q = $request->get('q');
        $id = $request->input('id');

        $admin_id = $request->input('admin_id');
        $response = $this->getWxuserOption($id, $admin_id)->toArray();
        $response = array_keys($response);

        $is_zw = preg_match("/[\x7f-\xff]/", $q);
        $model = Wxuser::select('id', 'wxname')->whereIn('id', $response)
            ->when($is_zw, function ($model) use ($q) {
                return $model->where('wxname', 'like', "%$q%");
            }, function ($model) use ($q) {
                return $model->where('token', 'like', "%$q%");
            });
        return $model->paginate();
    }

}
