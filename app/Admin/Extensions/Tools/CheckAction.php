<?php

namespace App\Admin\Extensions\Tools;

use Encore\Admin\Grid\Tools\BatchAction;

class CheckAction extends BatchAction
{

    protected $action;

    public function __construct($action = 1)
    {
        $this->action = $action;
    }


    public function script()
    {
        return <<<EOT

$('{$this->getElementClass()}').on('click', function() {
    $.ajax({
        method: 'post',
        url: '{$this->resource}/check',
        data: {
            _token:LA.token,
            ids: selectedRows().join(),
            action: {$this->action}
        },
        success: function () {
            $.pjax.reload('#pjax-container');
            toastr.success('操作成功');
        }
    });
});
   function selectedRows(){
        var selected = [];
        $('.grid-row-checkbox:checked').each(function(){
            selected.push($(this).data('id'));
        });
        return selected;
   }

EOT;

    }
}
