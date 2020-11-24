<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="format-detection" content="telephone=no">
    <title>我的奖品列表</title>
    <link rel="stylesheet" href="{{asset('wechatWeb/LuckyDraw/common/css/myRecord.css')}}">
    <!-- 移动端适配 -->
</head>

<body>
<!-- 二维码弹框 -->
<div id="showEWr" class="showEWr hidden">
    <div class="inner">
        <img class="cancelImg" src="{{asset('wechatWeb/LuckyDraw/common/image/myRecord/close.png')}}" alt="">
        <span class="ewr_title">兑奖码二维码</span>
        <div id="qrcode" class="ewrpic"></div>
    </div>
</div>
<!-- 抽奖列表 -->
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
                    <div class="award-code">
                        <span class="status" style="color: #999999;">状态:</span>
                        <span class="status" style="color: #999999;">
                            @if($value['status'] == 1)
                                已发奖
                            @else
                                未发奖
                            @endif
                        </span>
                    </div>
                    <div class="draw-time">
                        <span class="time">获奖时间:{{$value['created_at']}}</span>
                        @if($value['status']==1)
                            <img class="received"
                                 src="{{asset('wechatWeb/LuckyDraw/common/image/myRecord/received.png')}}">
                        @endif
                    </div>
                    <div class="award-code">
                        <span class="code-title">兑奖码</span>
                        <span class="code-number">{{$value['code']}}</span>
                        <img data-text="{{$value['qrCodeText']}}" class="code-ewr"
                             src="{{asset('wechatWeb/LuckyDraw/common/image/myRecord/ewr.png')}}" alt="">
                    </div>
                    @if(empty($value->address['address']))
                        <div class="award-address">
                            <a href="{{route('LuckyDraw01::addAddress',['token'=>$configure['token'],'l_id'=>$id,'id'=>$value['id']])}}"><span
                                        class="code-title" style="float: right; width:200px;padding: 0.03rem 0.1rem;background-color:transparent;">快递信息填写</span></a>
                        </div>
                    @else
                        <div class="award-address">
                            <span class="address">快递地址:</span>
                            <span class="address"
                                  style="width:140px;background-color:transparent;color: #999999;">{{$value->address['address']}}</span>
                        </div>
                        @if(!empty($value['expressNo']))
                            <div class="expressNo">
                                <span>物流单号:</span>
                                <span style="width:140px;background-color:transparent;color: #999999;">{{$value['expressNo']}}</span>
                            </div>
                        @endif
                    @endif
                </div>
            </li>
        @endforeach

    </ul>
</div>
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
<script type="text/javascript" src="{{asset('wechatWeb/LuckyDraw/common/js/qrcode.min.js')}}"></script>
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
</body>

</html>
