<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>预约记录</title>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/base.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/index.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/iosSelect.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/mescroll.min.css')}}" />
    <style type="text/css">
        .tmChose{
            height: 100%;
            display: inline-block;
            width:100%;
        }
    </style>
</head>
<body>

<div class="isOrderArea">
    @if($myBooking->count() > 0)
        <div class="isOrderTit">正在进行的预约</div>
        <div class="orderTmLine" id="list">
            @foreach($myBooking as $booking)
            <div class="listItem @if($loop->index == 0) listItem-first highlight @elseif($loop->last) listItem-last @endif" data-index="{{$loop->index}}">
                <div class="listItemContent">
                    <div class="listItemContent-date roomName">{{$booking->mark}} {{$booking->chart->numid}} 号位</div>
                    <div class="listItemContent-content odTime">
                        <span>{{substr($booking->s_time,0,16)}}~{{substr($booking->s_time,11,5)}}</span>
                    </div>
                    @if($booking->status == 0)
                        <a href="javascript:;" class="signBtn disNone">待签到</a>
                    @elseif($booking->status == 1)
                        <a href="javascript:;" class="signBtn disNone">已签到</a>
                    @else
                    @endif

                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

<div class="hisOrder pd40">
    <p class="hisOrderTit">预约历史</p>
    <div id="mescroll">

    </div>

</div>


<!--弹出框-->
<div class="continueOrderArea">
    <div class="mask"></div>
    <div class="integralHintIn">
        <i class="closePop"></i>
        <div class="inteHintCon">
            <form action="" method="post">
                <div class="tmGrop">
                    <p class="fmTit">继续预约时间：</p>
                    <div class="tmInpArea ">
                        <div class="tmInp firstTm ">
                            <input type="hidden" name="su_id" id="suId" value="">
                            <span id="showGeneral" class="tmChose"></span>
                        </div>
                        <span>~</span>
                        <div class="tmInp sencondTm ">
                            <input type="hidden" name="su_id1" id="suId1" value="">
                            <span id="showGeneral1" class="tmChose"></span>
                        </div>
                    </div>
                </div>
                <button type="button" class="loginSub contOdBtn">继续预约</button>
                <button type="button" class="loginSub cancelBtn">取消</button>
            </form>
        </div>
    </div>
</div>
<!--弹出层-->
<div class="maxNum">时间段不能为空</div>
<script src="{{asset('wechatWeb/seatBooking/js/common.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/seatBooking/js/jquery-2.1.3.min.js')}}" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
    // 点击高亮
    var closest = function(el, className) {
        if (el.classList.contains(className)) return el;
        if (el.parentNode) {
            return closest(el.parentNode, className);
        }
        return null;
    };
    document.getElementById('list').addEventListener('click', function(e) {
        var listItem = closest(e.target, 'listItem');
        var siblings = $(listItem).siblings();
        if($(listItem).hasClass('highlight')) return;
        siblings.each(function (i) {
            $(this).removeClass('highlight')
        })
        $(listItem).addClass('highlight');
    });


</script>
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/mescroll.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/jquery-2.1.3.min.js')}}"></script>
<script  type="text/javascript">
    wx.config({!! $app->jssdk->buildConfig(['checkJsApi', 'hideAllNonBaseMenuItem'], false) !!});
    wx.ready(function() {
        wx.hideAllNonBaseMenuItem();

    });//end_ready
</script>
<script>
    let mescroll = new MeScroll("body", {
        down: {
            isLock:true  // 锁定下拉功能
        },
        up: {
            callback: upCallback, //上拉加载的回调
            page: {
                num: 0, //当前页 默认0,回调之前会加1; 即callback(page)会从1开始
                size: 10 //每页数据条数,默认10
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

    //下拉刷新的回调
    function downCallback() { }
    //上拉加载的回调
    function upCallback(page) {console.log('上拉加载...')
        let ajaxItemsUrl= "{!!$ajaxItemsUrl!!}"
        let currRdid = '{{$user['rdid']}}';
        $.ajax({
            url: ajaxItemsUrl + '&page=' + page.num + '&rdid=' + currRdid ,
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
        let status = '已违约';
        let statusClass = 'hasCancel';
        if(item.status == 1){
            status = '已签到';
            statusClass = 'hasSign';
        }
        else if(item.status == 2){
            status = '已取消';
            statusClass = 'hasSign';
        }
        else if(item.status == 3){
            status = '已违约';
            statusClass = 'hasCancel';
        }

        let HTMLBank = `<div class="hisLine">
            <div class="hisLineTop">
                <h3 class="roomName">${item.mark }${item.chart.numid}号座</h3>
                <p class="odTime">${item.s_time.slice(0,16)} ~ ${item.e_time.slice(10,16)}</p>
            </div>
            <div class="hisRight">
                <p class="${statusClass}">${status}</p>
            </div>
        </div>`;

        return HTMLBank
    }
</script>
</body>
</html>
