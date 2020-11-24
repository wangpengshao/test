@extends('web.vote.app')
@section('content')
    <div class="apply">
        <p>报名处</p>
        <div class="blank10"></div>
        <form accept-charset="utf-8" method="post">
            <dl class="clearfix">
                <dt>组别:</dt>
                <dd>{{$groupInfo['title']}}</dd>
            </dl>
            <dl class="clearfix">
                <dt>标题:</dt>
                <dd><input type="text" class="input_txt" name="title" value="ceshi"></dd>
            </dl>
            <dl class="clearfix">
                <dt>手机号码:</dt>
                <dd><input type="number" class="input_txt" name="phone" placeholder="请输入您的真实手机号" value="137"></dd>
            </dl>
            <dl class="upload clearfix">
                <dt>上传封面</dt>
                <dd class="upload_area clearfix">
                    <ul id="imglist" class="post_imglist"></ul>
                    <div class="upload_btn">
                        <input class="uploadImg" name="cover[]" value="图片上传" accept="image/*" type="file">
                    </div>
                </dd>
            </dl>
            <dl class="clearfix">
                <dt>简介 :</dt>
                <dd><textarea class="textarea" name="info" id="content"></textarea></dd>
            </dl>
            @foreach($fields as $val)
                @switch($val['type'])
                    @case(1)
                    <dl class="clearfix">
                        <dt>{{$val['name']}}:</dt>
                        <dd>
                            @foreach(explode('|',$val['data']) as $k =>  $v)
                                <label>
                                    <input name="fields{{$val['id']}}" value="{{$k}}" type="radio"/>{{$v}}
                                </label>
                            @endforeach
                        </dd>
                    </dl>
                    @break
                    @case(2)
                    <dl class="clearfix">
                        <dt>{{$val['name']}}:</dt>
                        <dd>
                            @foreach(explode('|',$val['data']) as $k => $v)
                                <label>
                                    <input name="fields{{$val['id']}}[]" value="{{$k}}" type="checkbox"/>{{$v}}
                                </label>
                            @endforeach
                        </dd>
                    </dl>
                    @break
                    @case(4)
                    <dl class=" upload clearfix">
                        <dt>{{$val['name']}}:</dt>
                        <dd class="upload_area clearfix">
                            <ul class="post_imglist imglist"></ul>
                            <div class="upload_btn">
                                <input class="uploadImg" accept="image/*" type="file" name="fields{{$val['id']}}[]"
                                       data-num="{{$val['data']}}">
                            </div>
                        </dd>
                    </dl>
                    @break
                    @default
                    <dl class="clearfix">
                        <dt>{{$val['name']}}:</dt>
                        <dd>
                            <input type="text" class="input_txt" name="fields{{$val['id']}}" id="fields{{$val['id']}}">
                        </dd>
                    </dl>
                @endswitch
            @endforeach
            <div class="btn_box">
                <input type="submit" class="button" value="确认报名">
            </div>
            <div class="blank20"></div>
            <div class="blank20"></div>
            <div class="blank20"></div>
            {{csrf_field()}}
        </form>

    </div>
    <section>
        @if($config['s_time'] > date('Y-m-d H:i:s'))
            <div class="pop" style="display:block">
                <div class="mengceng"></div>
                <div class="pop_up">
                    <p class="tit_p">报名还未开始</p>
                    <p class="tit_txt">请{{$config['s_time']}}后再来！</p>

                </div>
            </div>
        @elseif($config['e_time'] < date('Y-m-d H:i:s'))
            <div class="pop" style="display:block">
                <div class="mengceng"></div>
                <div class="pop_up">
                    <p class="tit_p">对不起！报名已经结束！</p>
                </div>
            </div>
        @endif
    </section>

@endsection

@section('jsResources')
    <script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/vote/js/localResizeIMG2.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/vote/js/mobileBUGFix.mini.js')}}"></script>
    <script type="text/javascript">
        @if ($errors->any())
        @foreach ($errors->all() as $error)
        alert("{{ $error }}")
        @endforeach
        @endif

        /* 图片上传方法 */
        $(function () {
            let imgLength = $('.uploadImg').length;
            if (imgLength > 0) {
                let imgs = $('.uploadImg');
                $.each(imgs, function (index, item) {
                    let e = $(item);
                    let num = (e.data('num')) ? e.data('num') : 1;
                    let viewImg = e.parent().prev('ul');

                    e.localResizeIMG({
                        width: 480,
                        quality: 0.9,
                        success: function (result) {
                            let status = true;
                            let name = e.attr('name');
                            if (result.height > 1600) {
                                status = false;
                                alert("照片最大高度不超过1600像素");
                            }
                            if (viewImg.find("li").length > num - 1) {
                                status = false;
                                alert("此栏目只能上传" + num + "张图片");
                            }
                            if (status) {
                                viewImg.append('<li><span class="pic_time"><span class="p_img"></span><em>50%</em></span></li>');
                                viewImg.find("li:last-child").html('<span class="del"></span><img class="wh60" src="' + result.base64 + '"/>' +
                                    '<input type="hidden"  name="' + name + '" value="' + result.clearBase64 + '">');
                                // '<input type="hidden"  name="' + name + '[]" value="' + result.clearBase64 + '">');
                                // $("*[name='name']").val()
                                console.log($(this));
                                console.log(e.attr('name'));
                                $(".del").on("click", function () {
                                    $(this).parent('li').remove();
                                    $("#upload_image").show();
                                });
                            }
                        }
                    });
                })
            }
            //点击提交表单
            let fieldsJson = @json($fields)

            $('form').submit(function (e) {
                //检查必填项 title phone  cover info
                let formData = $(this).serializeArray();
                let requiredArr = [
                    {name: "title", type: 0},
                    {name: "phone", type: 0},
                    {name: "cover", type: 4},
                    {name: "info", type: 0},
                ];
                $.each(fieldsJson, function (index, item) {
                    if (item['required_sw'] == 1) {
                        let name = 'fields' + item['id'];
                        requiredArr.push({name: name, type: item['type']})
                    }
                })
                console.log(requiredArr);
                for (let i = 0; i < requiredArr.length; i++) {
                    let status = 0;
                    let key = requiredArr[i].name;
                    let type = requiredArr[i].type;
                    if (type == 2 || type == 4) {
                        key += '[]';
                    }
                    $.each(formData, function (index, item) {
                        if (item.name === key && item.value !== "") {
                            status = 1;
                        }
                    })
                    if (status !== 1) {
                        toast('抱歉,尚有必填项未填写!')
                        console.log(key);
                        return false;
                    }
                }
            });


        });
    </script>
@endsection
