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
                <i class="entrieBg"></i>
                <p class="entrieTit">投票排行</p>
            </div>
            <div class="entHeadTab">
                <ul class="entHeadTabUl">
                    <li class="entHeadTabLi"><a href="{{$urlArr['indexUrl']}}" class="entHeadTabLink">参赛列表</a></li>
                    <li class="entHeadTabLi entHeadCur"><a href="{{$urlArr['rankUrl']}}" class="entHeadTabLink">投票排行</a></li>
                    <li class="entHeadTabLi"><a href="{{$urlArr['explainUrl']}}" class="entHeadTabLink">活动详情</a></li>
                </ul>
            </div>
        </div>
    </div>
    </div>
    <div class="wrap rankArea">
        <table class="rankTable" cellpadding="0"cellspacing="0" border="0">
            <tr>
                <th><span>排名</span></th>
                <th><span>编号</span></th>
                <th><span>作品</span></th>
                <th><span>浏览量</span></th>
                <th><span>票数</span></th>
            </tr>
            @foreach($rankList as $k => $v)
                <tr class="tbContext">
                    @switch($v['rank'])
                        @case(1)
                        <td class="rankNumFront" style="min-width: 1rem"><i class="FrontIcon FrontOneIcon"></i><span>第1名</span></td>
                        @break
                        @case(2)
                        <td class="rankNumFront"><i class="FrontIcon FrontTwoIcon"></i><span>第2名</span></td>
                        @break
                        @case(3)
                        <td class="rankNumFront"><i class="FrontIcon FrontThreeIcon"></i><span>第3名</span></td>
                        @break
                        @default
                        <td><span>第{{$v['rank']}}名</span></td>
                    @endswitch
                    <td><span>{{$v['number']}}</span></td>
                    <td class="rankNumFront" style="max-width: 2rem;"><a href="{{$v['url']}}"><span>{{$v['title']}}</span></a></td>
                    <td><span>{{$v['views']}}</span></td>
                    <td><span>{{$v['votes']}}</span></td>
                </tr>
            @endforeach
        </table>
    </div>
@endsection

@section('jsResources')
    <script type="text/javascript" src="{{$templatePath.'/js/index.js'}}"></script>
    <script type="text/javascript" src="{{$templatePath.'/js/search.js'}}"></script>
@endsection