<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> @yield('title')</title>
    @section('cssResources')
        <link rel="stylesheet" href="{{asset('wechatWeb/LuckyDraw/common/css/common_mobile-version=1.0.0.css')}}">
        <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/deposit/wap/css/base.css')}}" />
        <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/deposit/wap/css/index.css')}}" />
        <link rel="stylesheet" href="{{asset('wechatWeb/deposit/wap/css/iosSelect.css')}}">
@show
<!-- 移动端适配 -->
    <script>
        var html = document.querySelector('html');
        changeRem();
        window.addEventListener('resize', changeRem);

        function changeRem() {
            var width = html.getBoundingClientRect().width;
            html.style.fontSize = width / 10 + 'px';
        }
    </script>
</head>

<body>
{{--加载中遮罩层--}}
<div class="inputShade hidden">
    <div id="loading">
        <div class="demo3"></div>
        <div class="demo3"></div>
        <div class="demo3"></div>
        <div class="demo3"></div>
        <div class="demo3"></div>
        <div class="demo3"></div>
        <div class="demo3"></div>
        <div class="demo3"></div>
        <div class="loadingText">处理中..</div>
    </div>
</div>


{{--提示语--}}
<div class="inputShade hidden" id="prompts">
    <div class="inner">
        <img id="wordsCancel" class="cancelImg" src="{{asset('wechatWeb/LuckyDraw/common/image/redPack/close2.png')}}"
             alt="">
        <span class="input_title">提示</span>
        <div class="toInput inputTips" id="promptsText"></div>
        <span class="sureGet" id="wordsSure">确认</span>
    </div>
</div>


{{--中奖提示--}}
{{--<div id="mask">--}}
    {{--<div class="blin"></div>--}}
    {{--<div class="caidai"></div>--}}
    {{--<div class="winning">--}}
        {{--<div class="red-head"></div>--}}
        {{--<div class="red-body"></div>--}}
        {{--<div id="card">--}}
            {{--<a href="" target="_self" class="win"></a>--}}
        {{--</div>--}}
        {{--<a href="" target="_self" class="btn"></a>--}}
        {{--<span id="close"></span>--}}
    {{--</div>--}}
{{--</div>--}}
<div id="mask">
    <div class="blin"></div>
    <div class="caidai"></div>
    <div class="winning">
        <div class="red-head"></div>
        <div class="red-body" style="background-image: url('/wechatWeb/IntegralExchange/common/image/bottom_03.png');"></div>
        <div id="card">
            <a href="" target="_self" class="win">
                <img  class="prizeImg" alt="" >
            </a>
        </div>
        <span class="prizeTips"></span>
        <a href="" target="_self" class="btn" id="myRecord" style="background-image: url('/wechatWeb/IntegralExchange/common/image/button.png');"></a>
        <span id="close"></span>
    </div>
</div>
{{--不中奖提示--}}
<div id="noMask">
    <div class="winning">
        <div class="red-head"></div>
        <div class="red-body no-red-body"></div>
        <div id="noCard">
            <a href="" target="_self" class="noWin"></a>
        </div>
        <span id="noClose"></span>
    </div>
</div>
<!-- 收集信息弹框 -->
{{--'1' => '真实姓名', '2' => '手机号码', '3' => '详细地址'--}}
<div class="inputShade hidden" id="gatherDom">
    <div class="inner" id="infoTable">
        {{--<img id="infoClose" class="cancelImg" src="{{asset('wechatWeb/LuckyDraw/common/image/redPack/close2.png')}}"--}}
             {{--alt="">--}}
        <img class="titImg" src="{{asset('wechatWeb/LuckyDraw/common/image/rule/star2.png')}}">
        <span class="input_title">请输入信息</span>
        <div class="toInput">
            <div class="infoItem hidden" id="gather1">
                <div class="name">
                    <img class="nameIcon" src="{{asset('wechatWeb/LuckyDraw/common/image/tips/name.png')}}">
                </div>
                <input  type="text" placeholder="请输入真实姓名"  value="">
            </div>
            <div class="infoItem hidden" id="gather2">
                <div class="name">
                    <img class="nameIcon" src="{{asset('wechatWeb/LuckyDraw/common/image/tips/phone.png')}}">
                </div>
                <input type="number" data-type="gather2" placeholder="请输入手机号码" pattern="[0-9]{16}" value="">
                <img class="infoTips hidden yesTips" src="{{asset('wechatWeb/LuckyDraw/common/image/tips/correct.png')}}">
                <img class="infoTips hidden noTips" src="{{asset('wechatWeb/LuckyDraw/common/image/tips/error.png')}}">

            </div>

            <div class="infoItem hidden" id="gather4">
                <div class="name">
                    <img class="nameIcon" src="{{asset('wechatWeb/LuckyDraw/common/image/tips/card.png')}}">
                </div>
                <input type="text" data-type="gather4" placeholder="请输入详细地址" value="">
                <img class="infoTips hidden noTips" src="{{asset('wechatWeb/LuckyDraw/common/image/tips/error.png')}}">
                <img class="infoTips hidden yesTips" src="{{asset('wechatWeb/LuckyDraw/common/image/tips/correct.png')}}">
            </div>
        </div>
        <span id="sureGet" class="sureGet gatherSubmit">确定兑换</span>
    </div>
</div>

@yield('content')


@section('jsResources')
    <script type="text/javascript" charset="utf-8"
            src="https://cdn.staticfile.org/jquery/3.3.1/jquery.min.js"></script>
    {{--<script type="text/javascript" charset="utf-8"--}}
    {{--src="{{asset('wechatWeb/LuckyDraw/common/js/jquery.min.js')}}"></script>--}}
    <script type="text/javascript" charset="utf-8"
            src="{{asset('wechatWeb/LuckyDraw/common/js/h5_game_common-version=1.0.0.js')}}"></script>
@show
</body>
</html>
