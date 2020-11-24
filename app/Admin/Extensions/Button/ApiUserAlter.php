<?php

namespace App\Admin\Extensions\Button;

use Encore\Admin\Admin;

class ApiUserAlter
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    protected function script()
    {
        $postUrl = route('apiuseradmin.userAlter');
        return <<<SCRIPT
$('.userAlter').on('click', function () {
   let id =$(this).data('id');
   swal({
     titleText: '用户接口次数编辑',
     text:'请输入需要增加的次数(允许负数,负数即减少次数)',
     showCancelButton: true,
     confirmButtonText: '提交',
     showLoaderOnConfirm: true,
     input: 'number',
     inputPlaceholder:'整数',
     inputValidator: function (value) {
        return new Promise(function (resolve, reject) {
          if (value && parseInt(value, 10) == value) {
            resolve();
          } else {
            reject('你需要输入一些整数');
          }
        })
     },
     preConfirm: function(number) {
        let data={_token: LA.token,number:number,id:id};
        let headers={'Content-Type': 'application/json'};
        return fetch('{$postUrl}',{method: 'POST',body: JSON.stringify(data),headers: new Headers(headers)}
        ).then(response => {
            if (!response.ok) {
              throw new Error(response.statusText)
            }
            return response.json()
        }).catch(error => {throw new Error(error);})
     },
     allowOutsideClick: false,
    }).then(result => {
      $.pjax.reload('#pjax-container');
      swal('编辑完成','','success');
    }) .catch(swal.noop);
});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());
        return "<a  class='userAlter btn btn-xs btn-default' title='次数编辑' data-toggle='tooltip' data-id='{$this->id}'><i class='fa fa-plus'></i></a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}
