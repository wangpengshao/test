@extends('web.newVote.app')

@section('cssResources')
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/swiper.min.css'}}" />
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/index.css'}}" />
    <link rel="stylesheet" type="text/css" href="{{$templatePath.'/css/mescroll.min.css'}}" />
    <link rel="stylesheet" href="{{asset('wechatWeb/vote/css/dropload.css')}}">
@endsection

@section('content')
    <div class="bannerArea" @empty(!$config['img_height'])style="height: {{$config['img_height']}}px;" @endempty>
        <!-- Swiper -->
        <div class="swiper-container">
            <div class="swiper-wrapper">
                @foreach($sliderImg as $img)
                    <div class="swiper-slide"><img src="{{$img}}" /></div>
                @endforeach
            </div>
            @empty($myItemID)
                @if($config['s_time'] < date('Y-m-d H:i:s') && $config['e_time'] > date('Y-m-d H:i:s'))
                    <div class="actDet">
                        <a href="{{$urlArr['signUpUrl']}}" class="goSignBtn">我要报名</a>
                    </div>
                @endif
            @else
                <div class="actDet">
                    <a href="{{$urlArr['detailsUrl'].'&t_id='.$myItemID}}" class="goSignBtn">我的作品</a>
                </div>
            @endempty

            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
        </div>
        <div class="wrap actNumberArea">
            <div class="actNumberIn">
                <p class="actNumTit">已报名</p>
                <p class="actNum">{{$groupData['item_n']}}</p>
            </div>
            <div class="actNumberIn">
                <p class="actNumTit">投票人次</p>
                <p class="actNum">{{$groupData['voting_n']}}</p>
            </div>
            <div class="actNumberIn">
                <p class="actNumTit">浏览量</p>
                <p class="actNum">{{$groupData['view_n']}}</p>
            </div>
        </div>
    </div>
    <div class="actEndTimeArea">
        <div class="wrap">
            <div class="actEndTimeAreaIn">
                <i class="actEndTmIcon"></i>
                <div class="actEndTmCon">
                    <p class="actEndTmIn">
                        距离<span id="countDownText">活动开始</span>还剩:
                        <strong id="DD" style="color:red">0</strong> 天
                        <strong id="HH" style="color:red">00</strong> 时
                        <strong id="MM" style="color:red">00</strong> 分
                        <strong id="SS" style="color:red">00</strong> 秒
                    </p>
                    <p class="actHint">@isset($config['top_tip'])公告：{{$config['top_tip']}}@else公告：本届大赛严禁刷票，一经查出，取消其作品参赛资格！@endisset</p>
                </div>
            </div>
            <div class="entrieArea">
                <div class="entrieTitArea">
                    <i class="entrieBg"></i>
                    <p class="entrieTit">参赛作品</p>
                </div>
                <div class="entHeadTab">
                    <ul class="entHeadTabUl">
                        <li class="entHeadTabLi  entHeadCur"><a href="{{$urlArr['indexUrl']}}" class="entHeadTabLink">参赛列表</a></li>
                        <li class="entHeadTabLi"><a href="{{$urlArr['rankUrl']}}" class="entHeadTabLink">投票排行</a></li>
                        <li class="entHeadTabLi"><a href="{{$urlArr['explainUrl']}}" class="entHeadTabLink">活动详情</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="wrap">
        <div class="entConArea">
            <div class="entConIn">
                <ul class="entConUl mescroll" id="mescroll">
                </ul>
            </div>
        </div>
    </div>
@endsection

@section('jsResources')
    <script type="text/javascript" src="{{$templatePath.'/js/index.js'}}"></script>
    <script type="text/javascript" src="{{$templatePath.'/js/swiper.min.js'}}"></script>
    <script type="text/javascript" src="{{$templatePath.'/js/search.js'}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/vote/js/dropload.js')}}"></script>
    <script type="text/javascript" src="{{$templatePath.'/js/mescroll.min.js'}}"></script>
    <!-- Initialize Swiper -->
    <script>
        var swiper = new Swiper('.swiper-container', {
            loop:true,
            effect : 'fade',
            autoplay:{
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
            },
        });
        // 倒计时调用
        //倒计时逻辑判断
        let s_time = "{{$config['s_time']}}"    //报名开始时间
        s_time = new Date(s_time.replace(/\-/g, "/")).getTime();
        let e_time = "{{$config['e_time']}}"    //报名结束时间
        e_time = new Date(e_time.replace(/\-/g, "/")).getTime();

        let s_date = "{{$config['s_date']}}"    //投票开始时间
        s_date = new Date(s_date.replace(/\-/g, "/")).getTime();
        let e_date = "{{$config['e_date']}}"    //投票结束时间
        e_date = new Date(e_date.replace(/\-/g, "/")).getTime();

        let differ = 0;
        initCountDown(s_time, e_time, s_date, e_date);

        function initCountDown(s_time, e_time, s_date, e_date) {
            let now = new Date().getTime();

            if(s_time > now){
                $('#countDownText').text('活动报名开始');
                differ = s_time - now;
                differ = parseInt(differ / 1000);
                setInterval("timingCountDown()", 1000)
                return;
            }

            if(s_time < now && now < e_time){
                $('#countDownText').text('活动报名结束');
                differ = e_time - now;
                differ = parseInt(differ / 1000);
                setInterval("timingCountDown()", 1000)
                return;
            }

            if(e_time < now && now < s_date){
                $('#countDownText').text('投票开始');
                differ = s_date - now;
                differ = parseInt(differ / 1000);
                setInterval("timingCountDown()", 1000)
                return;
            }
            if(s_date < now && now < e_date){
                $('#countDownText').text('投票结束');
                differ = e_date - now;
                differ = parseInt(differ / 1000);
                setInterval("timingCountDown()", 1000)
                return;
            }
            if (now > e_date) {
                $('.actEndTmIn').text('活动已结束');
                return;
            }

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
    </script>
    {{--异步加载作品--}}
    <script>
        $(function(){
            let ajaxItemsUrl= "{!!$urlArr['ajaxItemsUrl']!!}";
            let searchKey = "{!! request()->input('searchKey','') !!}";
            //当前关键词
            var curWord = '';

            var mescroll = new MeScroll("body", {
                down: {
                    isLock:true  // 锁定下拉功能
                },
                up: {
                    callback: upCallback, //上拉加载的回调
                    page: {
                        num: 0, //当前页 默认0,回调之前会加1; 即callback(page)会从1开始
                        size: 5 //每页数据条数,默认10
                    },
                    htmlNodata: '',
                    noMoreSize: 1,
                    isBounce: false, //此处禁止ios回弹,
                    clearEmptyId: "mescroll",
                    hardwareClass: "mescroll-hardware",
                    empty: {
                        warpId:'mescroll', //父布局的id; 如果此项有值,将不使用clearEmptyId的值;
                        icon: null, //图标,默认null
                        tip: "暂无相关数据~", //提示
                        btntext: "", //按钮,默认""
                        btnClick: null, //点击按钮的回调,默认null
                    },
                    loadFull:{
                        delay : 800
                    },
                    scrollbar:{
                        use:true,
                        barClass : "mescroll-bar"
                    }
                }
            });

            //搜索按钮
            $("#search").click(function(){
                var word=$("#keyword").val();
                if(word){
                    curWord=word; //更新关键词
                    mescroll.resetUpScroll(); //重新搜索,重置列表数据
                }
            })

            //下拉刷新的回调
            function downCallback() { }
            //上拉加载的回调
            function upCallback(page) {console.log('上拉加载...')
                let ajaxItemsUrl= "{!!$urlArr['ajaxItemsUrl']!!}"

                $.ajax({
                    url: ajaxItemsUrl + '&page=' + page.num + '&searchKey=' + curWord,
                    dataType:'json',
                    type:'GET',
                    success: function(res) {
                        let html = '';
                        let dataLength = res.data.length;
                        let hasNext = res.next_page_url ? true:false;
                        if(dataLength > 0){
                            $.each(res.data, function (index, item) {
                                html += PJHTMLren(item);
                            })
                        }
                        if (res.next_page_url === null || dataLength === 0) {
                            mescroll.lockUpScroll(true);
                        }
                        mescroll.endSuccess(dataLength, hasNext );
                        if(html){
                            $('#mescroll').append(html);
                        }
                    },
                    error: function(e) {
                        mescroll.endErr();
                    }
                });
            }

            function PJHTMLren(item) {
                let defaultImg = "{{asset('wechatWeb/vote/template9/images/perIcon.png')}}";
                let HTMLBank = `<li class="entConLi">
                            <div class="entTopTip">
                                <span class="flLeft"><i class="likeIcon"></i><i>${item.voting_n}</i></span>
                                <span class="flRight"><i class="lookIcon"></i><i>${item.view_n}</i></span>
                            </div>
                            <a href="${item.url}" class="entConLink"><img src="${item.cover}" /></a>
                            <p class="entTit"><span class="flLeft">${item.title.length>12 ? item.title.substr(0,12)+' . . .' : item.title}</span><span class="flRight">${item.created_at.substr(0,10)}</span></p>
                            <div class="entMes">${item.info}</div>
                            <div class="entWriteLine">
                            <span class="flLeft perNameArea">
                                <img class="perImg" src="${item.fans ? item.fans.headimgurl : defaultImg}">
                                <i style="vertical-align: middle;">${item.fans ? item.fans.nickname : '佚名'}</i>
                            </span>
                                <span class="flRight">编号：<i class="ftSize28">${item.number}</i></span>
                            </div>
                        </li>`;

                return HTMLBank
            }


            $(".mbSearchBtn").click(function(){
                if($('.mbSearchInpArea').css('display')==='block'){
                    return false;
                }else{
                    console.log('搜索...')
                    var keyword = $.trim($('#mobileSearch').val());

                    if(keyword){
                        curWord=keyword; //更新关键词
                        mescroll.resetUpScroll(); //重新搜索,重置列表数据
                    }
                }
            })

        })

    </script>
@endsection