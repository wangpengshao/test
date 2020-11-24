@extends('web.collectCard.app')

@section('bodyClass', 'bgRed')

@section('content')
    <header class="cardColHd">
        <div class="bgPicSm bgPicSmLf"></div>
        <div class="bgPicSm bgPicSmRt"></div>
        <div class=" bgPicBot ">
            <div class="bgPicSm bgPicBot04"></div>
            <div class="bgPicSm bgPicBot01"></div>
            <div class="bgPicSm bgPicBot02"></div>
            <div class="bgPicSm bgPicBot03"></div>
        </div>
        <div class=" fgPicBig ">
            <div class="bgPicSm fgPicB01"></div>
            <div class="bgPicSm fgPicB02"></div>
            <div class="bgPicSm fgPicB03"></div>
            <div class="bgPicSm fgPicB04"></div>
        </div>
        <div class="actTitArea ovFlow"><div class="actTitAreaIn"></div></div>
        <div class="actTime">活动时间:{{str_limit($config['start_at'],16,'').'~'.date('Y-m-d H:i',strtotime($config['end_at']))}}</div>
    </header>
    <div class="actDesArea">
        <div class="actDesCon">
            {!! $config['description'] !!}
        </div>
        {{--<div class="actDesEq"><img src="img/eqImg.png" alt="" /></div>--}}
        {{--<div class="actDesEqHint">--}}
            {{--<p>长按识别二维码</p>--}}
            {{--<p>关注公众号后即可参与活动</p>--}}
        {{--</div>--}}
    </div>

@endsection



