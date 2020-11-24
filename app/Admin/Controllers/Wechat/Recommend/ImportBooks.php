<?php

namespace App\Admin\Controllers\Wechat\Recommend;

use App\Admin\Extensions\ExcelImport\BooksImport;
use App\Models\Recommend\RecommendBooks;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ImportBooks extends Action
{
    public $name = '导入数据';

    protected $selector = '.library-import';

    public function handle(Request $request)
    {
        $token = $request->session()->get('wxtoken');
        // 取出书单表中当前书单id的最大值
        $res_id = RecommendBooks::orderBy('id', 'desc')->limit(1)->first(['id']);
        // 取出该管下当前书单的总期数
        $count = RecommendBooks::where('token', $token)->count();
        if (empty($res_id)) {
            $id = 1;
        } else {
            $id = $res_id->id +1; // 添加书单后的最新id
        }
        $count = !empty($count) ? $count+1 : 1;  // 添加书单后的最新期数
        $RecommendImport = new BooksImport();
        $RecommendImport->setParam($token, $id, $count);
        Excel::import($RecommendImport, request()->file('file'));
        return $this->response()->success('导入成功...')->refresh();
    }

    public function form()
    {
        $this->file('file', '文件格式:xlsx,文件大小限制:2MB')->required();
    }

    public function html()
    {
        return <<<HTML
        <a class="btn btn-sm btn-default library-import"><i class="fa fa-upload"></i>导入数据</a>
HTML;
    }

}
