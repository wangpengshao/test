<script type="text/javascript" charset="utf-8" src="{{asset('wechatAdmin/js/bootstrap-clockpicker.min.js')}}"></script>
<script type="text/javascript" charset="utf-8" src="{{asset('wechatAdmin/js/laydate.js')}}"></script>
<script type="text/javascript" charset="utf-8" src="{{asset('wechatAdmin/js/layui.js')}}"></script>
<script type="text/javascript" charset="utf-8" src="{{asset('wechatAdmin/js/jquery.jscrollpane.js')}}"></script>
<style>
    .red{
        color: #f00;
    }
    .week{
        margin-bottom: 10px;
    }
    .week-text,.checkbox{
        position: relative;
        margin-top: 7px;
    }

    ul,li{
        padding: 0;
        margin: 0;
        list-style: none;
    }
    .big{
        width: 800px;
        height: 290px;
        margin: 5px auto;
    }
    .one{
        width: 100%;
        height: 30px;
        line-height: 30px;
    }
    .bottom{
        width: 100%;
        height: 60px;
        text-align: center;
        margin-top:20px;
        line-height: 30px;
    }
    .bottom input{
        padding: 4px 12px;
        cursor: pointer;
    }
    .pleaseSelect{
        float: left;
        width: 40%;
    }
    .alreadyChoose{
        margin-left: 20%;
        float: left;
        width: 40%;
    }
    .box{
        width: 100%;
        height: 200px;
        overflow: hidden;
    }
    .b_left{
        width: 40%;
        height: 100%;
        float: left;
    }
    .b_right{
        width: 40%;
        height: 100%;
        float: left;
    }
    .box li{
        width: 96%;
        padding: 0 4%;
        height: auto;
        margin: 0;
        line-height: 26px;
        cursor: pointer;
    }
    .box li:hover{
        background: #ccc;
    }
    .b_center{
        width: 19%;
        float: left;
        text-align: center;
        line-height: 200px;
        font-size: 30px;
        height: 100%;
        background: #fff;
    }
    .jspContainer{
        background: #fff;
    }
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">
<form  id="form" action="/admin/deposit/time_rules/setting/config" method="post" onsubmit="return false;">
    @csrf
    <div class="form-group ">
    <span class="red">*</span><label class="control-label">工作时间:</label>
    <section style="padding-left: 5%;">
        <div class="week col-md-10">
            <div class="control-label col-md-1 week-text" style="">星期一</div>
            <div class="col-md-9" style="overflow: hidden;">
                <div style="overflow: hidden;padding-bottom: 5px;">
                    <div class="control-label col-md-1 week-text">上午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week1" placeholder="开始时间" type="text"   @if ($week1[0] == 'true')  required="required" @endif name="week1_1"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week1"  placeholder="结束时间" type="text" @if ($week1[0] == 'true')  required="required" @endif name="week1_2"  ></div>
                </div>
                <div style="overflow: hidden">
                    <div class="control-label col-md-1 week-text">下午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week1" placeholder="开始时间" type="text"  @if ($week1[0] == 'true')  required="required" @endif name="week1_3"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week1" placeholder="结束时间" type="text" @if ($week1[0] == 'true')  required="required" @endif name="week1_4"  ></div>
                </div>
            </div>
            <div class="checkbox col-md-2" style="">
                <label>
                    <input type="checkbox" name="week1_check" @if ($week1[0] == 'false') checked @endif > 闭馆
                </label>
            </div>
        </div>

        <div class="week col-md-10">
            <div class="control-label col-md-1 week-text" style="">星期二</div>
            <div class="col-md-9" style="overflow: hidden;">
                <div style="overflow: hidden;padding-bottom: 5px;">
                    <div class="control-label col-md-1 week-text">上午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week2" placeholder="开始时间" type="text"  @if ($week2[0] == 'true')  required="required" @endif name="week2_1"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week2" placeholder="结束时间" type="text"  @if ($week2[0] == 'true') required="required"  @endif name="week2_2"  ></div>
                </div>
                <div style="overflow: hidden">
                    <div class="control-label col-md-1 week-text">下午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week2" placeholder="开始时间" type="text"   @if ($week2[0] == 'true') required="required"  @endif name="week2_3"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week2" placeholder="结束时间" type="text"  @if ($week2[0] == 'true') required="required"  @endif name="week2_4"  ></div>
                </div>
            </div>
            <div class="checkbox col-md-2" style="">
                <label>
                    <input type="checkbox" name="week2_check" @if ($week2[0] == 'false') checked @endif > 闭馆
                </label>
            </div>
        </div>
        <div class="week col-md-10">
            <div class="control-label col-md-1 week-text" style="">星期三</div>
            <div class="col-md-9" style="overflow: hidden;">
                <div style="overflow: hidden;padding-bottom: 5px;">
                    <div class="control-label col-md-1 week-text">上午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week3" placeholder="开始时间" type="text"  @if ($week3[0] == 'true')  required="required" @endif name="week3_1"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week3" placeholder="结束时间" type="text"  @if ($week3[0] == 'true') required="required"  @endif name="week3_2"  ></div>
                </div>
                <div style="overflow: hidden">
                    <div class="control-label col-md-1 week-text">下午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week3" placeholder="开始时间" type="text"   @if ($week3[0] == 'true') required="required"  @endif name="week3_3"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week3" placeholder="结束时间" type="text"  @if ($week3[0] == 'true') required="required"  @endif name="week3_4"  ></div>
                </div>
            </div>
            <div class="checkbox col-md-2" style="">
                <label>
                    <input type="checkbox" name="week3_check" @if ($week3[0] == 'false') checked @endif > 闭馆
                </label>
            </div>
        </div>
        <div class="week col-md-10">
            <div class="control-label col-md-1 week-text" style="">星期四</div>
            <div class="col-md-9" style="overflow: hidden;">
                <div style="overflow: hidden;padding-bottom: 5px;">
                    <div class="control-label col-md-1 week-text">上午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week4" placeholder="开始时间" type="text"  @if ($week4[0] == 'true')  required="required" @endif name="week4_1"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week4" placeholder="结束时间" type="text"  @if ($week4[0] == 'true') required="required"  @endif name="week4_2"  ></div>
                </div>
                <div style="overflow: hidden">
                    <div class="control-label col-md-1 week-text">下午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week4" placeholder="开始时间" type="text"   @if ($week4[0] == 'true') required="required"  @endif name="week4_3"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week4" placeholder="结束时间" type="text"  @if ($week4[0] == 'true') required="required"  @endif name="week4_4"  ></div>
                </div>
            </div>
            <div class="checkbox col-md-2" style="">
                <label>
                    <input type="checkbox" name="week4_check" @if ($week4[0] == 'false') checked @endif > 闭馆
                </label>
            </div>
        </div>
        <div class="week col-md-10">
            <div class="control-label col-md-1 week-text" style="">星期五</div>
            <div class="col-md-9" style="overflow: hidden;">
                <div style="overflow: hidden;padding-bottom: 5px;">
                    <div class="control-label col-md-1 week-text">上午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week5" placeholder="开始时间" type="text"  @if ($week5[0] == 'true')  required="required" @endif name="week5_1"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week5" placeholder="结束时间" type="text"  @if ($week5[0] == 'true') required="required"  @endif name="week5_2"  ></div>
                </div>
                <div style="overflow: hidden">
                    <div class="control-label col-md-1 week-text">下午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week5" placeholder="开始时间" type="text"   @if ($week5[0] == 'true') required="required"  @endif name="week5_3"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week5" placeholder="结束时间" type="text"  @if ($week5[0] == 'true') required="required"  @endif name="week5_4"  ></div>
                </div>
            </div>
            <div class="checkbox col-md-2" style="">
                <label>
                    <input type="checkbox" name="week5_check" @if ($week5[0] == 'false') checked @endif > 闭馆
                </label>
            </div>
        </div>
        <div class="week col-md-10">
            <div class="control-label col-md-1 week-text" style="">星期六</div>
            <div class="col-md-9" style="overflow: hidden;">
                <div style="overflow: hidden;padding-bottom: 5px;">
                    <div class="control-label col-md-1 week-text">上午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week6" placeholder="开始时间" type="text"  @if ($week6[0] == 'true')  required="required" @endif name="week6_1"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week6" placeholder="结束时间" type="text"  @if ($week6[0] == 'true') required="required"  @endif name="week6_2"  ></div>
                </div>
                <div style="overflow: hidden">
                    <div class="control-label col-md-1 week-text">下午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week6" placeholder="开始时间" type="text"   @if ($week6[0] == 'true') required="required"  @endif name="week6_3"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week6" placeholder="结束时间" type="text"  @if ($week6[0] == 'true') required="required"  @endif name="week6_4"  ></div>
                </div>
            </div>
            <div class="checkbox col-md-2" style="">
                <label>
                    <input type="checkbox" name="week6_check" @if ($week6[0] == 'false') checked @endif > 闭馆
                </label>
            </div>
        </div>
        <div class="week col-md-10">
            <div class="control-label col-md-1 week-text" style="">星期日</div>
            <div class="col-md-9" style="overflow: hidden;">
                <div style="overflow: hidden;padding-bottom: 5px;">
                    <div class="control-label col-md-1 week-text">上午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week0" placeholder="开始时间" type="text"  @if ($week0[0] == 'true')  required="required" @endif name="week0_1"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week0" placeholder="结束时间" type="text"  @if ($week0[0] == 'true') required="required"  @endif name="week0_2"  ></div>
                </div>
                <div style="overflow: hidden">
                    <div class="control-label col-md-1 week-text">下午:</div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week0" placeholder="开始时间" type="text"   @if ($week0[0] == 'true') required="required"  @endif name="week0_3"  ></div>
                    <div class="col-md-5"><input  class="form-control col-md-5 week0" placeholder="结束时间" type="text"  @if ($week0[0] == 'true') required="required"  @endif name="week0_4"  ></div>
                </div>
            </div>
            <div class="checkbox col-md-2" style="">
                <label>
                    <input type="checkbox" name="week0_check" @if ($week0[0] == 'false') checked @endif > 闭馆
                </label>
            </div>
        </div>
        <div style="clear: both;"></div>
    </section>
    </div>
    <div class="form-group ">
        <label class="control-label">黑名单规则:</label>
        <div class="big">
            <div class="one">
                <div class="pleaseSelect">请选择：</div>
                <div class="alreadyChoose">已选择：</div>
            </div>
            <div class="box">
                <ul class="b_left jspScrollable" tabindex="0" style="overflow: auto; padding: 0px; width: 40%;border:1px solid #ccc;">
                    <div class="jspContainer" style="width: 100%; height: 200px;">
                        <div class="jspPane" style="overflow:auto;padding: 0px; width: 318px;height:200px; top: 0px;">
                            <li scale="0" data-value="1-3">违约1次暂停该读者预约3天</li>
                            <li scale="1" data-value="2-7">违约2次暂停该读者预约7天</li>
                            <li scale="2" data-value="3-30">违约3次该读者暂停预约30天</li>
                            <li scale="3" data-value="4-0">违约4次停止该读者网上预约服务</li>
                        </div>
                        <div class="jspVerticalBar" style="display: block; opacity: 0;">
                            <div class="jspCap jspCapTop"></div>
                            <div class="jspTrack" style="height: 200px;">
                                <div class="jspDrag" style="height: 154px;"><div class="jspDragTop"></div>
                                    <div class="jspDragBottom"></div></div></div><div class="jspCap jspCapBottom"></div>
                        </div>
                    </div>
                </ul>
                <div class="b_center" style="color:#a4a0a0;">&lt;&gt;</div>
                <ul class="b_right" style="overflow: auto; padding: 0px; width: 40%;border:1px solid #ccc;">
                    <div class="jspContainer" style="width: 40%; height: 200px;">
                        <div class="jspPane" style="overflow:auto;padding: 0px; width: 318px;height:200px; top: 0px;"><li></li><li></li><li></li><li></li><li></li>
                        </div>
                    </div>
                </ul>
            </div>
            <!--<div class="bottom">
                <input type="button" value="确定" id="login">
            </div>-->
        </div>
    </div>
    <div class="form-actions col-md-10">
        <button class="btn btn-default" type="submit" >提交</button>
        <input type="button" class="btn btn-default" onclick="javascript:window.location.reload()" value="撤销"/>
    </div>
</form>
<script>
    //双向选择
    $(function(){
        rolling();
        $('.b_left').delegate('li','click',function(){
            var maxIndex=$('.b_left li').length-1;
            var index=$('.b_left li').index(this);
            var val=$($('.b_left li')[index]).text();
            var scale=$($('.b_left li')[index]).attr('scale');
            var value=$($('.b_left li')[index]).attr('data-value');
            //$($('.b_left li')[index]).remove();
            var oddid='<li scale='+scale+'></li>';
            var li='<li scale='+scale+' data-value="'+value+'">'+val+'</li>';
            if(index==maxIndex){
                $('.b_left .jspPane').append(oddid);
            }else{
                $($('.b_left li')[index]).before(oddid);
            }
            $('.b_right li:eq('+scale+')').replaceWith(li);
            rolling();
        });
        $('.b_right').delegate('li','click',function(){
            var maxIndex=$('.b_left li').length-1;
            var index=$('.b_right li').index(this);
            $($('.b_right li')[index]).remove();
            var oddid='<li></li>';
            if(index==maxIndex){
                $('.b_right .jspPane').append(oddid);
            }else{
                $($('.b_right li')[index]).before(oddid);
            }
            rolling();
        });
        $('#login').click(function(){
            var temp='';
            var text=$('.b_right li');

            for (var i = 0; i < text.length; i++) {
                var val=$(text[i]).html();
                if(val!=''){
                    temp+=val+','+'\n';
                }
            };
            var temps=temp.substring(0,temp.length-1);
            if (temp=='') {
                alert('请点击左侧进行选择！！');
            }else{
                alert('您的选择为：'+temps);
            }

        });
        function rolling(){
            var bars = '.jspHorizontalBar, .jspVerticalBar';
            $('.box>ul').bind('jsp-initialised',function (event, isScrollable){
                $(this).find(bars).hide();
            }).jScrollPane().hover(
                function(){
                    $(this).find(bars).stop().fadeTo('fast',0.9);
                },
                function(){
                    $(this).find(bars).stop().fadeTo('fast',0);
                }
            );
        }


        var temp = "{{$black_rule}}";
        if(temp){
            var te = temp.split(',');
            var xuanxiang = $('.b_left li');
            $.each(te,function(i,v){
                $.each(xuanxiang,function(a,b){
                    if(v== $(b).attr('data-value')){
                        var li =  li='<li scale='+$(b).attr('scale')+' data-value="'+$(b).attr('data-value')+'">'+$(b).html()+'</li>';
                        $($('.b_right li')[a]).before(li);
                        rolling();
                    }
                })
            })
        }
    });



</script>
<script type="text/javascript">

    $('.clockpicker').clockpicker({
        placement: 'top',
        align: 'left',
        donetext: '确定'

    });
    //只出现确定按钮
    lay('.week1').each(function(i,v){
        var timeStr = '';
        switch (i){
            case 0:
                timeStr = "{{ $week1[1] }}";
                break;
            case 1:
                timeStr = "{{ $week1[2] }}";
                break;
            case 2:
                timeStr = "{{ $week1[3] }}";
                break;
            case 3:
                timeStr = "{{ $week1[4] }}";
                break;
        }
        laydate.render({
            elem: this
            ,type: 'time'
            ,value:timeStr
            ,trigger: 'click'
            ,btns: ['confirm']
        });
    });
    //只出现确定按钮
    lay('.week2').each(function(i,v){
        var timeStr = '';
        switch (i){
            case 0:
                timeStr = "{{ $week2[1] }}";
                break;
            case 1:
                timeStr = "{{ $week2[2] }}";
                break;
            case 2:
                timeStr = "{{ $week2[3] }}";
                break;
            case 3:
                timeStr = "{{ $week2[4] }}";
                break;
        }
        laydate.render({
            elem: this
            ,type: 'time'
            ,value:timeStr
            ,trigger: 'click'
            ,btns: ['confirm']
        });
    });
    //只出现确定按钮
    lay('.week3').each(function(i,v){
        var timeStr = '';
        switch (i){
            case 0:
                timeStr = "{{ $week3[1] }}";
                break;
            case 1:
                timeStr = "{{ $week3[2] }}";
                break;
            case 2:
                timeStr = "{{ $week3[3] }}";
                break;
            case 3:
                timeStr = "{{ $week3[4] }}";
                break;
        }
        laydate.render({
            elem: this
            ,type: 'time'
            ,value:timeStr
            ,trigger: 'click'
            ,btns: ['confirm']
        });
    });
    //只出现确定按钮
    lay('.week4').each(function(i,v){
        var timeStr = '';
        switch (i){
            case 0:
                timeStr = "{{ $week4[1] }}";
                break;
            case 1:
                timeStr = "{{ $week4[2] }}";
                break;
            case 2:
                timeStr = "{{ $week4[3] }}";
                break;
            case 3:
                timeStr = "{{ $week4[4] }}";
                break;
        }
        laydate.render({
            elem: this
            ,type: 'time'
            ,value:timeStr
            ,trigger: 'click'
            ,btns: ['confirm']
        });
    });
    //只出现确定按钮
    lay('.week5').each(function(i,v){
        var timeStr = '';
        switch (i){
            case 0:
                timeStr = "{{ $week5[1] }}";
                break;
            case 1:
                timeStr = "{{ $week5[2] }}";
                break;
            case 2:
                timeStr = "{{ $week5[3] }}";
                break;
            case 3:
                timeStr = "{{ $week5[4] }}";
                break;
        }
        laydate.render({
            elem: this
            ,type: 'time'
            ,value:timeStr
            ,trigger: 'click'
            ,btns: ['confirm']
        });
    });
    //只出现确定按钮
    lay('.week6').each(function(i,v){
        var timeStr = '';
        switch (i){
            case 0:
                timeStr = "{{ $week6[1] }}";
                break;
            case 1:
                timeStr = "{{ $week6[2] }}";
                break;
            case 2:
                timeStr = "{{ $week6[3] }}";
                break;
            case 3:
                timeStr = "{{ $week6[4] }}";
                break;
        }
        laydate.render({
            elem: this
            ,type: 'time'
            ,value:timeStr
            ,trigger: 'click'
            ,btns: ['confirm']
        });
    });
    //只出现确定按钮
    lay('.week0').each(function(i,v){
        var timeStr = '';
        switch (i){
            case 0:
                timeStr = "{{ $week0[1] }}";
                break;
            case 1:
                timeStr = "{{ $week0[2] }}";
                break;
            case 2:
                timeStr = "{{ $week0[3] }}";
                break;
            case 3:
                timeStr = "{{ $week0[4] }}";
                break;
        }
        laydate.render({
            elem: this
            ,type: 'time'
            ,value:timeStr
            ,trigger: 'click'
            ,btns: ['confirm']
        });
    });

    //关闭馆控制工作时间是否必填
    $("input[type='checkbox']").each(function(){
        $(this).click(function(){
            if($(this).prop('checked')){
                var curr = $(this).parents('div')[0];
                if($($(curr).siblings()[1]).find('input').attr('required')){
                    $($(curr).siblings()[1]).find('input').removeAttr('required')
                }
                if($($(curr).siblings()[2]).find('input').attr('required')){
                    $($(curr).siblings()[2]).find('input').removeAttr('required')
                }
            }else{
                var curr = $(this).parents('div')[0];
                if(!$($(curr).siblings()[1]).find('input').attr('required')){
                    $($(curr).siblings()[1]).find('input').attr('required','required')
                }
                if(!$($(curr).siblings()[2]).find('input').attr('required')){
                    $($(curr).siblings()[2]).find('input').attr('required','required')
                }
            }
        })
    })

    //提交
    $("button[type='submit']").click(function(){ //IE兼容有问题
        var form = document.getElementById('form');
        var data = new FormData(form);
        var parm = {};
        var black_rule = [];
        $('.b_right li').each(function(i){
            var val = this.dataset.value;
            if(val){
                black_rule.push(val)
            }
        })
        data.append('black_rule',black_rule)
        data.forEach(function(val, key) {
            parm[key] = val;
        })
        var validate = true;
        $("input[required='required']").each(function(){
            if(!$(this).val()){
                validate = false;
            }
        })
        if(validate){
            swal({
                title: "确定保存操作吗",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "确认",
                showLoaderOnConfirm: true,
                cancelButtonText: "取消",
                preConfirm: function() {
                    return new Promise(function(resolve) {
                        $.ajax({
                            method : 'post',
                            url: '/admin/deposit/time_rules/setting/config',
                            data: parm,
                            success: function (data) {
                                swal.close();
                                swal({
                                    type:"success",
                                    text: "保存成功！"
                                });
                            },
                            error:function(){
                                swal.close();
                                swal({
                                    type: "error",
                                    title: "获取失败！",
                                });
                            }
                        });
                    });
                }
            }).then(function(result) {

            });

        }
        //return false;
    })
           /*$("button[type='submit']").click(function(){
               var form = document.getElementById('form');
               var data = new FormData(form);
               var black_rule = [];
               var validate = true;
               $('.b_right li').each(function(i){
                   var val = this.dataset.value;
                   if(val){
                       black_rule.push(val)
                   }
               })
               data.append('black_rule',black_rule)
               $("input[required='required']").each(function(){
                   if(!$(this).val()){
                       validate = false;
                   }
               })

               if(validate){
                   // var index = layer.load(2, {shade: false})
                   var butt = $("button[type='submit']")
                   butt.attr('disabled',true)
                   if (window.XMLHttpRequest)
                   {// code for IE7+, Firefox, Chrome, Opera, Safari
                       xmlhttp=new XMLHttpRequest();
                   }else
                   {// code for IE6, IE5
                       xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
                   }
                   xmlhttp.onreadystatechange=function()
                   {
                       var res = xmlhttp.responseText;
                       if (xmlhttp.readyState==4 && xmlhttp.status==200)
                       {
                           layer.close(index)
                           layer.msg(JSON.parse(res).message);
                           butt.removeAttr('disabled')
                           if(JSON.parse(res).status == 'successful'){
                               setTimeout(function(){
                                   location.reload()
                               },1500)
                           }
                       }else{
                           // layer.close(index)
                           butt.removeAttr('disabled')
                       }
                   }
                   xmlhttp.open("POST","/admin/deposit/time_rules/setting/config",true);
                   xmlhttp.send(data);
               }
          return false;
           });*/

</script>
