@extends('web.integralExchange.app')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="format-detection" content="telephone=no">
    <title>兑奖记录</title>
    <!-- 移动端适配 -->
</head>
@section('cssResources')
    @parent
    <link rel="stylesheet" href="{{asset('wechatWeb/IntegralExchange/common/css/awardRecord.css')}}">
@endsection

@section('content')
<body>
<div class="headTab">
    <ul class="headTabUl" style="height: 30px;">
        <li class="headTabLi">
            <a href="/webWechat/IntegralAward/index?token={{$token}}" class="headTabLink" style="height: 30px;font-size: 20px;line-height: 30px;">兑奖列表</a>
        </li>
        <li class="headTabLi headTabCur">
            <a href="/webWechat/IntegralAward/awardRecord?token={{$token}}" class="headTabLink" style="height: 30px;font-size: 20px;line-height: 30px;" id="log">兑奖记录</a>
        </li>
    </ul>
</div>
<!-- 二维码弹框 -->
<div id="showEWr" class="showEWr hidden">
    <div class="inner">
        <img class="cancelImg" src="{{asset('wechatWeb/IntegralExchange/common/image/awardRecord/close.png')}}" alt="">
        <span class="ewr_title">兑奖码二维码</span>
        <div id="qrcode" class="ewrpic"></div>
    </div>
</div>
<!-- 已兑奖品列表 -->
<div class="draw-list">
    <div class="draw-top">
        <span class="contact-info">
            @isset($configure['phone'])
                联系电话:{{$configure['phone']}}&nbsp;&nbsp;
            @endisset
            &nbsp;
            @isset($configure['qq'])
                联系QQ:{{$configure['qq']}}
            @endisset

        </span>
    </div>
    <ul class="draw-content">
        @foreach($myList as $value)
            <li class="draw-item">
                <img class="draw-cover" src="{{$value['image']}}" alt="">
                <div class="draw-detail">
                    <div class="draw-title">{{$value->prize['title']}}</div>
                    <div class="draw-time">
                        <span class="time">兑奖时间:{{$value['created_at']}}</span>
                        @if($value['status']==1)
                            <img class="received"
                                 src="{{asset('wechatWeb/IntegralExchange/common/image/awardRecord/received.png')}}">
                        @endif
                    </div>
                    <div class="award-code">
                        <span class="code-title">兑奖码</span>
                        <span class="code-number">{{$value['code']}}</span>
                        <img data-text="{{$value['qrCodeText']}}" class="code-ewr"
                             src="{{asset('wechatWeb/IntegralExchange/common/image/awardRecord/ewr.png')}}" alt="">
                    </div>
                </div>
            </li>
        @endforeach

    </ul>
</div>
</body>
@endsection

@section('jsResources')
    @parent
<script type="text/javascript" charset="utf-8" src="https://cdn.staticfile.org/jquery/3.3.1/jquery.min.js"></script>
<script>
    (function (doc, win) {
        var docEl = doc.documentElement,
            resizeEvt = 'orientationchange' in window ? 'orientationchange' : 'resize',
            recalc = function () {
                var clientWidth = docEl.clientWidth;
                if (!clientWidth) return;
                if (clientWidth >= 750) {
                    docEl.style.fontSize = '100px';
                } else {
                    docEl.style.fontSize = 100 * (clientWidth / 750) + 'px';
                }
            };

        if (!doc.addEventListener) return;
        win.addEventListener(resizeEvt, recalc, false);
        doc.addEventListener('DOMContentLoaded', recalc, false);
    })(document, window);
</script>
<script type="text/javascript" src="{{asset('wechatWeb/IntegralExchange/common/js/qrcode.min.js')}}"></script>
<script>
    $(function () {
        var qrCode = new QRCode('qrcode', {
            width: 128,
            height: 128,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        $(".code-ewr").click(function () {
            let text = $(this).data('text');
            text = JSON.stringify(text);
            qrCode.makeCode(text);
            $("#showEWr").removeClass("hidden");
        });
        $(".cancelImg").click(function () {
            $("#showEWr").addClass("hidden");
            qrCode.clear();
        });
    })
</script>
@endsection
