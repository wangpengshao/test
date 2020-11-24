@extends('web.luckyDraw.app')

@section('title', $configure['title'])

@section('cssResources')
    @parent
    <link rel="stylesheet" href="{{asset('wechatWeb/LuckyDraw/zhuanpan/css/index-version=1.0.0.css')}}">
@endsection

@section('content')
    <div id="wrap">

        <div class="ribbon"></div>

        <div class="header clearfix" style="position: relative;">
            <p class="rule fl">规则</p>
            <a href="{{route('LuckyDraw01::myRecord',['token'=>$token,'l_id'=>$config['id']])}}"
               id="myWin">
                <p class="my fr">我的奖品</p>
            </a>
            <div class="title"></div>
            <div class="act-time">{{$config['start_at'].' ~ '.$config['end_at']}}
                <p>{{$config['tip']}}</p>
            </div>
        </div>

        <!--轮盘-->
        <div class="rotate">
            <div class="lunpai">
                <ul class="prize running {{(count($prize) == 8) ? 'prize-8' : 'prize-6'}}">
                    @foreach($prize as $value)
                        <li style="background-image: url({{$value['image']}})">
                            <span></span>
                            <p>{{$value['title']}}</p>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="ring"></div>
            <div id="btn"></div>
        </div>
        @if($configure['status'] != 1)
            <div class="disable">活动已关闭</div>
        @else
            @if(date('Y-m-d H:i:s') < $configure['start_at'])
                <div class="disable">活动未开始</div>
            @elseif(date('Y-m-d H:i:s') >= $configure['end_at'])
                <div class="disable">活动已结束</div>
            @else
                <div class="border">您 {{ $configure['type']?'今日':'当前' }}
                    还有 <span id="change"> {{$allowNumber}} </span>
                    次抽奖机会
                </div>
        @endif
    @endif

    <!--滚动信息-->
        <div class="scroll">
            <p></p>
            <div>
                <ul id="infoScroll">
                    @foreach($logList as $key => $value)
                        <li>
                            恭喜 {{str_limit($value->fansInfo['nickname'],3,'...')}}
                            获得 <span class="info">{!! $value['text'] !!}</span>

                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <!--游戏规则弹窗-->
        <div id="mask-rule">
            <div class="box-rule">
                <span class="star"></span>
                <h2>活动规则说明</h2>
                <span id="close-rule"></span>
                <div class="con">
                    <div class="text">
                        {!! $configure['describe'] !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('jsResources')
    @parent
    <script type="text/javascript" src="{{asset('wechatWeb/LuckyDraw/common/js/jquery.rotate.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/LuckyDraw/zhuanpan/js/index-version=1.0.0.js')}}"></script>
    <script type="text/javascript" charset="utf-8">
        const config = @json($config);
        const fansInfo = @json($fansInfo);
        const reader = @json($reader);
        const gatherId = @json($gatherId);
        const prize = @json($prize);
        const csrf_token = "{{csrf_token()}}";
        const token = "{{$token}}";
        const saveGatherUrl = "{!! route('LuckyDraw01::saveGather',['token'=>$token]) !!}";
        const toDrawUrl = "{!! route('LuckyDraw01::toDraw',['token'=>$token]) !!}";
        const bindUrl = "{!! $bindUrl !!}";
        const myRecord = "{!! route('LuckyDraw01::myRecord',['token'=>$token,'l_id'=>$config['id']]) !!}";
        let gather2Switch = 0;
        let gather3Switch = 0;
        let allowNumber = "{{$allowNumber}}";
    </script>
    <script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js" type="text/javascript" charset="utf-8"></script>
    <script>
        // <!-- JS-SDK -->
        wx.config({!! $app->jssdk->buildConfig(['checkJsApi', 'updateAppMessageShareData', 'updateTimelineShareData'], false) !!});
        wx.ready(function () {
            let title = "{{$config['share_title']}}";
            let desc = "{{$config['share_desc']}}";
            let imgUrl = "{!! $config['share_img'] !!}";
            let link = window.location.href;
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
        });
    </script>
@endsection
