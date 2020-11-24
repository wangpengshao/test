<?php

namespace App\Admin\Extensions\Button;

use Encore\Admin\Admin;

class EditBalance
{
    protected $id;

    public function __construct($id)
    {
        $this->id = $id;

    }

    protected function script()
    {
        $postUrl = route('spark-payers.balance.edit');
        return <<<SCRIPT
$('.editBalance').on('click', function () {
   let id =$(this).data('id');
   let money;
   swal.setDefaults({
      confirmButtonText: '下一步',
      showCancelButton: true,
      animation: false,
      allowOutsideClick: false,
      progressSteps: [1, 2,3]
   });

    let steps = [
      {
        title: '余额管理',
        text:'请输入需要增加的金额(允许负数,负数即减少)',
        input: 'number',
        inputPlaceholder:'整数',
        inputValidator: function (value) {
            return new Promise(function (resolve, reject) {
              if (value && parseInt(value, 10) == value) {
                money = value;
                resolve();
              } else {
                reject('请输入金额(整数)');
              }
            })
         }
      },
       {
            text: '请简述此次操作,方便记录',
            input: 'text',
            confirmButtonText:'提交',
            inputValidator: function (value) {
                return new Promise(function (resolve, reject) {
                  if (value) {
                    resolve()
                  } else {
                    reject('你需要输入一些东西')
                  }
               })
            },
            preConfirm: function(desc) {
                swal.showLoading();
                let data={"_token": LA.token,"number":money,"id":id,"desc":desc};
                let headers={'Content-Type': 'application/json'};
                return fetch(
                    '{$postUrl}',
                    {method: 'POST',body: JSON.stringify(data),headers: new Headers(headers)}
                ).then(response => {
                    if (!response.ok) {
                      throw new Error(response.statusText)
                    }
                    return response.json()
                }).then(data=>{
                    let queueData = {"title":data.message,"type":"error","showCancelButton":false,"confirmButtonText":"OK"}
                    if(data.status === true){
                        queueData.type="success";
                    }
                    swal.insertQueueStep(queueData);
                    return queueData.type;
                }).then(state => {
                   if(state==="success"){
                      $.pjax.reload('#pjax-container');
                   }
                 }).catch(error => {throw new Error(error);})
             }
        }
    ];
    
    swal.queue(steps).finally(function() {
      swal.resetDefaults();
    }).catch(swal.noop);
    
});

SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());
        return "<a  class='editBalance btn btn-xs btn-primary' title='余额编辑' data-toggle='tooltip' data-id='{$this->id}'>&nbsp;<i class='fa fa-jpy'></i>&nbsp;</a>";
    }

    public function __toString()
    {
        return $this->render();
    }
}
