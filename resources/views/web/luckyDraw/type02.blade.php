@extends('web.luckyDraw.app')

@section('title', $configure['title'])

@section('cssResources')
    @parent
    <link rel="stylesheet" href="{{asset('wechatWeb/LuckyDraw/tiger/css/index-version=1.0.0.css')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/LuckyDraw/common/css/swiper.min.css')}}">
@endsection

@section('content')
    <div id="wrap">
        <!--游戏区域-->
        <div class="main">
            <!--滚动信息-->
            <div class="info clearfix">
                <img src="{{asset('wechatWeb/LuckyDraw/tiger/image/sound.png')}}">
                <ul>
                    @foreach($logList as $key => $value)
                        <li>
                            恭喜 &nbsp;
                            {{str_limit($value->fansInfo['nickname'],3,'...')}}
                            &nbsp;
                            获得 {!! $value['text'] !!}
                        </li>
                    @endforeach
                </ul>
            </div>
            <!--滚动奖品-->
            <ul class="box clearfix">
                @for ($i = 0; $i < 3; $i++)
                    <li class="roll">
                        <div>
                            <ul>
                                @foreach($range[$i] as $value)
                                    <li class="prize{{$value}}"></li>
                                @endforeach
                                @foreach($prize as $value)
                                    <li style="background-image: url({{$value['image']}}); "></li>
                                @endforeach
                            </ul>
                        </div>
                    </li>
                @endfor
                <li class="shadow"></li>
            </ul>
            <!--信息-->
            <div class="txt clearfix">
                <p class="fl">
                    @if($configure['status'] != 1)
                        活动已关闭
                    @else
                        @if(date('Y-m-d H:i:s') < $configure['start_at'])
                            活动未开始
                        @elseif(date('Y-m-d H:i:s') >= $configure['end_at'])
                            活动已结束
                        @else
                            您{{ $configure['type']?'今日':'当前' }}还有
                            <span id="change"> {{$allowNumber}}</span>
                            次抽奖机会
                        @endif
                    @endif
                </p>
                <p class="fr">{{$configure['title']}}</p>
            </div>
            <!--按钮区域-->
            <div class="box-btn clearfix">
                <div class="rule fl"></div>
                <div id="go" class="go fl"></div>
                <div id="hand" class="shak"></div>
                <a href="{{route('LuckyDraw02::myRecord',['token'=>request()->get('token'),'l_id'=>request()->get('l_id')])}}"
                   id="myWin" class="my fr"></a>
            </div>
            <!--奖品展示-->
            <div class="awards">
                <div class="swiper-container">
                    <ul class="swiper-wrapper">
                        @foreach($prize as $value)
                            <li class="swiper-slide">
                                <img src="{{$value['image']}}">
                            </li>
                        @endforeach

                    </ul>
                </div>
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
    <script type="text/javascript" src="{{asset('wechatWeb/LuckyDraw/common/js/swiper.jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/LuckyDraw/tiger/js/index-version=1.0.0.js')}}"></script>
    <script type="text/javascript" charset="utf-8">
        const config = @json($config);
        const fansInfo = @json($fansInfo);
        const reader = @json($reader);
        const gatherId = @json($gatherId);
        const prize = @json($prize);
        const csrf_token = "{{csrf_token()}}";
        const token = "{{request()->get('token')}}";
        const saveGatherUrl = '{!! route('LuckyDraw02::saveGather',['token'=>request()->get('token')]) !!}';
        const toDrawUrl = '{!! route('LuckyDraw02::toDraw',['token'=>request()->get('token')]) !!}';
        const myRecord = '{!! route("LuckyDraw02::myRecord",["token"=>request()->get("token"),"l_id"=>request()->get("l_id")]) !!}';
        let gather2Switch = 0;
        let gather3Switch = 0;
        let allowNumber = "{{$allowNumber}}";
    </script>
@endsection
