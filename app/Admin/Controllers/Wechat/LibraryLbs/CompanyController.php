<?php

namespace App\Admin\Controllers\Wechat\LibraryLbs;

use App\Admin\Extensions\ExcelExporter\LbsImportDome;
use App\Admin\Extensions\ExcelExporter\LibraryLbsExporter;
use App\Admin\Extensions\Tools\Button;
use App\Admin\Extensions\Tools\IconButton;
use App\Models\ChinaArea;
use App\Models\LibraryLbs\Company;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Column;
use Encore\Admin\Layout\Content;
use Encore\Admin\Layout\Row;
use Encore\Admin\Widgets\Box;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

/**
 * 图书馆LBS定位
 * Class CompanyController
 * @package App\Admin\Controllers\Wechat\LibraryLbs
 */
class CompanyController extends Controller
{
    use HasResourceActions;

    /**
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        $content->header('单位信息');
        $content->description('编辑');
        $content->row(function (Row $row) {
            $row->column(10, function (Column $column) {
                $request = request();
                $token = $request->session()->get('wxtoken');
                $type = $request->input('type');
                $where = ['token' => $token];
                $p_id = 1;
                switch ($type) {
                    case 'create':
                        $data = null;
                        break;
                    case 'edit':
                        if (!$request->filled('id')) {
                            return admin_error('警告', '非法访问');
                        }
                        $where['p_id'] = $p_id;
                        $where['id'] = $request->input('id');
                        $data = Company::where($where)->first();
                        break;
                    default:
                        $p_id = 0;
                        $where['p_id'] = $p_id;
                        $data = Company::where($where)->first();
                }
                //逻辑判断 start

                //逻辑判断 end
                $form = new \Encore\Admin\Widgets\Form($data);
                $url = ($data) ? route('wechat.libraryLbs.up', $data->id) : route('wechat.libraryLbs.add');
                $form->action($url);
                $form->hidden('token')->default($token);
                $form->hidden('p_id')->default($p_id);
                $form->display('id', '单位ID');
                $form->text('name', '单位名称')->required();
                $form->switch('is_show', '显示');
                $form->image('logo', 'Logo地址')->move(materialUrl())->uniqueName();
                $form->number('order', '排序')->default(0);
                $form->text('telephone', '联系方式');
                $form->mobile('phone', '手机号码')->options(['mask' => '999 9999 9999']);
                $form->textarea('intro', '简介');

                $form->select('province_id', '省')->options(
                    ChinaArea::province()->pluck('name', 'id')
                )->load('city_id', '/admin/api/china/city');
                $form->select('city_id', '市')->options(function ($id) {
                    return ChinaArea::options($id);
                })->load('district_id', '/admin/api/china/district');
                $form->select('district_id', '区')->options(function ($id) {
                    return ChinaArea::options($id);
                });
                $form->text('address', '详细地址')->required();
                $form->map('lat', 'lng', '地图坐标');
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

                $column->append((new Box(' ', $form))->style('success'));
            });
        });
        return $content;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    public function form()
    {
        return Admin::form(Company::class, function (Form $form) {
            $form->hidden('token');
            $form->display('id', '单位ID');
            $form->text('name', '单位名称')->required();
            $form->switch('is_show', '显示');
            $form->hidden('p_id');
            $form->image('logo', 'Logo地址')->move(materialUrl())->uniqueName();
            $form->number('order', '排序')->default(0);
            $form->text('telephone', '联系方式');
            $form->mobile('phone', '手机号码')->options(['mask' => '999 9999 9999']);
            $form->textarea('intro', '简介');
            $form->select('province_id', '省');
            $form->select('city_id', '市');
            $form->select('district_id', '区');
            $form->text('address', '详细地址')->required();
            $form->map('lat', 'lng', '地图坐标');
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

            $form->saved(function (Form $form) {
                admin_toastr('保存成功!', 'success');
                if ($form->model()->p_id == 1) {
                    return redirect(route('wechat.libraryLbs.list'));
                }
                return back();
            });
        });
    }

    /**
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Company);
        $token = request()->session()->get('wxtoken');

        $grid->filter(function ($filter) {
            // 去掉默认的id过滤器
            $filter->disableIdFilter();
            // 在这里添加字段过滤器
            $filter->like('name', '单位名称');
        });
        $grid->disableExport(false);
        $grid->exporter(new LibraryLbsExporter());

        $grid->header(function ($query) use ($token) {
            $url = route('LbsLocation::index', ['token' => $token]);
            return '<div class="callout callout-success"><h4>展示地址</h4><p>' . $url . '</p></div>';
        });

        $grid->model()->where('token', $token);
        $grid->model()->where('p_id', '<>', 0);
        $grid->model()->orderBy('order', 'desc');

        $grid->column('name', '单位名称');
        $grid->column('logo')->image('', 60, 60);
        $grid->column('telephone', '联系方式');
        $grid->column('phone', '手机号码');
        $grid->column('address', '详细地址');
        $grid->column('order', '排序')->sortable();

        $grid->column('is_show', '状态')->using([
            '0' => '<span class="badge ">隐藏</span>',
            '1' => '<span class="badge bg-green">显示</span>',
        ])->sortable();

        $grid->tools(function (Grid\Tools $tools) {
            $url = route('wechat.libraryLbs.show', ['type' => 'create']);
            $tools->append(new Button($url, '新增单位', 'fa-plus', false, 'success'));
            $url = 'https://lbs.qq.com/tool/getpoint/';
            $tools->append(new Button($url, '坐标拾取器', 'fa-map-pin', true, 'warning'));
            $url = route('wechat.libraryLbs.exportDome');
            $tools->append(new Button($url, '下载模板', 'fa-download', true));
            $tools->append(new ImportData());
        });

        $grid->actions(function ($actions) {
            $actions->disableView();
            $actions->disableEdit();
            $actions->disableDelete();

            $url = route('wechat.libraryLbs.show', ['id' => $actions->row->id, 'type' => 'edit']);
            $actions->append(new IconButton($url, '编辑', 'fa-edit'));
        });
        $grid->disableCreateButton();
        return $grid;
    }

    /**
     * @param Content $content
     * @return Content
     */
    public function list(Content $content)
    {
        $token = request()->session()->get('wxtoken');
        $exists = Company::where(['token' => $token, 'p_id' => 0])->exists();
        if (!$exists) {
            return $content->withWarning('提示', '请先完善主单位信息,才可编辑分单位!');
        }
        return $content->header('分馆单位|')->description('管理')->body($this->grid());
    }

    /**
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportDome()
    {
        $export = new LbsImportDome([
            ['name', 'telephone', 'phone', 'lat', 'lng', 'address', 'intro', '',
                '注意:第二行为当前列的文字说明,编写或上传时需要将此行删除掉,其中name、lat、lng都是必须要填写的列'],
            ['xxxxx图书馆', '固定电话', '手机号码', '定位坐标', '定位坐标', '详细地址', '简介'],
        ]);
        return Excel::download($export, 'LBS定位导入模版.xlsx');
    }
}
