@extends('web.vote.app')

@section('content')
    @include('web.vote.header')

    <div class="content">
        @include('web.vote.menu')
        <div class="blank20"></div>
        <div class="rank300" id="top300">
            <ul>
                <li class="rank-head">
                    <span>排名</span>
                    <span>编号</span>
                    <span>{{$config['unit_title'] or '作品'}}</span>
                    <span>查看数</span>
                    <span>票数</span>
                </li>
                @foreach($rankList as $k => $v)
                    <a href="{{$v['url']}}">
                        <li class="list rank-list">
                            @switch($v['rank'])
                                @case(1)
                                <span class="crown"><img src="{{asset('wechatWeb/vote/img/one.png')}}" alt="1"></span>
                                @break
                                @case(2)
                                <span class="crown"><img src="{{asset('wechatWeb/vote/img/two.png')}}" alt="2"></span>
                                @break
                                @case(3)
                                <span class="crown"><img src="{{asset('wechatWeb/vote/img/three.png')}}" alt="3"></span>
                                @break
                                @default
                                <span class="crown">{{$v['rank']}}</span>
                            @endswitch

                            <span>{{$v['number']}}</span>
                            <span>{{$v['title']}}</span>
                            <span>{{$v['views']}}</span>
                            <span>{{$v['votes']}}</span>
                        </li>
                    </a>
                @endforeach
            </ul>
        </div>
    </div>
    <img class="bg" src="{{$templatePath.'/img/mw_005.jpg'}}">

@endsection

@section('jsResources')
    <script type="text/javascript" src="{{asset('wechatWeb/vote/js/yxMobileSlider.js')}}"></script>
    <script>
        let sliderHeight = "{{$config['img_height']}}";
        $(".slider").yxMobileSlider({
            during: 5000, height: sliderHeight
        });   //height可以设置首页幻灯片高度
        //倒计时逻辑判断
        let e_date = "{{$config['e_date']}}"
        e_date = new Date(e_date.replace(/\-/g, "/"));
        let s_date = "{{$config['s_date']}}"
        s_date = new Date(s_date.replace(/\-/g, "/"));
        let differ = 0;
        initCountDown(s_date, e_date);

        function initCountDown(s_date, e_date) {
            let now = new Date().getTime();
            if (e_date < now) {
                $('.countDown').text('活动已结束');
                return;
            }
            // countDownText
            if (s_date < now) {
                $('#countDownText').text('活动结束');
                differ = e_date - now;
                differ = parseInt(differ / 1000);
            } else {
                $('#countDownText').text('活动开始');
                differ = s_date - now;
                differ = parseInt(differ / 1000);
            }
            setInterval("timingCountDown()", 1000)
        }

        function timingCountDown() {
            if (differ < 0) {
                $('#DD').text('0');
                $('#HH').text('00');
                $('#MM').text('00');
                $('#SS').text('00');
                return false;
            }
            let h = Math.floor(differ / 60 / 60);
            let m = Math.floor((differ - h * 60 * 60) / 60);
            let s = Math.floor((differ - h * 60 * 60 - m * 60));
            $('#DD').text(parseInt(h / 24));
            $('#HH').text(h - parseInt(h / 24) * 24);
            $('#MM').text(m);
            $('#SS').text(s);
            differ--;
        }

        {{--$(function () {--}}
        {{--    let ajaxItemsUrl = "{!! $urlArr['ajaxItemsUrl'] !!}";--}}
        {{--    let page = 1;--}}
        {{--    //瀑布流图片加载--}}
        {{--    let $container = $('.list_box').masonry({--}}
        {{--        // columnWidth: 280,--}}
        {{--        itemSelector: '.picCon',--}}
        {{--        // gutter: 20,--}}
        {{--        // isFitWidth: true--}}
        {{--    });--}}

        {{--    $('.content').dropload({--}}
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
        {{--                url: ajaxItemsUrl + '&page=' + page,--}}
        {{--                dataType: 'json',--}}
        {{--                success: function (response) {--}}
        {{--                    let dataLength = response.data.length;--}}
        {{--                    let result = '';--}}
        {{--                    if (dataLength > 0) {--}}
        {{--                        $.each(response.data, function (index, item) {--}}
        {{--                            result += '<li class="picCon"><div>'--}}
        {{--                                + '<i class="number">' + item["number"] + '</i>'--}}
        {{--                                + '<a class="img"><img src="' + item["cover"] + '"></a>'--}}
        {{--                                + '<div class="clearfix"><p> ' + item["title"] + '<br/>' + item["voting_n"] + ' </p>'--}}
        {{--                                + '<a href="" class="vote" data-zpid="">投票</a>';--}}
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
        {{--                        $container.append($moreBlocks);--}}
        {{--                        $container.masonry('appended', $moreBlocks);--}}
        {{--                        $container.imagesLoaded().progress(function () {--}}
        {{--                            $container.masonry('layout');--}}
        {{--                            me.resetload()--}}
        {{--                        });--}}
        {{--                    } else {--}}
        {{--                        me.resetload()--}}
        {{--                    }--}}
        {{--                    // alert(page)--}}
        {{--                },--}}
        {{--                error: function (xhr, type) {--}}
        {{--                    alert('Ajax error!');--}}
        {{--                    // 即使加载出错，也得重置--}}
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
