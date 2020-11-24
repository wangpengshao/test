@extends('web.newVote.app')

@section('cssResources')
    {{--<link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/radio_checkbox.css'}}" />--}}
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/signUp.css'}}"/>
@endsection

@section('content')
    <div class="inputShade" id="qrCode">
        <div class="inner">
            <img id="wordsCancel"
                 src="{{asset('wechatWeb/LuckyDraw/common/image/redPack/close2.png')}}"
                 alt="">
            <span class="input_title">长按识别关注公众号</span>
            <div class="toInput ewrShow" id="promptsText">
                <img src="{{$qrCode}}" alt="公众号二维码">
            </div>
        </div>
    </div>
    <form action="" class="signUpFmIn" method="post" enctype="multipart/form-data" id="signupok">
        <div class="actEndTimeArea signUpLine">
            <div class="wrap">
                <div class="signUpIn">
                    <div class="signUpTit">
                        <div class="leaveMesTitIn">上传作品</div>
                        <span class="colorRed upWorkHint">（请在下方完善您的基本信息）</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="actEndTimeArea signUpLine">
            <div class="wrap">
                <div class="signUpIn">
                    <div class="signUpTit borderBt">
                        <div class="leaveMesTitIn">作品信息</div>
                    </div>
                    <div class="signUpForm">
                        <div class="signUpFmGroup">
                            <label class="signUpTit">组<i class="wSpace"></i>别</label>
                            <div class="signUpInp signUpSel">
                                {{--<i class="selDown"></i>--}}
                                {{$groupInfo['title']}}
                            </div>
                        </div>
                        <div class="signUpFmGroup">
                            <label class="signUpTit"><i class="must">*</i>标题</label>
                            <div class="signUpInp"><input type="text" id="zpname" name="title" placeholder="请输入作品标题"/>
                            </div>
                        </div>
                        <div class="signUpFmGroup">
                            <label class="signUpTit"><i class="must">*</i>联系电话</label>
                            <div class="signUpInp">
                                <input type="text" id="telphone" name="phone" placeholder="请输入联系电话" class="phoneInp"
                                       value=""/>
                                <!-- <div class="colorRed errHint"><i class="errHintIcon"></i>手机号码格式有误</div> -->
                            </div>
                        </div>

                        <div class="signUpFmGroup">
                            <label class="signUpTit"><i class="must">*</i>上传封面</label>
                            <div class="up-img-area-con">
                                <ul id="imglist" class="post_imglist"></ul>
                                <div class="up-img-area" id="imglist">
                                    <label class="add-pic">
                                        <div class="addHint">
                                            <i class="addIcon"></i>
                                            <p>点击添加图片</p>
                                        </div>
                                        <input class="uploadImg" type="file" name="cover[]" id="addimage" value=""
                                               multiple="multiple"/>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="signUpFmGroup">
                            <label class="signUpTit"><i class="must">*</i>作品简介</label>
                            <div class="signUpInp"><textarea name="info" id="content" placeholder="请输入作品简介（字数100字左右）"></textarea></div>
                        </div>

                        @foreach($fields as $val)
                            @switch($val['type'])
                                @case(1)
                                <div class="signUpFmGroup">
                                    <label class="signUpTit"><i class="must">@if($val['required_sw'])
                                                *@endif</i>{{$val['name']}}</label>
                                    <div class="signUpInp" style="border-width: 0">
                                        @foreach(explode('|',$val['data']) as $k =>  $v)
                                            <input name="fields{{$val['id']}}" value="{{$k}}" type="radio"
                                                   id="fields{{$val['id']}}{{$k}}"/>
                                            <label for="fields{{$val['id']}}{{$k}}">{{$v}}</label>
                                        @endforeach
                                    </div>
                                </div>
                                @break
                                @case(2)
                                <div class="signUpFmGroup">
                                    <label class="signUpTit"><i class="must">@if($val['required_sw'])
                                                *@endif</i>{{$val['name']}}</label>
                                    <div class="signUpInp" style="border-width: 0">
                                        @foreach(explode('|',$val['data']) as $k => $v)
                                            <input name="fields{{$val['id']}}[]" value="{{$k}}" type="checkbox"
                                                   id="fields{{$val['id']}}{{$k}}"/>
                                            <label for="fields{{$val['id']}}{{$k}}">{{$v}}</label>
                                        @endforeach
                                    </div>
                                </div>
                                @break
                                @case(4)
                                <div class="signUpFmGroup">
                                    <label class="signUpTit"><i class="must">@if($val['required_sw'])
                                                *@endif</i>{{$val['name']}}</label>
                                    <div class="up-img-area-con">
                                        <ul id="imglist" class="post_imglist"></ul>
                                        <div class="up-img-area" id="imglist">
                                            <label class="add-pic">
                                                <div class="addHint">
                                                    <i class="addIcon"></i>
                                                    <p>点击添加图片</p>
                                                </div>
                                                <input class="uploadImg" type="file" name="fields{{$val['id']}}[]"
                                                       id="addimage" value="" multiple="multiple"
                                                       data-num="{{$val['data']}}"/>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @break
                                @default
                                <div class="signUpFmGroup">
                                    <label class="signUpTit"><i class="must">@if($val['required_sw'])
                                                *@endif</i>{{$val['name']}}</label>
                                    <div class="signUpInp">
                                        <input type="text" name="fields{{$val['id']}}"
                                               id="fields{{$val['id']}}"
                                               placeholder="请输入{{$val['name']}}"/>
                                    </div>
                                </div>
                            @endswitch
                        @endforeach

                    </div>
                </div>
            </div>
        </div>
        <div class="wrap">
            <div class="conFirBtn">
                <input type="submit" value="确认提交" class="conFirBtnInp"/>
            </div>
        </div>
        {{csrf_field()}}
    </form>
@endsection
@section('jsResources')
    <script type="text/javascript" src="{{$templatePath.'/js/index.js'}}"></script>
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
                    let viewImg = e.parents('div .up-img-area').prev('ul');

                    e.localResizeIMG({
                        width: 480,
                        quality: 0.9,
                        success: function (result) {
                            let status = true;
                            let name = e.attr('name');
                            if (result.height > 1600) {
                                status = false;
                                toast("照片最大高度不超过1600像素");
                            }
                            if (viewImg.find("li").length > num - 1) {
                                status = false;
                                toast("此栏目只能上传" + num + "张图片");
                            }
                            if (status) {
                                viewImg.append('<li style=""><span class="pic_time"><span class="p_img"></span><em>50%</em></span></li>');
                                viewImg.find("li:last-child").html('<span class="del" onclick="deleted(this)"></span><img class="wh60" src="' + result.base64 + '"/>' +
                                    '<input type="hidden"  name="' + name + '" value="' + result.clearBase64 + '">');
                            }
                        }
                    });
                })
            }

            //点击提交表单
            const subscribe = "{{$fansInfo['subscribe']}}";
            let fieldsJson = @json($fields)

            $('form').submit(function (e) {
                if (subscribe !== "1") {
                    toast('请先关注公众号才可进行评论！');
                    showQrCode();
                    return false;
                }

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
                        console.log(item.name)
                        console.log(key)
                        console.log(item.value)
                        if (item.name === key && item.value !== "") {
                            status = 1;
                        }
                    })
                    if (status !== 1) {
                        toast('抱歉,尚有必填项未填写!');
                        return false;
                    }
                }

                let telreg = /^1[3|4|5|7|8|9][0-9]\d{8}$|^\d{8}$/;
                var tel = $("#telphone").val();
                if (!telreg.test(tel)) {
                    $("#telphone").focus()
                    toast('请输入正确的手机号!')
                    return false
                }
            });

        });

        function deleted(e) {
            $(e).parent('li').remove();
            $("#upload_image").show();
        }

        let s_time = "{{$config['s_time']}}"
        s_time = new Date(s_time.replace(/\-/g, "/")).getTime();
        let e_time = "{{$config['e_time']}}"
        e_time = new Date(e_time.replace(/\-/g, "/")).getTime();
        let now = new Date().getTime();
        let url = "{!! $urlArr['indexUrl'] !!}";

        if (s_time > now) {
            let index = layer.open({
                content: "报名还未开始，请 {{$config['s_time']}} 后再来！"
                , btn: '我知道了'
                , yes: function () {
                    layer.close(index)
                    window.location.href = url
                }
            });
        } else if (e_time < now) {
            var index = layer.open({
                content: '对不起！报名已经结束！'
                , btn: '我知道了'
                , yes: function () {
                    layer.close(index)
                    window.location.href = url
                }
            });
        }
    </script>
@endsection
