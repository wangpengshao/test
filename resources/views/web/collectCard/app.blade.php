<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <!-- 视口标签 -->
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no"/>
    <!-- 删除苹果默认的工具栏和菜单栏 -->
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <!-- 设置苹果工具栏颜色 -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black"/>
    <!-- 忽略页面中的数字识别为电话，忽略email识别 -->
    <meta name="format-detection" content="telphone=no, email=no"/>
    <!-- 启用360浏览器的极速模式(webkit) -->
    <meta name="renderer" content="webkit">
    <!-- 避免IE使用兼容模式 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- 针对手持设备优化，主要是针对一些老的不识别viewport的浏览器，比如黑莓 -->
    <meta name="HandheldFriendly" content="true">
    <!-- 微软的老式浏览器 -->
    <meta name="MobileOptimized" content="320">
    <!-- uc强制竖屏 -->
    <meta name="screen-orientation" content="portrait">
    <!-- QQ强制竖屏 -->
    <meta name="x5-orientation" content="portrait">
    <!-- UC应用模式 -->
    <meta name="browsermode" content="application">
    <!-- QQ应用模式 -->
    <meta name="x5-page-mode" content="app">
    <!-- windows phone 点击无高光 -->
    <meta name="msapplication-tap-highlight" content="no">
    <!-- 适应移动端end -->
    <link rel="stylesheet" href="{{asset('wechatWeb/collectCard/css/base.css')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/collectCard/css/xt.css')}}">
    <link rel="stylesheet" href="{{asset('common/css/loading.css')}}">
    @yield('cssResources')
    <script type="text/javascript" src="{{asset('wechatWeb/collectCard/js/common.js')}}"></script>
    <title>{{$config['title']}}</title>
</head>
{{--<body class="bgWhite">--}}
<body class=" @yield('bodyClass')">

<div class="loadingSw" style="display: none">
    <div class="spinner">
        <div class="spinner-container container1">
            <div class="circle1"></div>
            <div class="circle2"></div>
            <div class="circle3"></div>
            <div class="circle4"></div>
        </div>
        <div class="spinner-container container2">
            <div class="circle1"></div>
            <div class="circle2"></div>
            <div class="circle3"></div>
            <div class="circle4"></div>
        </div>
        <div class="spinner-container container3">
            <div class="circle1"></div>
            <div class="circle2"></div>
            <div class="circle3"></div>
            <div class="circle4"></div>
        </div>
        <div class="loadingText">处理中</div>
    </div>
</div>

@if(request()->route()->action['as'] !== 'CollectCard::index')
    <div class="goHomeBtn">
        <a href="{{$indexUrl}}" class="actRuleBtnIn">回到首页</a>
    </div>
@endif

@yield('content')
</body>
<script type="text/javascript" src="{{asset('wechatWeb/collectCard/js/jquery-1.11.1.min.js')}}"></script>
@yield('jsResources')
</html>
