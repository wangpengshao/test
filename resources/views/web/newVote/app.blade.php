<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="HandheldFriendly" content="true">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{$config['title']}}</title>
    <link rel="stylesheet" href="{{asset('wechatWeb/vote/css/common.css')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/vote/css/loading.css')}}">
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/base.css'}}" />
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/conn.css'}}" />
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/layer_mobile/need/layer.css'}}" />
    @yield('cssResources')
</head>
<body>

<!-- 菜单区域 start-->
<header class="idxHead">
    <div class="wrap idxHeadIn">
        <a href="{{route('Vote::index2', ['token'=>$config['token'], 'a_id'=>$config['id']])}}" class="logoArea"></a>
        <nav class="idxNav">
            <div class="pcNav">
                <ul class="idxNavUl">
                    <li class="idxNavLi @if(request()->route()->named('Vote::index2')) idxNavCur @endif"><a href="{{$urlArr['indexUrl']}}" class="idxNavLink">首页</a></li>
                    <li class="idxNavLi @if(request()->route()->named('Vote::rank2')) idxNavCur @endif"><a href="{{$urlArr['rankUrl']}}" class="idxNavLink">排名</a></li>
                    <li class="idxNavLi @if(request()->route()->named('Vote::signUp2')) idxNavCur @endif"><a href="{{$urlArr['signUpUrl']}}" class="idxNavLink">报名</a></li>
                    @if($groupList->count() > 1)
                        <li class="idxNavLi" id="des1"><a href="javascript:void(0)"  class="idxNavLink">切换分组</a>
                            <ul class="group">
                                @foreach($groupList as $val)
                                    <li data-value="{{$val->id}}" class="switch_group">{{$val->title}}</li>
                                @endforeach
                            </ul>
                        </li>
                    @endif
                    <li class="idxNavLi @if(request()->route()->named('Vote::explain2')) idxNavCur @endif"><a href="{{$urlArr['explainUrl']}}" class="idxNavLink">活动详情</a></li>
                </ul>
                <a href="javascritp:;" class="searchBtn"  ></a>
            </div>
            <div class="pcInpArea">
                @if(request()->route()->named('Vote::index2'))
                    <div class="wrap pcInpAreaIn">
                        <i class="arrowUp"></i>
                        <div class="pcForm">
                            <div class="pcSearchInp">
                                <input type="text" id="pcSearch" placeholder="请输入作品标题或编号" />
                                <a href="javascript:;" class="pcCloseInp"></a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </nav>
        <div class="mbHeadArea">
            @if(request()->route()->named('Vote::index2'))
                <div class="mbSearchBtn" ></div>
                <div class="mbSearchInpArea">
                    <i class="arrowUp"></i>
                    <div class="mbForm">
                        <div class="mbSearchInp">
                            <input type="text" id="mobileSearch" placeholder="请输入作品标题或编号" />
                        </div>
                    </div>
                </div>
            @endif
            <div class="icoNav">
                <div class="icoNavIn">
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
            <div class="mbMenuArea">
                <i class="arrowUp"></i>
                <div class="mbMenuAreaIn">
                    <ul>
                        <li class="@if(request()->route()->named('Vote::index2')) menuCur @endif"><a href="{{$urlArr['indexUrl']}}">首页</a></li>
                        <li class="@if(request()->route()->named('Vote::rank2')) menuCur @endif "><a href="{{$urlArr['rankUrl']}}">排名</a></li>
                        <li class="@if(request()->route()->named('Vote::signUp2')) menuCur @endif"><a href="{{$urlArr['signUpUrl']}}">报名</a></li>
                        @if($groupList->count() > 1)
                            <li id="des"><a href="javascript:void(0)">切换分组</a>
                                <ul class="group">
                                    @foreach($groupList as $val)
                                        <li data-value="{{$val->id}}" class="switch_group">{{$val->title}}</li>
                                    @endforeach
                                </ul>
                            </li>
                        @endif
                        <li class="@if(request()->route()->named('Vote::explain2')) menuCur @endif"><a href="{{$urlArr['explainUrl']}}">活动详情</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- 菜单区域 end-->

<!-- 内容区域 start-->
@section('content')
@show
<!-- 内容区域 end-->
@section('footer')
    <!-- 页面底部 start-->
    <footer class="idxFootArea">
        <div class="wrap ">
            <div class="idxFootIn">

            </div>
        </div>
    </footer>
    <!-- 页面底部 end-->
@show
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script type="text/javascript" src="{{asset('wechatWeb/vote/js/jQuery-2.1.4.min.js')}}"></script>
<script type="text/javascript" src="{{$templatePath.'/layer_mobile/layer.js'}}"></script>
<script type="text/javascript" src="{{$templatePath.'/js/common.js'}}"></script>
@section('jsResources')
@show
<script>
    <!-- JS-SDK -->
    wx.config({!! $app->jssdk->buildConfig(['checkJsApi', 'updateAppMessageShareData', 'updateTimelineShareData'], false) !!});
    wx.ready(function() {
        let title = "{{$config['share_title']}}";
        let desc = "{{$config['share_desc']}}";
        let imgUrl = "{!! $config['share_img'] !!}";
        let link = window.location.href;
        if(title && desc && imgUrl){
            wx.updateAppMessageShareData({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: link,
                imgUrl: imgUrl, // 分享图标
                success: function () {
                    // 用户确认分享后执行的回调函数
                },
                cancel: function () {
                    // 用户取消分享后执行的回调函数
                }
            });

            //分享给朋友
            wx.updateTimelineShareData({
                title: title, // 分享标题
                desc: desc, // 分享描述
                link: link,
                imgUrl: imgUrl, // 分享图标
                success: function () {
                },
                cancel: function () {
                }
            });
        }
    });
</script>
<script>
    $('.group').hide()
    $('#des').click(function (event) {
        $('.group').fadeToggle();
        return false;
    });
    $('#des1').click(function (event) {
        $('.group').fadeToggle();
        return false;
    });
    $('.switch_group').click(function (event) {
        let val =$(this).data('value');
        if (val === '') return false;
        let url = window.location.href;
        url = changeURLArg(url, 'g_id', val)
        window.location.href = url;
    })
    function changeURLArg(url, arg, arg_val) {
        let pattern = arg + '=([^&]*)';
        let replaceText = arg + '=' + arg_val;
        if (url.match(pattern)) {
            let tmp = '/(' + arg + '=)([^&]*)/gi';
            tmp = url.replace(eval(tmp), replaceText);
            return tmp;
        } else {
            if (url.match('[\?]')) {
                return url + '&' + replaceText;
            } else {
                return url + '?' + replaceText;
            }
        }
        return url + '\n' + arg + '\n' + arg_val;
    }

    function showQrCode(){
        $('#qrCode').show();
    }

    $('#wordsCancel').click(function(){
        $('#qrCode').hide();
    })
</script>
</body>
</html>