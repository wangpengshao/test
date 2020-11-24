@extends('web.luckyDraw.app')
@section('title', $configure['title'])
@section('cssResources')
    @parent
    <link rel="stylesheet" href="{{asset('wechatWeb/LuckyDraw/goldEgg/css/index-version=1.0.0.css')}}">
@endsection

@section('content')
    <div id="wrap">
        <div class="bg"></div>
        <div class="rule"></div>
        <a href="{{route('LuckyDraw03::myRecord',['token'=>request()->get('token'),'l_id'=>request()->get('l_id')])}}"
           id="myWin">
            <div class="my">我的奖品</div>
        </a>
        <!--砸蛋区域-->
        <div class="box">
            <p class="tips">
                @if($configure['status'] != 1)
                    活动已关闭
                @else
                    @if(date('Y-m-d H:i:s') < $configure['start_at'])
                        活动未开始
                    @elseif(date('Y-m-d H:i:s') >= $configure['end_at'])
                        活动已结束
                    @else
                        您{{ $configure['type']?'今日':'当前' }}可砸
                        <span id="change"> {{$allowNumber}}</span>
                        次
                    @endif
                @endif
            </p>
            <ul class="egg clearfix">
                @foreach($eggs as $k=>$v)
                    <li>
                        @if(is_array($v))
                            <img src="{{($v['is_winning']==1) ?$v['image'] :asset('wechatWeb/LuckyDraw/goldEgg/image/step4.png')}}"
                                 class="goldegg">
                        @else
                            <img src="{{asset('wechatWeb/LuckyDraw/goldEgg/image/egg.png')}}" class="goldegg init"
                                 data-rank="{{$k}}">
                        @endif
                        <img src="{{asset('wechatWeb/LuckyDraw/goldEgg/image/base.png')}}">
                        <div class="info"></div>
                    </li>
                @endforeach

            </ul>
            <div id="hammer" class="shak"></div>
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
    <script type="text/javascript" src="{{asset('wechatWeb/LuckyDraw/common/js/jquery.cookie.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/LuckyDraw/goldEgg/js/index-version=1.0.0.js')}}"></script>
    <script type="text/javascript" charset="utf-8">
        const config = @json($config);
        const fansInfo = @json($fansInfo);
        const reader = @json($reader);
        const gatherId = @json($gatherId);
        const csrf_token = "{{csrf_token()}}";
        const token = "{{request()->get('token')}}";
        const saveGatherUrl = '{!! route('LuckyDraw03::saveGather',['token'=>request()->get('token')]) !!}';
        const toDrawUrl = '{!! route('LuckyDraw03::toDraw',['token'=>request()->get('token')]) !!}';
        const myRecord = '{!! route("LuckyDraw03::myRecord",["token"=>request()->get("token"),"l_id"=>request()->get("l_id")]) !!}';

        const step1 = "{{asset('wechatWeb/LuckyDraw/goldEgg/image/step1.png')}}";
        const step2 = "{{asset('wechatWeb/LuckyDraw/goldEgg/image/step2.png')}}";
        const step3 = "{{asset('wechatWeb/LuckyDraw/goldEgg/image/step3.png')}}";
        const step4 = "{{asset('wechatWeb/LuckyDraw/goldEgg/image/step4.png')}}";

        let gather2Switch = 0;
        let gather3Switch = 0;
        let allowNumber = "{{$allowNumber}}";
    </script>
@endsection
