<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=uft-8">
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="black" name="apple-mobile-web-app-status-bar-style">
    <meta content="telephone=no" name="format-detection">
    <meta charset="uft-8">
    <title>{{$config['title']}}</title>
    <link rel="stylesheet" href="{{$templatePath.'/touch.css'}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/vote/css/common.css')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/vote/css/loading.css')}}">
    @yield('cssResources')
</head>
<body>
{{--载入页面动画--}}
<div id="loading">
    <div id="loading-center">
        <div id="loading-center-absolute">
            <div class="object" id="object_one"></div>
            <div class="object" id="object_two"></div>
            <div class="object" id="object_three"></div>
        </div>
    </div>
</div>
{{--处理事务动画--}}
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
@yield('content')
<div class="bot_main">
    <ul>
        <a href="{{$urlArr['indexUrl']}}">
            <li class="ico_1">
                <span class="ico"><img src="{{asset('wechatWeb/vote/img/i1.png')}}"/></span>
                <span class="txt">
                    @if(request()->route()->named('Vote::index'))
                        <strong>首页</strong>
                    @else
                        首页
                    @endif
            </span>
            </li>
        </a>
        <a href="{{$urlArr['rankUrl']}}">
            <li class="ico_2">
                <span class="ico"><img src="{{asset('wechatWeb/vote/img/i3.png')}}"/></span>
                <span class="txt">
                    @if(request()->route()->named('Vote::rank'))
                        <strong>排名</strong>
                    @else
                        排名
                    @endif
            </span>
            </li>
        </a>
        <a href="{{$urlArr['explainUrl']}}">
            <li class="ico_3">
                <span class="ico"><img src="{{asset('wechatWeb/vote/img/i4.png')}}"/></span>
                <span class="txt">
                 @if(request()->route()->named('Vote::explain'))
                        <strong>说明</strong>
                    @else
                        说明
                    @endif
            </span>
            </li>
        </a>
    </ul>
</div>
<script type="text/javascript" src="{{asset('wechatWeb/vote/js/jQuery-2.1.4.min.js')}}"></script>
<script type="text/javascript">
    $(window).load(function () {
        $("#loading").fadeOut(500);
    })
    $("#loading-center").click(function () {
        $("#loading").fadeOut(500);
    })
    $('#searchBtn').click(function () {
        let val = $('#searchText').val();
        let url = "{!! $urlArr['indexUrl'] !!}";
        if (val == '') return false;
        window.location.href = url + '&searchKey=' + val;
    })

    function openSelect(elem) {
        if (document.createEvent) {
            let e = document.createEvent("MouseEvents");
            e.initMouseEvent("mousedown", true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
            elem[0].dispatchEvent(e);
        } else if (element.fireEvent) {
            elem[0].fireEvent("onmousedown");
        }
    }

    $('#des').click(function () {
        openSelect($(this).next());
    });

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

    $('#groupSwitch').on('change', function () {
        let val = $(this).val();
        if (val === '') return false;
        let url = window.location.href;
        url = changeURLArg(url, 'g_id', val)
        window.location.href = url;
    });

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

    function showQrCode() {
        $('#qrCode').removeClass('hidden');
    }

    function hideQrCode() {
        $('#qrCode').addClass('hidden');
    }

    $('.cancelImg').click(function () {
        hideQrCode();
    })
    let csrf_token = "{{csrf_token()}}";

    function ajaxVote(g_id, t_id) {
        showLoading('投票中..');
        $.ajax({
            type: "POST",
            url: "{!! $urlArr['ajaxVoteUrl'] !!}",
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': csrf_token
            },
            data: {
                t_id: t_id, g_id: g_id,
            },
            success: function (response) {
                hideLoading();
                toast(response.message);
                if (response.status == true) {
                    setTimeout(function () {
                        window.location.reload();
                    }, 2000)
                }
            }
        });
    }
</script>
@yield('jsResources')
<div style="display:none">
    <!-- 统计代码 -->
    {!! $config['statistical_code'] !!}
</div>
</body>
</html>

