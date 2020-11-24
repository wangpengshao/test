<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

class WangEditor extends Field
{
    protected $view = 'admin.wang-editor';

    protected static $css = [
        '/vendor/wangEditor-3.0.10/release/wangEditor.css',
    ];

    protected static $js = [
        '/vendor/wangEditor-3.0.10/release/wangEditor.js',
    ];

    public function render()
    {
        $name = $this->formatName($this->column);
        $url = route('wangEditorUpload');
        $token = csrf_token();
        $this->script = <<<EOT

var E = window.wangEditor
var editor = new E('#{$this->id}');
editor.customConfig.zIndex = 0
//editor.customConfig.uploadImgShowBase64 = true
 editor.customConfig.uploadImgServer='{$url}';
 editor.customConfig.uploadImgParams = {
    "_token": '{$token}'
}
editor.customConfig.uploadImgHooks = {
    before: function (xhr, editor, files) {
        // 图片上传之前触发
        // xhr 是 XMLHttpRequst 对象，editor 是编辑器对象，files 是选择的图片文件
               console.log(123);
        // 如果返回的结果是 {prevent: true, msg: 'xxxx'} 则表示用户放弃上传
        // return {
        //     prevent: true,
        //     msg: '放弃上传'
        // }
    },
    success: function (xhr, editor, result) {
        // 图片上传并返回结果，图片插入成功之后触发
        // xhr 是 XMLHttpRequst 对象，editor 是编辑器对象，result 是服务器端返回的结果
        console.log(xhr);
        console.log(editor);
        console.log(result);
    },
    fail: function (xhr, editor, result) {
        // 图片上传并返回结果，但图片插入错误时触发
        // xhr 是 XMLHttpRequst 对象，editor 是编辑器对象，result 是服务器端返回的结果
    },
    error: function (xhr, editor) {
        console.log(1);
        // 图片上传出错时触发
        // xhr 是 XMLHttpRequst 对象，editor 是编辑器对象
    },
    timeout: function (xhr, editor) {
    
        // 图片上传超时时触发
        // xhr 是 XMLHttpRequst 对象，editor 是编辑器对象
    }
}

editor.customConfig.onchange = function (html) {
    $('input[name=$name]').val(html);
}

editor.create()

EOT;
        return parent::render();
    }
}


