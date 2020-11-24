@extends('web.vote.app')
@section('cssResources')
    <link rel="stylesheet" href="{{asset('wechatWeb/vote/css/dropload.css')}}">
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
    @include('web.vote.header')
    <div class="content">
        @include('web.vote.menu')
        <div class="blank20"></div>
        <div id="pageCon" class="match_page masonry">
            <ul class="list_box clearfix"></ul>
        </div>
    </div>

@endsection
@section('jsResources')
    <script type="text/javascript" src="{{asset('wechatWeb/vote/js/masonry.pkgd.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/vote/js/imagesloaded.pkgd.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/vote/js/yxMobileSlider.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/vote/js/dropload.js')}}"></script>
    <script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
    <script>
        let sliderHeight = "{{$config->img_height}}";
        let defaultImg = "{!! asset('wechatWeb/vote/img/default.jpg') !!}";
        $(".slider").yxMobileSlider({
            during: 5000, height: sliderHeight
        });   //height可以设置首页幻灯片高度
        //倒计时逻辑判断
        let e_date = "{{$config['e_date']}}"
        e_date = new Date(e_date.replace(/\-/g, "/"));
        let s_date = "{{$config['s_date']}}"
        s_date = new Date(s_date.replace(/\-/g, "/"));
        let s_time = "{{$config['s_time']}}"
        s_time = new Date(s_time.replace(/\-/g, "/"));
        let e_time = "{{$config['e_time']}}"
        e_time = new Date(e_time.replace(/\-/g, "/"));
        let differ = 0;
        initCountDown(s_date, e_date, s_time, e_time);

        function initCountDown(s_date, e_date, s_time, e_time) {
            let now = new Date().getTime();
            if (now < s_time) {
                $('.countDown').text('尚未开始');
                return;
            } else if (s_time <= now && now <= e_time) {
                $('#countDownText').text('报名结束');
                differ = e_time - now;
                differ = parseInt(differ / 1000);
            } else if (e_time < now && now < s_date) {
                $('#countDownText').text('投票开始');
                differ = s_date - now;
                differ = parseInt(differ / 1000);
            } else if (s_date <= now && now <= e_date) {
                $('#countDownText').text('投票结束');
                differ = e_date - now;
                differ = parseInt(differ / 1000);
            } else {
                $('.countDown').text('活动已结束');
                return;
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

        let droploadAjaxSwitch = false;
        $(function () {
            let ajaxItemsUrl = "{!!$urlArr['ajaxItemsUrl']!!}";
            let searchKey = "{!! request()->input('searchKey','') !!}";
            let page = 1;
            //瀑布流图片加载
            let $container = $('.list_box').masonry({
                itemSelector: '.picCon',
            });
            $('.content').dropload({
                scrollArea: window,
                autoLoad: true,
                domDown: {
                    domClass: 'dropload-down',
                    domRefresh: '<div class="dropload-refresh">↑上拉加载更多</div>',
                    domLoad: '<div class="dropload-load"><span class="loading"></span>加载中...</div>',
                    domNoData: '<div class="dropload-noData">到底了~~</div>'
                },
                loadDownFn: function (me) {
                    if (droploadAjaxSwitch === false) {
                        droploadAjaxSwitch = true;
                    } else {
                        return false;
                    }
                    $.ajax({
                        type: 'GET',
                        url: ajaxItemsUrl + '&page=' + page + '&searchKey=' + searchKey,
                        dataType: 'json',
                        success: function (response) {
                            let dataLength = response.data.length;
                            let result = '';
                            if (dataLength > 0) {
                                $.each(response.data, function (index, item) {
                                    result += PJHTMLren(item);
                                })
                            }
                            if (result !== '') {
                                let $moreBlocks = $(result);
                                $container.append($moreBlocks);
                                $container.masonry('appended', $moreBlocks);
                                $container.imagesLoaded().progress(function (instance, image) {
                                    $container.masonry('layout');
                                }).always(function (instance) {
                                    if (response.next_page_url === null || dataLength === 0) {
                                        me.lock();
                                        me.noData(true);
                                        me.resetload();
                                    } else {
                                        page++;
                                        me.resetload();
                                        droploadAjaxSwitch = false;
                                    }
                                });
                            }
                        },
                        error: function (xhr, type) {
                            // 即使加载出错，也得重置
                            alert('Ajax error!');
                            // 锁定
                            me.lock();
                            // 无数据
                            me.noData();
                            me.resetload();
                        }
                    });
                }
            });
        });
        let g_id = "{{$g_id}}";
        const subscribe = "{{$fansInfo['subscribe']}}";
        //Js add dom 赋予事件
        $(document).on('click', '.index-vote', function () {
            event.preventDefault();   // 阻止浏览器默认事件，重要
            let t_id = $(this).data('id');
            if (subscribe !== "1") {
                toast('请先关注公众号才可进行投票！');
                showQrCode();
                return false;
            }
            ajaxVote(g_id, t_id);
        });

        function PJHTMLren(item) {
            let HTMLBank = '<li class="picCon">' +
                '<div><i class="number">' + item["number"] + '</i>' +
                '<a class="img" href="' + item["url"] + '"><img  src="' + item["cover"] + '"></a>' +
                '<div class="clearfix">' +
                '<a  href="' + item["url"] + '"><p><span class="item-title">' + item["title"] + '</span>' + item["voting_n"] + '票</p></a>' +
                '<a href="javascript:void(0)" class="vote index-vote" data-id="' + item["id"] + '">投票</a>' +
                '</div></div></li>'
            // console.log(HTMLBank);
            return HTMLBank
        }
    </script>
@endsection
