@extends('web.newVote.app')

@section('cssResources')
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/activeDet.css'}}" />
@endsection

@section('content')
    <div class="bannerArea">
        <div class="bannerImg detBanner">
            <div class="wrap actDetTopArea">
                <div class="constTit">
                    <p class="constTitEn">大赛详情</p>
                    <p class="constTitEng">CONTEST DETAILS</p>
                </div>
                {{--<div class="singUpBtn"><span class="singUpBtnIn">我要报名</span></div>--}}
                <div class="downDire"></div>
            </div>
        </div>
        <div class="wrap actDetArea">
{{--            <div class="actDetGroup">--}}
{{--                <div class="actDetGTit">活动时间</div>--}}
{{--                <div class="neirong" style="text-align: center;">--}}
{{--                    <p>活动<span style="color: red;">报名</span>时间：{{substr($config['s_time'],0,10)}} 到 {{substr($config['e_time'],0,10)}}</p>--}}
{{--                    <p>活动<span style="color: red;">结束</span>时间：{{substr($config['s_date'],0,10)}} 到 {{substr($config['e_date'],0,10)}}</p>--}}
{{--                </div>--}}
{{--            </div>--}}

            @isset($config['explain_a'])
                <div class="actDetGroup">
                    <div class="actDetGTit">{{$config['explain_a']}}</div>
                    <div class="neirong">{!! $config['explain_at'] !!}</div>
                </div>
            @endisset

            @isset($config['explain_b'])
                <div class="actDetGroup">
                    <div class="actDetGTit">{{$config['explain_b']}}</div>
                    <div class="neirong">{!! $config['explain_bt'] !!}</div>
                </div>
            @endisset

            @isset($config['explain_c'])
                <div class="actDetGroup noBorder">
                    <div class="actDetGTit">{{$config['explain_c']}}</div>
                    <div class="neirong">{!! $config['explain_ct'] !!}</div>
                </div>
            @endisset
            <div class="actDetIcon"></div>
        </div>
    </div>
@endsection
@section('footer')
@endsection
@section('jsResources')
    <script type="text/javascript" src="{{$templatePath.'/js/index.js'}}"></script>
    <script type="text/javascript" src="{{$templatePath.'/js/search.js'}}"></script>
@endsection