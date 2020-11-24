<!doctype html>
<html class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="">
    <meta name="keywords" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no, target-densitydpi=device-dpi"/>
    <title>领取红包</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="Cache-Control" content="no-siteapp"/>
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Amaze UI"/>
    <meta name="msapplication-TileColor" content="#0e90d2">
    <link rel="stylesheet" href="https://cdn.staticfile.org/amazeui/2.7.2/css/amazeui.min.css">
    <link rel="stylesheet" href="{{asset('common/css/loading.css')}}">
    <style>
        html {
            font-size: 10px;
        }

        html, body {
            background-color: #f0eff4;
        }

        body {
            padding-bottom: 0;
            margin: 0;
            padding-top: 49px;
        }

        * {
            padding: 0;
            margin: 0;
        }

        header {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 999;
            width: 100%;
            height: 49px;
            background-color: #333;
            color: #fff;
        }

        header .back {
            position: absolute;
            top: 0;
            left: 0;
            display: inline-block;
            padding-left: 5px;
            font-size: 30px;
        }

        header p {
            margin: 0;
            line-height: 49px;
            font-size: 16px;
            text-align: center;
        }

        .register {
            padding: 8px 6px;
            font-size: 14px;
        }

        .res-item {
            position: relative;
            width: 100%;
            border-radius: 4px;
            margin-bottom: 8px;
            background-color: #fff;
        }

        .res-icon {
            position: absolute;
            left: 8px;
            top: 5px;
            z-index: 100;
            display: inline-block;
            font-size: 18px;
            color: #9c9c9c;
        }

        .res-item .input-item {
            display: inline-block;
            width: 100%;
            padding-left: 31px;
            height: 40px;
            border: none;
            font-size: inherit;
        }

        .res-item .input-item:focus {
            outline-offset: 0;
            outline: -webkit-focus-ring-color auto -2px;
            background-color: #fefffe;
            border: 1px solid #e21945;
            outline: 0;
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 5px rgba(226, 25, 69, .3);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, .075), 0 0 5px rgba(226, 25, 69, 0.3);
        }

        .res-item .input-item:focus + .res-icon {
            color: #e21945;
        }

        .yanzhengma {
            position: absolute;
            right: 10px;
            top: 5px;
            z-index: 100;
            display: inline-block;
            padding: 0.08rem 0.2rem;
            font-size: 14px;
            border: none;
            background-color: #e21945;
            color: #fff;
            border-radius: 8px;
        }

        .yanzhengma:disabled {
            background-color: #ddd;
        }

        .res-btn {
            margin-top: 10px;
            padding: 0 5px;
        }

        .res-btn button {
            background-color: #e21945;
            font-size: 14px;
            color: #fff;
            border-radius: 8px;
        }

        .res-btn button:focus {
            color: #fff;
        }
    </style>
</head>
<body>
<!--[if lte IE 9]>
<p class="browsehappy">你正在使用<strong>过时</strong>的浏览器，Amaze UI 暂不支持。 请
    <a href="http://browsehappy.com/" target="_blank">升级浏览器</a>以获得更好的体验！</p>
<![endif]-->
<header><p>手机验证</p></header>
<div class="loadingSw" style="display: none">
    {{--<div class="loadingSw">--}}
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
<div class="register">
    <div class="res-item">
        <input type="tel" placeholder="手机号" class="input-item mobile">
        <i class="res-icon am-icon-phone"></i>
    </div>
    <div class="res-item">
        <input type="text" placeholder="验证码" class="input-item yanzheng">
        <i class="res-icon am-icon-mobile"></i>
        <button type="button" class="yanzhengma">发送验证码</button>
    </div>
    <div class="res-btn">
        <button type="button" id="res-btn" class="am-btn am-btn-block">确认</button>
    </div>
</div>
<script type="text/javascript" src="{{asset('wechatWeb/collectCard/js/jquery-1.11.1.min.js')}}"></script>
<script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
<script>
    (function (win, doc) {
        let docEl = doc.documentElement,
            design = 750;
        let resizeEvt = "orientationchange" in win ? "orientationchange" : "resize";
        let recale = function () {
            let clientWidth = docEl.clientWidth;
            if (!clientWidth) return;
            docEl.style.fontSize = 100 * (clientWidth / design) + "px";
        }
        if (!doc.addEventListener) return;
        win.addEventListener(resizeEvt, recale, false);
        docEl.addEventListener("DOMContentLoaded", recale, false);
        recale();
    })(window, document)

    function showLoading(text = null) {
        if (text) {
            $('.loadingText').text(text);
        }
        $('.loadingSw').show();
    }

    function hideLoading() {
        $('.loadingText').text('');
        $('.loadingSw').hide();
    }

    let times = 60;
    const sendMes = "{!! $sendMesUrl !!}";
    const csrf_token = "{{csrf_token()}}";
    const openid = "{{$fansInfo['openid']}}";

    function roof() {
        if (times == 0) {
            $('.yanzhengma').text('发送验证码(' + times + 's)');
            $('.yanzhengma').prop('disabled', false);
            $('.yanzhengma').text('发送验证码');
            times = 60;
            return
        }
        $('.yanzhengma').text('发送验证码(' + times + 's)');
        times--;
        setTimeout(roof, 1000);
    }

    function checkPhone(phone) {
        if (!(/^1[3|4|5|7|8]\d{9}$/.test(phone))) {
            return false;
        }
        return true;
    }

    $('.yanzhengma').on('click', function () {
        let mobile = $('.mobile').val();
        if (!mobile) {
            $('.mobile').focus();
            document.querySelector('.mobile').placeholder = '请填写手机号码';
            return
        }
        if (!checkPhone(mobile)) {
            $('.mobile').focus();
            toast('请输入正确的手机号码！');
            return;
        }
        $(this).prop('disabled', true);
        roof();
        $.ajax({
            type: 'POST',
            url: sendMes,
            dataType: "json",
            data: {"phone": mobile, "openid": openid},
            headers: {
                'X-CSRF-TOKEN': csrf_token
            },
            success: function (response) {
                hideLoading();
                toast({time: 4000, content: response.message})
            },
            error: function (e) {
            }
        });
    });
    $('#res-btn').on('click', function () {
        let yanzheng = $('.yanzheng').val();
        let mobile = $('.mobile').val();
        if (!mobile) {
            $('.mobile').focus();
            document.querySelector('.mobile').placeholder = '请填写手机号码';
            return
        }
        if (!yanzheng) {
            $('.yanzheng').focus();
            document.querySelector('.yanzheng').placeholder = '请填写验证码';
            return
        }
        $(this).prop('disabled', true);
        showLoading('领取中')
        $.ajax({
            type: 'POST',
            url: window.location.href,
            dataType: "json",
            data: {"phone": mobile, "phoneCode": yanzheng},
            headers: {
                'X-CSRF-TOKEN': csrf_token
            },
            success: function (response) {
                hideLoading();
                if (response.status == false) {
                    $(this).attr("disabled",false);
                    toast({time: 4000, content: response.message})
                    return false;
                }
                if (response.status == true) {
                    alert(response.data.message).then(function () {
                        window.location.href = response.data.redirect;
                    })
                }
            },
            error: function (e) {
            }
        });
        // alert('注册成功');
    })
</script>

</body>
</html>
