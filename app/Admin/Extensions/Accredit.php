<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class Accredit
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    protected function script()
    {
        $postUrl = route('accreditToken');
        return <<<SCRIPT

$('.accredit').on('click', function () {
    var wxtoken = $(this).data('token');
    swal({
     title: '授权操作',
      showCancelButton: true,
      confirmButtonText: "确认",
      cancelButtonText: "取消",
      preConfirm: function(number) {
        let data={_token: LA.token};
        let headers={'Content-Type': 'application/json'};
        return fetch('{$postUrl}/'+ wxtoken ,{method: 'POST',body: JSON.stringify(data),headers: new Headers(headers)}
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
         swal(result.message,'','success');
         setTimeout(function(){ window.location.reload();}, 2000);
    })

});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());
        return "<a  class='accredit btn btn-xs btn-default' title='授权' data-toggle='tooltip' data-token='{$this->token}'><i class='fa fa-paper-plane'></i></a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}
