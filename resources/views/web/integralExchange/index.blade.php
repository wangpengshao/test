@extends('web.integralExchange.app')
<head>
    <title>兑奖列表</title>
</head>
@section('cssResources')
    @parent
    <link rel="stylesheet" href="{{asset('wechatWeb/IntegralExchange/common/css/awardRecord.css')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/IntegralExchange/common/css/layer.css')}}">
@endsection
@section('content')
    <body>
    <div class="headTab">
        <ul class="headTabUl" style="height: 30px;">
            <li class="headTabLi headTabCur">
                <a href="/webWechat/IntegralAward/index?token={{$token}}" class="headTabLink"
                   style="height: 30px;font-size: 20px;line-height: 30px;">兑奖列表</a>
            </li>
            <li class="headTabLi">
                <a href="/webWechat/IntegralAward/awardRecord?token={{$token}}" class="headTabLink"
                   style="height: 30px;font-size: 20px;line-height: 30px;" id="log">兑奖记录</a>
            </li>
        </ul>
    </div>
    <div class="draw-list">
        <ul class="draw-content" id="ul">
            @foreach($configure as $value)
                <li class="draw-item">
                    <img class="draw-cover" src="{{$value['image']}}" alt="">
                    <div class="draw-detail">
                        <div class="draw-title">{{$value['title']}}</div>
                        <div class="draw-time">
                            <span class="time">所需积分:{{$value['integral']}}</span>
                            <!-- 兑奖方式为线上快递并且没有保存个人信息的情况 -->
                            @if ($value['reward_way'] == 1 && $gatherId == '')
                                <span class="go_reward" id="go_reward" data-reward_id="{{$value['id']}}"></span>
                            @else
                            <!-- 兑奖方式为线上快递且存在个人信息或者兑奖方式为线下 -->
                                <span class="go" id="go" data-id="{{$value['id']}}"></span>
                            @endif
                        </div>
                        <div class="draw-time">
                            <span class="time" id="change">所剩库存:{{$value['inventory']}}</span>
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
    <script type="text/javascript" src="{{asset('wechatWeb/IntegralExchange/common/js/integralExchange.js')}}"></script>
    <script type="text/javascript"
            src="{{asset('wechatWeb/IntegralExchange/common/js/h5_game_common-version=1.0.0.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/LuckyDraw/common/js/swiper.jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/IntegralExchange/common/js/layer.js')}}"></script>
    <script type="text/javascript" charset="utf-8">
        const csrf_token = "{{csrf_token()}}";
        const token = "{{request()->get('token')}}";
        const saveGatherUrl = '{!! route('Award::saveGather',['token'=>request()->get('token')]) !!}';
        const matchPrizeUrl = '{!! route('Award::matchPrize',['token'=>request()->get('token')]) !!}';
        const awardRecord = '{!! route("AwardRecord::home",["token"=>request()->get("token"),"id"=>request()->get("id")]) !!}';
        let gather2Switch = 0;
        let gather3Switch = 0;
    </script>
@endsection

