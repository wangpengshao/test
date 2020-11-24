<?php

namespace App\Admin\Controllers\Wechat\LibraryLbs;

use App\Admin\Extensions\ExcelImport\LibraryLbsImport;
use Encore\Admin\Actions\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ImportData extends Action
{
    public $name = '导入数据';

    protected $selector = '.library-import';

    public function handle(Request $request)
    {
        $messages = [
            'required' => '请上传正确文件',
            'max' => '文件大小不能超过 2 MB',
            'mimes' => '文件必须是xlsx',
        ];
        $validator = Validator::make($request->file(), [
            'file' => 'required|mimes:xlsx|max:2048',
        ], $messages);

        if ($validator->fails()) {
            return $this->response()->error('抱歉:' . $validator->errors()->first('file'));
        }

        try {
            $token = $request->session()->get('wxtoken');
            $libraryLbsImport = new LibraryLbsImport();
            $libraryLbsImport->setToken($token);
            Excel::import($libraryLbsImport, request()->file('file'));
            return $this->response()->success('导入成功...')->refresh();
        } catch (\Exception $e) {
            $errMes = '';
            $failures = $e->failures();
            foreach ($failures as $failure) {
                $errMes .= '第' . $failure->row() . '行-' . implode($failure->errors());
            }
            return $this->response()->error('产生错误：' . $errMes);
        }
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

    //弹窗式表单，提交权限控制
    public function authorize($user, $model)
    {
        if ($user->can('wechat.libraryLbs.location')) {
            return true;
        }
        return false;
    }
}
