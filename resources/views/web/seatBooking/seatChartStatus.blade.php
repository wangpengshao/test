<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>座位信息</title>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/base.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/index.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/lib/layer-mobile/need/layer.css')}}">

    <script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/jquery-2.1.3.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/leftTime.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/common.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/seatBooking/lib/layer-mobile/layer.js')}}"></script>
    <style>
        .label {
            display: inline;
            padding: .2em .6em .3em;
            font-size: 75%;
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
        .label-default {
            background-color: #777;
        }
        .label-blue {
            background-color: #2a7efb;
        }
        .ma{
            margin: 0 .1rem;
        }
        .headimg{
            width: .8rem;height: .8rem;border-radius: .8rem;margin-left: 10px;margin-top: -.1rem
        }
        .sub{
            float: right;padding: .2rem
        }
        .sub2{
            float: right;padding: .2rem .4rem;
        }
    </style>
</head>
<body>
<header class="siteHeadArea">
    <div class="sysTit">
        <h1>{{$wxUser->wxname}}</h1>
    </div>
</header>
<div class="myOrderArea pd40">
<div class="myOrderAreaIn"  >
    <div class="myOrderTop">
            <span class="myOrdTit">座位号 : {{$data['numid']}}</span>
            @if($data['status'] == 2)
                @if($data['seating_type'] == 1)
                <span class="label label-success sub" id="dateShow2" style="display: none;" >剩余时长：<span class="h"></span> :<span class="m"></span> :<span class="s"></span></span>
                @endif
            @endif
        @foreach($data['attr'] as $v)
            <span class="label label-default ma"> {{$v['name']}} </span>
        @endforeach
    </div>
    <div class="myOrderTop">
            <span class="myOrdTit">所属区域 : {{$data['region']['name']}}&nbsp;</span>
    </div>
    @empty($data['usetime'])

    @else
        <div class="myOrderTop">
            <span class="myOrdTit">入坐时间 :
                    {{date('Y-m-d H:i:s',$data['usetime'])}}
            </span>
        </div>
    @endempty


    <div class="myOrderTop">
        <span class="myOrdTit">状态 :
            @if($data['status'] == 1)
                <span class="label label-default"> 空位 </span>
                <span class="label label-success sub" onclick="ruzuo()">点击入座</span>
            @elseif($data['status'] == 3)
                <span class="label label-default"> 座位暂停使用 </span>
            @else
                <img src="{{$data['headimgurl']}}" class="headimg" >&nbsp;
                <span>{{$data['nickname']}}</span>&nbsp; &nbsp;

                @if($data['seating_type'] == 1)
                    <span class="label label-success">预约使用中</span>

                @else
                    <span class="label label-success">使用中</span>
                @endif

                @if($data['curr_user'] == $user['rdid'])
                    @if($data['queueNum'])
                        <span class="label label-blue sub2" onclick="cleanQueque()"  >   清空队列 </span>
                    @else
                        @if($data['seating_type'] == 1)
                           <span class="label label-success sub2" onclick="likai(1, {{$data['seated_id']}})"  >   离座 </span>
                            @else
                            <span class="label label-success sub2" onclick="likai(2, {{$data['seated_id']}})"  >   离座 </span>
                        @endif
                    @endif
                @else
                    @empty($data['queue_id'])
                        <span class="label label-success sub" onclick="queue()" >排队入座 </span>
                    @else
                        @if($data['countdown'] == 1)
                            <span class="label label-success sub" onclick="ruzuo()" >   可入座 </span>
                        @else
                            <span class="label label-default sub" id="dateShow1" style="display: none;"><span class="m"></span> :<span class="s"></span></span>
                        @endif
                    @endempty
                @endif

            @endif
        </span>
    </div>
    @if($data['queueNum'])
        <div class="myOrderTop">
            <span  class="myOrdTit">目前排队人数 : &nbsp; {{$data['queueNum']}}  &nbsp;
            </span>
        </div>
    @endif

    @if($allbooking->first() or $currbooking->first())
        <div class="myOrderTop">
            <span  class="myOrdTit">今天已被预约时间段 :
                @isset($currbooking)
                    @foreach($currbooking as $cv)
                        <div style="overflow: hidden"><span style="float: left;color: green;">{{substr($cv->rdid,0,4)}}****{{substr($cv->rdid,-1)}}</span><span style="float: right">{{substr($cv->s_time,11,5)}} ~ {{substr($cv->s_time,11,5)}}</span></div>
                    @endforeach
                @endisset
                @foreach($allbooking as $v)
                    <div style="overflow: hidden"><span style="float: left;color: green;">{{substr($v->rdid,0,4)}}****{{substr($v->rdid,-1)}}</span><span style="float: right">{{substr($v->s_time,11,5)}} ~ {{substr($v->e_time,11,5)}}</span></div>
                @endforeach
            </span>
        </div>
    @endif
</div>
</div>

<script type="text/javascript">
    const status="{{$data['status']}}";
    const dateSwitch="{{$data['countdown']}}";
    const uid="{{$user['rdid']}}";
    const seat_id="{{$data['id']}}";
    const numid="{{$data['numid']}}";
    const old_user="{{$data['rdid']}}";

    function ruzuo() {
        layer.open({
            content: '您确定要入座吗？'
            ,btn: ['是的', '不要']
            ,yes: function(index){
                var a =  layer.open({type: 2});
                $.ajax({
                    type: "post",
                    url: "{{route('Seat::seatUseAjax',['token'=>request()->input('token')])}}",
                    data: {"id": seat_id,"uid":uid},
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data, textStatus, jqXHR) {
                        layer.close(a);
                        layer.open({
                            content: data.message
                            ,skin: 'msg'
                            ,time: 2 //2秒后自动关闭
                        });
                        if (true == data.status) {
                            setTimeout(function(){
                                location.reload();
                                return true;
                            },2000)
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("请求失败！");
                    }
                });
            }
        });

    }
    function likai(type, downLog) {
        let indexUrl = "{{route('Seat::index', ['token'=>request()->input('token')])}}"
        layer.open({
            content: '您确定要离开座位吗 ？'
            ,btn: ['是的', '点错了']
            ,yes: function(index){
                var a = layer.open({type: 2});
                $.ajax({
                    type: "post",
                    url: "{{route('Seat::seatLogoffAjax',['token'=>request()->input('token')])}}",
                    data: {"id": seat_id,"uid":uid, "type":type, "downLog":downLog},
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data, textStatus, jqXHR) {
                        layer.close(a);
                        layer.open({
                            content: data.message
                            ,skin: 'msg'
                            ,time: 2 //2秒后自动关闭
                        });
                        if (true == data.status) {
                            setTimeout(function(){
                                location.href = indexUrl;
                                return true;
                            },2000)
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("请求失败！");
                    }
                });
            }
        });


    }
    function queue(){
        layer.open({
            content: '您确定要排队入座吗？'
            ,btn: ['是的', '不要']
            ,shadeClose:false
            ,yes: function(index){
                var a =  layer.open({type: 2});
                $.ajax({
                    type: "post",
                    url: "{{route('Seat::seatQueue',['token'=>request()->input('token')])}}",
                    data: {"id": seat_id,"numid":numid,"uid":uid, "old_user":old_user},
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data, textStatus, jqXHR) {
                        layer.close(a);
                        layer.open({
                            content: data.message
                            ,skin: 'msg'
                            ,time: 2 //2秒后自动关闭
                        });
                        if (true == data.status) {
                            setTimeout(function(){
                                location.reload();
                                return true;
                            },2000)
                        }
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("请求失败！");
                    }
                });
            }
        });
    }
    function cleanQueque() {
        var a = layer.open({type: 2});
        $.ajax({
            type: "post",
            url: "{{route('Seat::seatFanhuiAjax',['token'=>request()->token])}}",
            data: {"id": seat_id,"uid":uid},
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (data, textStatus, jqXHR) {
                layer.close(a);
                layer.open({
                    content: data.message
                    ,skin: 'msg'
                    ,time: 2 //2秒后自动关闭
                });

                setTimeout(function(){
                    location.reload();
                    return true;
                },2000)

            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log("请求失败！");
            }
        });
    }

    function startTimekeeping() {
        var a = layer.open({type: 2});
        $.ajax({
            type: "post",
            url: "{weimicms::U('SeatIndex/startTime',array('token'=>$_GET['token']))}",
            data: {"id": seat_id,"uid":uid,"old_user":old_user},
            dataType: "json",
            success: function (data, textStatus, jqXHR) {
                layer.close(a);
                layer.open({
                    content: data.message
                    ,skin: 'msg'
                    ,time: 2 //2秒后自动关闭
                });
                if (true == data.flag) {
                    location.reload();
                    return true;
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log("请求失败！");
            }
        });
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
    $(function(){
        //正常占座倒计时
        if (dateSwitch && dateSwitch !== 1){
            $.leftTime(dateSwitch,function(d){console.log(d)
                if(d.status){
                    var $dateShow1=$("#dateShow1");
                    $dateShow1.css('display','inline');
                    $dateShow1.find(".m").html(d.m);
                    $dateShow1.find(".s").html(d.s);
                    if(d.m=="00" && d.s=="00"){console.log(22)
                        // setTimeout(function () {
                            window.location.reload();
                        // },2000);
                    }
                }

            });
        }
        //是否预约，开始预约倒计时
        @if($data['status'] == 2 && $data['seating_type'] == 1 && $downLog['e_time'])
        const booking_end = "{{$downLog['e_time']}}";
        $.leftTime(booking_end,function(d){console.log(d)
            if(d.status){
                var $dateShow1=$("#dateShow2");
                $dateShow1.css('display','inline');
                $dateShow1.find(".h").html(d.h);
                $dateShow1.find(".m").html(d.m);
                $dateShow1.find(".s").html(d.s);
                if(d.m=="00" && d.s=="00" && d.h=="00"){
                    setTimeout(function () {
                        bookingLizuo()
                    },2000);
                }
            }
            else{
                bookingLizuo()
            }
        });
        @endif

        if(status=="3"){
            layer.open({
                content: '抱歉，此座位已暂停使用，请移步到别的座位！'
                ,btn: '我知道了',shadeClose:false,yes: function(index){
                    location.href="{{route('Seat::index',['token'=>request()->input('token')])}}";
                }
            });
        }
        function bookingLizuo(){
            $.ajax({
                type: "post",
                url: "{{route('Seat::seatLogoffAjax',['token'=>request()->input('token')])}}",
                data: {"id": seat_id,"uid":uid, "type":1, "downLog":{{$data['seated_id']}}},
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (data, textStatus, jqXHR) {
                    if (true == data.status) location.reload()
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    console.log("请求失败！");
                }
            });
        }


    });
</script>
</body>
</html>
