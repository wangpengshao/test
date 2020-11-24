@extends('web.vote.app')
@section('cssResources')
    <link rel="stylesheet" href="https://cdn.staticfile.org/font-awesome/4.7.0/css/font-awesome.min.css">
@endsection
@section('content')
    {{-- 公众号二维码 --}}
    <div class="inputShade hidden" id="qrCode">
        <div class="inner">
            <img id="wordsCancel" class="cancelImg"
                 src="{{asset('wechatWeb/LuckyDraw/common/image/redPack/close2.png')}}"
                 alt="">
            <span class="input_title">长按识别关注公众号</span>
            <div class="toInput ewrShow" id="promptsText">
                <img src="{{$qrCode}}" alt="公众号二维码">
            </div>
        </div>
    </div>
    <section class="content" id="get_info">
        <div class="detial_box">
            <a class="closed close_detial_box" href="{{$urlArr['indexUrl']}}"></a>
            <span class="fl" id="baby_info">
                {{$details['title']}}
                <span class="item-tip">&nbsp;{{$details['number']}}号&nbsp;</span>
                @switch($details['status'])
                    @case(0)
                    <span class="item-tip">&nbsp;审核中&nbsp;</span>
                    @break
                    @case(-1)
                    <span class="item-tip">&nbsp;已锁定&nbsp;</span>
                    @break
                @endswitch
            </span>
            <div class="blank10"></div>
            <div style="display:flex;white-space:nowrap">
                <span class="infoclass "><i class="fa fa-trophy"></i>&nbsp;第{{$details['ranking']['rank']}}名</span>
                <span class="infoclass " style="width: 100%;text-align: center"><i class="fa fa-credit-card"></i>&nbsp;{{$details['voting_n']}} 票</span>
                <span class="infoclass "><i class="fa fa-eye"></i>&nbsp;{{$details['view_n']}} 次</span>
            </div>
            <div class="blank10"></div>
            @empty(!$details['cover'])
                <img class="pro" src="{{$details['cover']}}" alt="">
            @endempty
            <div class="de-info">
                {{$details['info']}}
            </div>
        </div>
        <div class="blank10"></div>
        <div id="mcover" class="guide-close" onClick="$(this).hide()">
            <img src="{{asset('wechatWeb/vote/img/guide.png')}}"/>
        </div>
        <div class="abtn_box">
            @if($fansInfo['openid']==$details['openid'])
                <a class="a_btn toupiao" href="javascript:void(0)" onclick="$('#mcover').show()">为自己拉票</a>
            @else
                {{--有可投票数--}}
                @if($voteData['currentNumber'] > 0 && $voteData['allowNumber'] > 0)
                    <a href="javascript:void(0)" class="a_btn toupiao vote">
                        已投 {{$voteData['currentVoteNumber']}} 票，还可投
                        {{($voteData['allowNumber'] < $voteData['currentNumber']) ? $voteData['allowNumber'] :$voteData['currentNumber']}}
                        票
                    </a>
                @elseif($voteData['currentVoteNumber']>0 && $voteData['allowNumber'] == 0)
                    <a href="javascript:void(0)" class="a_btn"> 已投满 {{$voteData['currentVoteNumber']}} 票</a>
                @elseif($voteData['currentVoteNumber']>0 && $voteData['currentNumber'] == 0)
                    <a href="javascript:void(0)" class="a_btn"> 已投 {{$voteData['currentVoteNumber']}} 票</a>
                @else
                    <a href="javascript:void(0)" class="a_btn toupiao vote"> 我要投票</a>
                @endif
                <a href="javascript:void(0)" onclick="$('#mcover').show()" class="a_btn">帮TA拉票</a>
            @endif
        </div>

        <section class="rules">
            @foreach($showFields as $val)
                <div class="text">
                    <div class="prize">{{$val['title']}}</div>
                    <div class="neirong">{{$val['value']}}</div>
                </div>
            @endforeach
        </section>

        <div class="blank20"></div>
        <div class="blank20"></div>
        <div class="blank20"></div>
        <div class="blank20"></div>
    </section>

    {{--    <div class="blank20"></div>--}}
    {{--    <section class="rules">--}}
    {{--        <div class="text">--}}
    {{--            <div class="prize">参赛音频</div>--}}
    {{--            <div class="neirong">--}}
    {{--                <div id="wrapper">--}}
    {{--                    <video controls="" name="media" style="width: 100%;height: 76px;">--}}
    {{--                        <source src="{weimicms::$zpinfo['musicurl']}" type="audio/mp4">--}}
    {{--                    </video>--}}
    {{--                </div>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--    </section>--}}
    {{--    <section class="rules mesContent" style="color: black">--}}
    {{--        <div class="text ">--}}
    {{--            <div class="mes-list">--}}
    {{--                <h3><span>留言区</span><i id="sendinfo" class="fa fa-comments"></i></h3>--}}
    {{--                <ul class="messagesList">--}}
    {{--                    <li>--}}
    {{--                        <div class="userPic"><img src="{weimicms::$me['headimgurl']}"></div>--}}
    {{--                        <div class="contentA">--}}
    {{--                            <div class="userName"><a href="javascript:;">{weimicms::$me['name']}</a>:</div>--}}
    {{--                            <div class="msgInfo">{weimicms::$me['message']}</div>--}}
    {{--                            <div class="times"><span>{weimicms::$me['c_time']}</span></div>--}}
    {{--                        </div>--}}
    {{--                    </li>--}}
    {{--                </ul>--}}
    {{--            </div>--}}
    {{--        </div>--}}
    {{--        <div class="blank20"></div>--}}
    {{--        <div class="blank20"></div>--}}
    {{--    </section>--}}

@endsection

@section('jsResources')
    <script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/vote/js/dropload.js')}}"></script>
    <script type="text/javascript">
        const t_id = "{{$details['id']}}";
        const g_id = "{{$details['g_id']}}";
        const subscribe = "{{$fansInfo['subscribe']}}";

        $('.vote').click(function () {
            if (subscribe !== "1") {
                toast('请先关注公众号才可进行投票！');
                showQrCode();
                return false;
            }
            ajaxVote(g_id, t_id);
        })

        {{--$(function () {--}}
        {{--    let ajaxItemsUrl = "{!!$urlArr['ajaxItemsUrl']!!}";--}}
        {{--    let page = 1;--}}
        {{--    $('.mesContent').dropload({--}}
        {{--        scrollArea: window,--}}
        {{--        autoLoad: true,--}}
        {{--        domDown: {--}}
        {{--            domClass: 'dropload-down',--}}
        {{--            domRefresh: '<div class="dropload-refresh">↑上拉加载更多</div>',--}}
        {{--            domLoad: '<div class="dropload-load"><span class="loading"></span>加载中...</div>',--}}
        {{--            domNoData: '<div class="dropload-noData">到底了~~</div>'--}}
        {{--        },--}}
        {{--        loadDownFn: function (me) {--}}
        {{--            $.ajax({--}}
        {{--                type: 'GET',--}}
        {{--                url: ajaxItemsUrl + '&page=' + page ,--}}
        {{--                dataType: 'json',--}}
        {{--                success: function (response) {--}}
        {{--                    let dataLength = response.data.length;--}}
        {{--                    let result = '';--}}
        {{--                    if (dataLength > 0) {--}}
        {{--                        $.each(response.data, function (index, item) {--}}
        {{--                            result += '<li class="picCon"><div><i class="number">' + item["number"] + '</i>'--}}
        {{--                                + '<a class="img" href="' + item["url"] + '"><img src="' + item["cover"] + '"></a>'--}}
        {{--                                + '<div class="clearfix"><a href="' + item["url"] + '">'--}}
        {{--                                + '<p> ' + item["title"] + '<br/>' + item["voting_n"] + ' </p></a>'--}}
        {{--                                + '<a href="javascript:void(0)" class="vote index-vote" data-id="' + item["id"] + '">投票</a>';--}}
        {{--                            +'</div></div></li>';--}}
        {{--                        })--}}
        {{--                    }--}}
        {{--                    if (response.next_page_url === null || dataLength === 0) {--}}
        {{--                        me.lock();--}}
        {{--                        me.noData();--}}
        {{--                    } else {--}}
        {{--                        page++;--}}
        {{--                    }--}}
        {{--                    if (result !== '') {--}}
        {{--                        let $moreBlocks = $(result);--}}
        {{--                        // $container.append($moreBlocks);--}}
        {{--                        // $container.masonry('appended', $moreBlocks);--}}
        {{--                        // $container.imagesLoaded().progress(function () {--}}
        {{--                        //     $container.masonry('layout');--}}
        {{--                        //     me.resetload()--}}
        {{--                        // });--}}
        {{--                            me.resetload()--}}
        {{--                    } else {--}}
        {{--                        me.resetload()--}}
        {{--                    }--}}
        {{--                },--}}
        {{--                error: function (xhr, type) {--}}
        {{--                    // 即使加载出错，也得重置--}}
        {{--                    alert('Ajax error!');--}}
        {{--                    // 锁定--}}
        {{--                    me.lock();--}}
        {{--                    // 无数据--}}
        {{--                    me.noData();--}}
        {{--                    me.resetload();--}}
        {{--                }--}}
        {{--            });--}}
        {{--        }--}}
        {{--    });--}}
        {{--});--}}
    </script>
@endsection
