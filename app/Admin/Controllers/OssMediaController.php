<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\CustomMethod\OssMediaManager;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class OssMediaController extends Controller
{
    public function index(Request $request)
    {
        return Admin::content(function (Content $content) use ($request) {

            $content->header('Oss Media manager');

            $path = $request->get('path', '/');
            $view = $request->get('view', 'table');
            $view = ucfirst($view);

            $manager = new OssMediaManager($path);
            $content->body(view("admin.diy.oss" . $view, [
                'list' => $manager->ls(),
                'nav' => $manager->navigation(),
                'url' => $manager->urls(),
            ]));
        });
    }

    public function download(Request $request)
    {
        $file = $request->get('file');

        $manager = new OssMediaManager($file);

        return $manager->download();
    }

    public function upload(Request $request)
    {
        $files = $request->file('files');
        $dir = $request->get('dir', '/');

        $manager = new OssMediaManager($dir);

        try {
            if ($manager->upload($files)) {
                admin_toastr(trans('admin.upload_succeeded'));
            }
        } catch (\Exception $e) {
            admin_toastr($e->getMessage(), 'error');
        }

        return back();
    }

    public function delete(Request $request)
    {
        $files = $request->get('files');

        $manager = new OssMediaManager();

        try {
            if ($manager->delete($files)) {
                return response()->json([
                    'status' => true,
                    'message' => trans('admin.delete_succeeded'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function move(Request $request)
    {
        $path = $request->get('path');
        $new = $request->get('new');

        $manager = new OssMediaManager($path);

        try {
            if ($manager->move($new)) {
                return response()->json([
                    'status' => true,
                    'message' => trans('admin.move_succeeded'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function newFolder(Request $request)
    {
        $dir = $request->get('dir');
        $name = $request->get('name');

        $manager = new OssMediaManager($dir);

        try {
            if ($manager->newFolder($name)) {
                return response()->json([
                    'status' => true,
                    'message' => trans('admin.move_succeeded'),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => true,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function wangEditorUpload(Request $request)
    {
        $files = $request->file();
        $dir = '/wangEditor/common/' . date('Ym');
        if (!empty(session('wxtoken'))) {
            $dir = '/wangEditor/' . session('wxtoken') . '/' . date('Ym');
        }
        $disk = config('admin.extensions.oss-media-manager.disk');
        $storage = Storage::disk($disk);

        $data = [];
        foreach ($files as $k => $v) {
            $url = $storage->url($storage->putFile($dir, $v));
            if (filter_var($url, FILTER_VALIDATE_URL)) {
                $data[] = $url;
            }
        }
        $errno = (count($data) > 0) ? 0 : 1;
        return ['errno' => $errno, 'data' => $data];
    }

    public function CKEditorUpload(Request $request)
    {
        $postFile = 'upload';
        $allowedPrefix = ['jpg', 'jpeg', 'png', 'gif'];
        //检查文件是否上传成功
        if (!$request->hasFile($postFile) || !$request->file($postFile)->isValid()) {
            return $this->CKEditorUploadResponse(0, '文件上传失败');
        }
        $extension = $request->file($postFile)->extension();
        $size = $request->file($postFile)->getSize();
//        $filename = $request->file($postFile)->getClientOriginalName();
        //检查后缀名
        if (!in_array($extension, $allowedPrefix)) {
            return $this->CKEditorUploadResponse(0, '文件类型不合法');
        }
        if ($size > 10 * 1024 * 1024) {
            return $this->CKEditorUploadResponse(0, '文件大小超过限制');
        }
        $dir = '/wangEditor/common/' . date('Ym');
        if (!empty(session('wxtoken'))) {
            $dir = '/wangEditor/' . $request->session()->get('wxtoken') . '/' . date('Ym');
        }
        $disk = config('admin.extensions.oss-media-manager.disk');
        $storage = Storage::disk($disk);
        $url = $storage->url($storage->putFile($dir, $request->file($postFile)));
        return $this->CKEditorUploadResponse(1, '', '', $url);
    }


    private function CKEditorUploadResponse($uploaded, $error = '', $filename = '', $url = '')
    {
        return [
            "uploaded" => $uploaded,
            "fileName" => $filename,
            "url" => $url,
            "error" => [
                "message" => $error
            ]
        ];
    }

}
