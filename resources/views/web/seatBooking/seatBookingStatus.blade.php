<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <title>座位信息</title>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/base.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/index.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/lib/layer-mobile/need/layer.css')}}">

    <script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/common.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/seatBooking/lib/layer-mobile/layer.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/jquery-2.1.3.min.js')}}"></script>
    <script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/leftTime.min.js')}}"></script>

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
        .label-red {
            background-color: #fb0703;
            float: right;
            padding: .2rem;
        }
        .label-blue {
            background-color: #2a7efb;
        }
        .draggable {
            width: .6rem;
            height: .6rem;
            background: #000000;
            border-radius: .6rem;
            /* margin: 3px; */
            float: left;
            text-align: center;
            line-height: .6rem;
            /* font-weight: bold; */
            font-size: .4rem;
            color: white;
        }
    </style>
</head>
<body>
    @if($chartData->region->img)
    <div >
        <img id="imgID" src="{!! Storage::disk(config('admin.upload.disk'))->url($chartData->region->img); !!}" style="width: 100%" alt="">
    </div>
    @else
    <header class="siteHeadArea">
        <div class="sysTit">
        </div>
    </header>
    @endif

<div class="myOrderArea pd40"  style="padding-top: .5rem">
<div class="myOrderAreaIn"  >
    <div class="myOrderTop">
            <span class="myOrdTit">座位号 : {{$data->numid}}
            </span>
        @foreach($chartData->attr as $attr)
            <span class="label label-default ma"> {{$attr->name}}</span>
        @endforeach
    </div>
    <div class="myOrderTop">
            <span class="myOrdTit">所属区域 : {{$chartData->region->name}}&nbsp;
            </span>
    </div>
    <div class="myOrderTop">
        <span class="myOrdTit">状态 : </span><span class="label label-success ma"> 已预约 </span>
        @if($data->status == 1)
            &nbsp;<span class="label label-default ma"> 已签到 </span>
        @else
            <else/>
            <a class="label label-blue ma" href="{!! route('Seat::modifyStepTwo',['token'=>request()->input('token'),'bookingId'=>$data->id]) !!}"> 修改 </a>
        </if>
        @endif
        <span class="label label-red ma " onclick="cancel()"> 点击取消 </span>
    </div>
    <div class="myOrderTop">
        <span class="myOrdTit">预约时间 : </span>{{substr($data->s_time,0,11)}}&nbsp;{{substr($data->s_time,11,5)}}~{{substr($data->e_time,11,5)}}
    </div>
    <div class="myOrderTop">
        <span class="myOrdTit">签到时间 : </span>{{$data->allowTime}}
    </div>
    <div class="myOrderTop">
        <span  class="myOrdTit">座位现预约人数 : &nbsp; {{$allBookingNum}}  &nbsp;
        </span>
    </div>
    <div class="myOrderTop">
        <span  class="myOrdTit">全部被预约时间段 :
            @foreach($allBooking as $booking)
               <span style="display: block;text-align: right">{{date('Y-m-d',strtotime($booking['s_time']))}}&nbsp; {{substr($data->s_time,11,5)}}~{{substr($data->e_time,11,5)}} </span>
            @endforeach
        </span>
    </div>
</div>
</div>

<script type="text/javascript">
    const uid="{{$data->rdid}}";
    const booking_id="{{$data->id}}";
    const csrf_token = "{{csrf_token()}}";

    function cancel() {
        layer.open({
            content: '您确定要取消预约吗？'
            ,btn: ['是的', '不要']
            ,yes: function(index){
                var a =  layer.open({type: 2});
                $.ajax({
                    type: "post",
                    url: "{{route('Seat::cancelBooking',['token'=>request()->input('token')])}}",
                    data: {"id": booking_id,"uid":uid},
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': csrf_token
                    },
                    success: function (data) {
                        layer.close(a);
                        layer.open({
                            content: data.message
                            ,skin: 'msg'
                            ,time: 2 //2秒后自动关闭
                        });
                        if (true == data.status) {
                            setTimeout(function () {
                                location.href="{{route('Seat::index',['token'=>request()->input('token')])}}";
                                return true;
                            },2000);
                        }
//                return false;
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("请求失败！");
                    }
                });
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


</body>
</html>
