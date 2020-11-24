@extends('web.newVote.app')

@section('cssResources')
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/index.css'}}" />
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/rank.css'}}" />
    <style>
        @media (max-width: 980px){
            .FrontIcon {
                width: .32rem;
                height: .22rem;
                left: 0;
                margin-top: -.11rem;
            }
        }
    </style>
@endsection

@section('content')
    <div class="actEndTimeArea">
        <div class="wrap">
            <div class="entrieArea entRankArea">
                <div class="entrieTitArea">
                    <i class="entrieBg entrieBg3"></i>
                    <p class="entrieTit">活动详情</p>
                </div>
                <div class="entHeadTab">
                    <ul class="entHeadTabUl">
                        <li class="entHeadTabLi"><a href="{{$urlArr['indexUrl']}}" class="entHeadTabLink">参赛列表</a></li>
                        <li class="entHeadTabLi"><a href="{{$urlArr['rankUrl']}}" class="entHeadTabLink">投票排行</a></li>
                        <li class="entHeadTabLi entHeadCur"><a href="{{$urlArr['explainUrl']}}" class="entHeadTabLink">活动详情</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
                    {{--<p class="constTitEng">CONTEST DETAILS</p>--}}
        <div class="wrap" style="background-color: #fff;">
{{--            <div class="actDetGroup" style="width: 86%; margin-left: auto; margin-right: auto;padding-bottom: .23rem;">--}}
{{--                <div class="actDetGTit" style="font-weight: 600;">活动时间</div>--}}
{{--                <div class="neirong">--}}
{{--                    <p>活动报名时间：{{substr($config['s_time'],0,10)}} 到 {{substr($config['e_time'],0,10)}}</p>--}}
{{--                    <p>活动结束时间：{{substr($config['s_date'],0,10)}} 到 {{substr($config['e_date'],0,10)}}</p>--}}
{{--                </div>--}}
{{--            </div>--}}

            @isset($config['explain_a'])
                <div class="actDetGroup" style="width: 86%; margin-left: auto; margin-right: auto;padding: .23rem 0;">
                    <div class="actDetGTit" style="font-weight: 600;">{{$config['explain_a']}}</div>
                    <div class="neirong">{!! $config['explain_at'] !!}</div>
                </div>
            @endisset

            @isset($config['explain_b'])
                <div class="actDetGroup" style="width: 86%; margin-left: auto; margin-right: auto;padding: .23rem 0;">
                    <div class="actDetGTit" style="font-weight: 600;">{{$config['explain_b']}}</div>
                    <div class="neirong">{!! $config['explain_bt'] !!}</div>
                </div>
            @endisset

            @isset($config['explain_c'])
                <div class="actDetGroup noBorder" style="width: 86%; margin-left: auto; margin-right: auto;padding: .23rem 0;">
                    <div class="actDetGTit" style="font-weight: 600;">{{$config['explain_c']}}</div>
                    <div class="neirong">{!! $config['explain_ct'] !!}</div>
                </div>
            @endisset
            <div class="actDetIcon"></div>
        </div>
    </div>
@endsection

@section('jsResources')
    <script type="text/javascript" src="{{$templatePath.'/js/index.js'}}"></script>
    <script type="text/javascript" src="{{$templatePath.'/js/search.js'}}"></script>
@endsection