<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{$wxuser->name}}预约签到二维码</title>
    <base target="content-frame">
    <style>
        html {
            font: 100%/1.3 Verdana, Helvetica, Arial, sans-serif;
        }
        body {
            font: 70%/1.3 Verdana, Helvetica, Arial, sans-serif;
        }

        h1 {
            font: bold 2em Arial, sans-serif;
        }

        h2 {
            font: bold 1.5em Arial, sans-serif;
        }

        h3 {
            font: bold 1.25em Arial, sans-serif;
        }

        h4 {
            font: bold 1.1em Arial, sans-serif;
        }

        /* Default resetting */
        html, body, form, fieldset, legend, dt, dd {
            margin: 0;
            padding: 0;
        }

        h1, h2, h3, h4, h5, h6, p, ul, ol, dl {
            margin: 0 0 1em;
            padding: 0;
        }

        h1, h2, h3, h4, h5, h6 {
            margin-bottom: 0.5em;
        }

        h2 {
            margin-top: 20px;
        }

        pre {
            font-size: 1.5em;
        }

        li, dd {
            margin-left: 1.5em;
        }

        img {
            border: none;
            vertical-align: middle;
        }

        /* Basic element styles */
        a {
            color: #000;
        }

        a:hover {
            text-decoration: underline;
        }

        html {
            color: #000;
            background: gold;
            min-height: 100%;
        }

        body {
            margin-bottom: 30px;
        }

        ul {
            margin: 10px 0;
        }

        /* Structure */
        .container {
            width: 560px;
            min-height: 600px;
            background: #fff;
            border: 1px solid #ccc;
            border-top: none;
            /*margin: 0 auto;*/
            padding: 20px;
            -moz-border-radius: 10px;
            -webkit-border-radius: 10px;
            border-radius: 10px;
            -moz-box-shadow: 1px 1px 10px #000;
            -webkit-box-shadow: 1px 1px 5px #000;
            box-shadow: 1px 1px 10px #000;
            position:absolute;
            top:50%;
            margin-top:-325px;
            left: 50%;
            margin-left: -280px;

        }

        @media screen and (max-width: 320px) {
            #container {
                width: 280px;
                padding: 10px;
            }
        }

        video {
            display: block;
            margin-bottom: 10px;
        }

        /* Fullscreen */
        html:-moz-full-screen {
            background: #5e616b;
        }

        html:-webkit-full-screen {
            background:  #5e616b;
        }

        html:-ms-fullscreen {
            background:  #5e616b;
        }

        body:-ms-fullscreen {
            overflow: auto; /* fix for IE11 scrollbar */
        }

        html:fullscreen {
            background: #5e616b;
        }
    </style>
</head>

<body>

<div class="container">
    <h1 style="text-align: center">签到二维码展示</h1>

    <section class="main-content">
        <p>  <button id="view-fullscreen">全屏</button>
            <button id="cancel-fullscreen">取消</button></p>

        <p>当前模式: <b id="fullscreen-state">浏览器模式</b></p>
        <!--<p>The background should also turn red when in fullscreen.</p>-->

        <!--<script>-->
        <!--document.addEventListener("keydown", function (evt) {-->
        <!--console.log("keydown. You pressed the " + evt.keyCode + " key")-->
        <!--}, false);-->
        <!--</script>-->
    </section>
    <div id="qrcode" style="text-align: center"></div>

</div>

<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/jquery-2.1.3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/jquery.qrcode.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/lib/layer-mobile/layer.js')}}"></script>
<link rel="stylesheet" href="{{asset('wechatWeb/seatBooking/lib/layer-mobile/need/layer.css')}}">
<!--<script src="js/base.js"></script>-->
<script type="text/javascript">
    (function () {
        var viewFullScreen = document.getElementById("view-fullscreen");
        if (viewFullScreen) {
            viewFullScreen.addEventListener("click", function () {
                var docElm = document.documentElement;
                if (docElm.requestFullscreen) {
                    docElm.requestFullscreen();
                }
                else if (docElm.msRequestFullscreen) {
                    docElm = document.body; //overwrite the element (for IE)
                    docElm.msRequestFullscreen();
                }
                else if (docElm.mozRequestFullScreen) {
                    docElm.mozRequestFullScreen();
                }
                else if (docElm.webkitRequestFullScreen) {
                    docElm.webkitRequestFullScreen();
                }
            }, false);
        }

        var cancelFullScreen = document.getElementById("cancel-fullscreen");
        if (cancelFullScreen) {
            cancelFullScreen.addEventListener("click", function () {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
                else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                }
                else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                }
                else if (document.webkitCancelFullScreen) {
                    document.webkitCancelFullScreen();
                }
            }, false);
        }


        var fullscreenState = document.getElementById("fullscreen-state");
        if (fullscreenState) {
            document.addEventListener("fullscreenchange", function () {
                fullscreenState.innerHTML = (document.fullscreenElement)? "全屏模式" : "浏览器模式";
            }, false);

            document.addEventListener("msfullscreenchange", function () {
                fullscreenState.innerHTML = (document.msFullscreenElement)? "全屏模式" : "浏览器模式";
            }, false);

            document.addEventListener("mozfullscreenchange", function () {
                fullscreenState.innerHTML = (document.mozFullScreen)? "全屏模式" : "浏览器模式";
            }, false);

            document.addEventListener("webkitfullscreenchange", function () {
                fullscreenState.innerHTML = (document.webkitIsFullScreen)? "全屏模式" : "浏览器模式";
            }, false);
        }

        var Url="{!! $url !!}";

        setInterval(function () {
            createQrcode();
        },50000);

        function createQrcode() {
            layer.open({
                type: 2
                ,content: '稍等，正在生成二维码',shadeClose:false
            });
            $.ajax({
                type: "post",
                url: "{{route('Seat::signQrcode',['token'=>request()->input('token')])}}",
                data: {},
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data, textStatus, jqXHR) {
                    console.log(data);
//                    window.location.href=Url+'&key='+data;
                    $('#qrcode > canvas').remove();
                    $('#qrcode').qrcode({
                        render: "canvas",
                        width: '450',
                        height: '450',
                        text: Url+'&key='+data
                    });
                    layer.closeAll();
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.log("请求失败！");
                }
            });
        }
        createQrcode();
    })();

</script>

</body>
</html>
