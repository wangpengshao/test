<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>开始预约-选择场地</title>
    <meta name="viewport" content="width=device-width,initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/swiper.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/seatBooking.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/lib/layer-mobile/need/layer.css')}}">
</head>
<body>
<div class="seatnav">
    <div class="navBox">
        <div class="cBox">
            <div class="a outerC">
                <div class="b innerC">1</div>
            </div>
            <div class="line"></div>
            <div class="d outerC">
                <div class="e innerC">2</div>
            </div>
            <div class="line"></div>
            <div class="d outerC">
                <div class="e innerC">3</div>
            </div>
            <div class="clear"></div>
        </div>
        <div>
            <div class="fL">选择区域</div>
        </div>
        <div class="clear"></div>
    </div>
</div>


<div class="container">
    <div class="swiper-container swiper1">
        <div class="swiper-wrapper">
            @foreach($level1 as $value)
            <div class='swiper-slide slide-width  @if ($loop->first) selected @endif'>
                {{$value->name}}
            </div>
            @endforeach
        </div>
    </div>

    <div class="swiper-container swiper2">
        <div class="swiper-wrapper">
            @foreach($level1 as $lv1)
                <div class="swiper-slide swiper-no-swiping">
                    <div class="hotBooking">
                        <ul>
                            @foreach($level2 as $lv2)
                                @if ($lv2->pid == $lv1->id)
                                    <li style="display: block">
                                        <a href="javascript:void(0)" data-url="{{route('Seat::startBookingTwo',['token'=>request()->input('token'), 'id'=>$lv2->id])}}" data-booking="{{$lv2->booking_switch}}" onclick="checkStatus(this)">
                                            <div>
                                                {{$lv2->name}}</br>
                                                <span style="color: #727272;"> &nbsp;{{$lv2->remarks}}</span>
                                                <span style="float: right;margin-top: -.5em;color: #727272;margin-right: .2em">&gt;</span>
                                            </div>
                                        </a>
                                    </li>
                                @endif
                                </if>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/jquery-2.1.3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/swiper.js')}}"></script>
<script>
    $(function () {
        function setCurrentSlide(ele, index) {
            $(".swiper1 .swiper-slide").removeClass("selected");
            ele.addClass("selected");
            //swiper1.initialSlide=index;
        }

        var swiper1 = new Swiper('.swiper1', {
               // 设置slider容器能够同时显示的slides数量(carousel模式)。
               // 可以设置为number或者 'auto'则自动根据slides的宽度来设定数量。
               // loop模式下如果设置为'auto'还需要设置另外一个参数loopedSlides。
                slidesPerView: 4.5,
                paginationClickable: true,//此参数设置为true时，点击分页器的指示点分页器会控制Swiper切换。
                spaceBetween: 10,//slide之间的距离（单位px）。
                freeMode: true,//默认为false，普通模式：slide滑动时只滑动一格，并自动贴合wrapper，设置为true则变为free模式，slide会根据惯性滑动且不会贴合。
                loop: false,//是否可循环
                onTab: function (swiper) {
                    var n = swiper1.clickedIndex;
                }
            });
            swiper1.slides.each(function (index, val) {
                var ele = $(this);
                ele.on("click", function () {
                    setCurrentSlide(ele, index);
                    swiper2.slideTo(index, 500, false);
                    //mySwiper.initialSlide=index;
                });
            });

        var swiper2 = new Swiper('.swiper2', {
            direction: 'horizontal',//Slides的滑动方向，可设置水平(horizontal)或垂直(vertical)。
            loop: false,
            autoHeight: true,//自动高度。设置为true时，wrapper和container会随着当前slide的高度而发生变化。
            onSlideChangeEnd: function (swiper) {  //回调函数，swiper从一个slide过渡到另一个slide结束时执行。
                var n = swiper.activeIndex;
                setCurrentSlide($(".swiper1 .swiper-slide").eq(n), n);
                swiper1.slideTo(n, 500, false);
            }
        });
    });

    function checkStatus(obj) {
        var url = $(obj).data('url');
        var is_booking = $(obj).data('booking');

        if(is_booking != 1){
            layer.open({
                content: '当前区域暂未开放预约！'
                ,btn: '我知道了'
            });
            return;
        }
        location.href = url;
    }

</script>
</body>
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/lib/layer-mobile/layer.js')}}"></script>
<script  type="text/javascript">
    wx.config({!! $app->jssdk->buildConfig(['checkJsApi', 'hideAllNonBaseMenuItem'], false) !!});
    wx.ready(function() {
        wx.hideAllNonBaseMenuItem();

    });//end_ready
</script>

</html>
