<?php

namespace App\Admin\Extensions\Form;

use Encore\Admin\Form\Field;

class CKEditor extends Field
{
    protected static $js = [
        'vendor/ckeditor4.12.1/ckeditor.js',
    ];

    protected $view = 'admin.ckeditor';

    public function render()
    {
        $url = route('CKEditorUpload');
        $this->script = <<<EOT
        CKEDITOR.replace('{$this->id}',{
          extraPlugins: 'image2,uploadimage,colorbutton,font,justify',
          fileTools_requestHeaders:{
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          filebrowserImageUploadUrl: '{$url}',
          // Upload dropped or pasted images to the CKFinder connector (note that the response type is set to JSON).
          uploadUrl: '/apps/ckfinder/3.4.5/core/connector/php/connector.php?command=QuickUpload&type=Files&responseType=json',
          // Reduce the list of block elements listed in the Format drop-down to the most commonly used.
          format_tags: 'p;h1;h2;h3;pre',
          // Simplify the Image and Link dialog windows. The "Advanced" tab is not needed in most cases.
          removeDialogTabs: 'image:advanced;link:advanced',
          height: 430,
          width:"auto"
        });
EOT;
        return parent::render();
    }


}
