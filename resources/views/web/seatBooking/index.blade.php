<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>读者在线预约系统</title>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/base.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/index.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/lib/layer-mobile/need/layer.css')}}">
</head>
<style>
    .label {
        display: inline;
        padding: .2em .6em .3em;
        font-size: 100%;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: .25em;
    }
    .label-success {
        background-color: #5cb85c;
    }
    .fieldList li {
        margin-right: .32rem;
    }
    .label-blue {
        background-color: #2a7efb;
    }
</style>
<body>
<header class="siteHeadArea">
    <div class="sysTit">
        <h1>{{$wxUser->wxname}}</h1>
        <p class="fnName">在线订位</p>
    </div>
    <a  href="javascript:;" onclick="check()"  class="orderBtn">开始预约</a>
</header>
<div class="integralArea pd40">
    <div class="integralNum">
        <img src="{{$user['headimgurl']}}" alt="头像" class="headimg">
        <span>{{$user['nickname']}}</span>
    </div>
</div>
<div class="myOrderArea pd40">
    <div class="myOrderAreaIn ">
        <div class="myOrderTop">
            <span class="myOrdTit">我的座位</span>
            <a href="{{route('Seat::seatLogList',['token'=>$wxUser->token])}}" class="chekOrderLog">查看入座记录</a>
        </div>
        @if($mySeat->count() == 0 && $queue->count() == 0)
            <div class="myOrderBot"> 暂无,赶紧扫码入座吧! </div>
        @else
        @foreach($mySeat as $v)
            <div class="myOrderBot myseatUrl" data-seatid="{{$v->id}}" data-sn="{{md5($v->id.'2019')}}">
                <h3 class="roomName">{{$v->region->name}} &nbsp;&nbsp;&nbsp;{{$v->numid}} 号座&nbsp;</h3>
                <span class=" odTime label label-blue " >就座中</span>
                <i class="rightIcon"></i>
            </div>
        @endforeach

        @foreach($queue as $que)
            <div class="myOrderBot myseatUrl" data-seatid="{{$que->chart_id}}" >
                <h3 class="roomName">{{$que->region}} &nbsp;&nbsp;&nbsp;{{$que->numid}}&nbsp;号座位</h3>
                @if(strtotime($que->u_time) < time())
                    <span class=" odTime label label-blue "  >可入座</span>
                @else
                    <span class=" odTime label label-success sub" data-time="{{$que->u_time}}"  >排队中&nbsp;&nbsp;&nbsp;<span class="m"></span> :<span class="s"></span></span>
                @endif
                <i class="rightIcon"></i>
            </div>
        @endforeach
        @endif


    </div>
</div>
<div class="myOrderArea pd40">
    <div class="myOrderAreaIn ">
        <div class="myOrderTop">
            <span class="myOrdTit">我的预约</span>
            <a href="{{route('Seat::seatBookingLog',['token'=>$wxUser->token])}}" class="chekOrderLog">查看预约记录</a>
        </div>
        @if($myBooking->first())
            @foreach($myBooking as $va)
                <div class="myOrderBot myBookingUrl" data-seatid="{{$va['id']}} ">
                    <h3 class="roomName">{{$va['mark']}}&nbsp;&nbsp;{{$va['chart']['numid']}}号座
                    </h3>
                    @if($va['status'] == 0 && date('Y-m-d H:i:s')>=$va['sign_min'] && date('Y-m-d H:i:s')<=$va['sign_max'])
                        <span class=" odTime label label-success "  >可签到</span>
                    @elseif($va['status'] == 1)
                        <span class=" odTime label label-blue "  >已签到</span>
                    @endif

                    <p class="odTime"><span> {{substr($va['s_time'],0,16)}} ~ {{substr($va['e_time'],11,5)}}</span></p>
                    <i class="roomIcon"></i>
                    <i class="rightIcon"></i>
                </div>
            @endforeach
        @else
            <div class="myOrderBot"> 暂无预约 </div>
        @endif
    </div>
</div>
@if($hotBooking->first())
    <div class="hotOrderArea">
        <div class="hotOrderAreaIn">
            <p class="hotOrderField">热门预约地</p>
            <ul class="fieldList">
                @foreach($hotBooking as $vc)
                    <li onclick="hotUrl($(this))" data-id="{{$vc->id}}">
                        <a class="myOrderBot fieldLink">
                            <h3 class="roomName">{{$vc->name}}</h3>
                            <p class="odTime"><span>&nbsp;</span>&nbsp;<span>&nbsp;{{$vc->remarks}}</span></p>
                            <i class="rightIcon gray"></i>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
<div style="padding-bottom: 20px"></div>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/common.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/lib/layer-mobile/layer.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/jquery-2.1.3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/leftTime.min.js')}}"></script>
<script type="text/javascript">
    const status = "{{intval($globalConfig->status)}}"; //系统总开关
    const booking_switch = "{{intval($globalConfig->booking_switch)}}"; //系统总预约开关
    const violations = "{{$user['violations']}}";       //用户违规次数
    const violate_num = "{{$globalConfig->violate_num}}";    //违规次数上限
    const forbidDay_day="{{$user['forbidden']}}";
    function check() {

        if(status != 1 || booking_switch != 1){
            layer.open({
                content: '系统暂未开放！'
                ,btn: '我知道了'
            });
            return;
        }
        if(parseInt(violations) >= parseInt(violate_num)){
            layer.open({
                content: '您的违规次数已达上限，请 <span style="color: #fb0703">'+forbidDay_day+'</span> 日之后再来吧'
                ,btn: '我知道了'
            });
            return;
        }
        window.location.href="{{route('Seat::startBooking',['token'=>$wxUser->token])}}";
    }
    $('.myBookingUrl').click(function (e) {
        window.location.href="{{route('Seat::seatBookingStatus',['token'=>request()->input('token')])}}"+'&id='+$(this).data('seatid');
    });
    $('.myseatUrl').click(function (e) {
        window.location.href="{{route('Seat::chartStatus',['token'=>request()->input('token')])}}"+'&id='+$(this).data('seatid')+'&sn='+$(this).data('sn');
    });

    function hotUrl(e) {
        window.location.href="{{route('Seat::startBookingTwo',['token'=>request()->input('token')])}}"+'&id='+e.data('id');

    }

</script>
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script  type="text/javascript">
    wx.config({!! $app->jssdk->buildConfig(['checkJsApi', 'hideAllNonBaseMenuItem'], false) !!});
    wx.ready(function() {
        wx.hideAllNonBaseMenuItem();

    });//end_ready
</script>
<script type="text/javascript">
    var timeData=$('.sub');
    if (timeData.length > 0){
        timeData.each(function () {
            var a=$(this);
            $.leftTime(a.data('time'),function(d){
                if(d.status){
                    var $dateShow1=a;
                    $dateShow1.find(".m").html(d.m);
                    $dateShow1.find(".s").html(d.s);
                }
            });
        })
    }
</script>
</body>
</html>
