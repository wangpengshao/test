<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>扫码签到</title>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/lib/layer-mobile/need/layer.css')}}">
</head>
<body>
<div  class="box">
    {{--@if($reponse['status'] == 0)--}}
        {{--<img src="{{asset('wechatWeb/seatBooking/images/signed.png')}}" alt="" width="100">--}}
        {{--<p class="notice">{{$reponse['message']}}</p>--}}
    {{--@else--}}
        {{--<img src="{{asset('wechatWeb/seatBooking/images/signed.png')}}" alt="" width="100">--}}
        {{--<p class="notice">{{$reponse['message']}}</p>--}}
    {{--@endif--}}
    {{--<div>--}}
        {{--<a href="{{route('Seat::index',['token'=>request()->input('token')])}}">返回首页</a>--}}
    {{--</div>--}}
</div>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/jquery-2.1.3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/lib/layer-mobile/layer.js')}}"></script>
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
@if($status)
    <script  type="text/javascript">
        var indexUrl = "{{route('Seat::index',['token'=>request()->input('token')])}}";
        var index = layer.open({
            type: 2
            ,content: '签到处理中. . .'
        });

        wx.config({!! $app->jssdk->buildConfig(['checkJsApi', 'hideAllNonBaseMenuItem', 'getLocation'], false) !!});
        wx.error(function(res) {
            alert("出错了：" + res.errMsg);
        });
        wx.ready(function() {
            wx.checkJsApi({
                jsApiList : ['scanQRCode','getLocation', 'hideAllNonBaseMenuItem'],
                success : function(res) {}
            });

            wx.hideAllNonBaseMenuItem()

            wx.getLocation({
                type: 'gcj02',
                success: function (res) {
                    var latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                    var longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                    var speed = res.speed; // 速度，以米/每秒计
                    var accuracy = res.accuracy; // 位置精度

                    $.ajax({
                        type: "post",
                        url: "{{route('Seat::bookingAttendance',['token'=>request()->input('token')])}}",
                        data: {"lat": latitude,"lng":longitude},
                        dataType: "json",
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (data, textStatus, jqXHR) {
                            layer.closeAll();
                            layer.open({
                                content: data.message
                                ,btn: '返回首页',yes: function(index){
                                    location.href = indexUrl;
                                    layer.closeAll(index);
                                }
                            });
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            layer.closeAll();
                            layer.open({
                                content: '签到失败！'
                                ,btn: '返回首页',yes: function(index){
                                    location.href = indexUrl;
                                    layer.closeAll(index);
                                }
                            });
                            console.log("请求失败！");
                        }
                    });
                },
                error: function () {
                    layer.closeAll();
                    layer.open({
                        content: '定位失败！'
                        ,btn: '返回首页',yes: function(index){
                            location.href = indexUrl;
                            layer.closeAll(index);
                        }
                    });
                    console.log('获取失败！')
                }
            });

        });//end_ready

    </script>
@else
    <script>
        var indexUrl = "{{route('Seat::index',['token'=>request()->input('token')])}}";
        layer.open({
            content: "{{$message}}"
            ,btn: '返回首页',yes: function(index){
                location.href = indexUrl;
                layer.closeAll(index);
            }
        });
    </script>
@endif

</body>
</html>
