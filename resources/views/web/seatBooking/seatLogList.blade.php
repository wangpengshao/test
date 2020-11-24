<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>入座记录</title>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/base.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/index.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/mescroll.min.css')}}" />
</head>
<body class="hasBgColor">
<header class="logHead">
    <span class="inteTit" style="vertical-align: middle;color: #fff;font-size: .54rem;">入座记录</span>
    <span class="inteNum" style="vertical-align: middle;color: #fff;"></span>
</header>
<ul class="logList" id="mescroll">

</ul>
</body>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/common.js')}}"></script>
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
        let HTMLBank = `<li>
                            <span>${item.mark}</span>
                            <span style="float: right;display: block;">${item.s_time}</span>
                        </li>`;

        return HTMLBank
    }
</script>
</html>
