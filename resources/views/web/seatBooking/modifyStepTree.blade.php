<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>开始预约-选择座位</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <script type="text/javascript"src="{{asset('wechatWeb/seatBooking/js/jquery-2.1.3.min.js')}}" charset="utf-8"></script>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/swiper.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/seatBooking.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/seatBookingThree.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/lib/layer-mobile/need/layer.css')}}">
</head>

<body>
<script type="text/javascript">
    //    var loading=layer.open({type: 2,shade: 'background-color: rgba(0,0,0,.9)',shadeClose:false });
</script>
<div class="seatnav">
    <div class="navBox">
        <div class="cBox">
            <div class="a outerC">
                <div class="b innerC">1</div>
            </div>
            <div class="line"></div>
            <div class="d outerC">
                <div class="b innerC">2</div>
            </div>
            <div class="line"></div>
            <div class="d outerC">
                <div class="b innerC">3</div>
            </div>
            <div class="clear"></div>
        </div>
        <div>
            <div class="fR">选择座位</div>
        </div>
        <div class="clear"></div>
    </div>
</div>

<div class="">
    <img id="imgID" src="{{$storage->url($chartData->region->img)}}" style="width: 100%" alt="">
</div>


<div>
    <div class="seat_show">
        <ul>
            <li>
                <i></i>
                <span>可选</span>
            </li>
            <li>
                <i></i>
                <span>已预约</span>
            </li>
            <li>
                <i></i>
                <span>已选</span>
            </li>
            <li>
                <i></i>
                <span>停用</span>
            </li>
            <li>
            <i></i>
            <span>使用中</span>
            </li>
        </ul>
    </div>
    <div class="seat_show_two"></div>
</div>
<div class="seat_choose">
    <div class="number" id="num"></div>
    <div class="seats" id="seats"></div>
</div>
<div style="padding: 1.2rem 0">

</div>
<div class="btBtnArea">
    <div class="stepTowPev" onclick="location.href=backUrl">上一步</div>
    <div class="stepTowNext failure" id="goOK">确认</div>
</div>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/swiper.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/lib/layer-mobile/layer.js')}}"></script>
<!-- 步骤
1,进入选择座位默认小区（ 如无选区直接显示全部座位 ）
2,如有小区，默认选择第一个
3,每个小区配置（行数，列数，属性，区域位置如：门口）
4,切换区域的时候异步加载位置(考虑效率以及性能,缓存)
-->
<script>
    const oldBookingId = "{{$bookingData->id}}"
    const id="{{$chartData->region_id}}";
    const stime="{{request()->input('stime')}}";
    const etime="{{request()->input('etime')}}";
    const backUrl="{!! route('Seat::modifyStepTwo',['token'=>request()->input('token'),'bookingId'=>$bookingData->id]) !!}";
    const oldNumid = "{{$bookingData->chart_id}}";
    const csrf_token = "{{csrf_token()}}";
    var goOK=$('#goOK');

    function initSeat(area_id) {

        var index=layer.open({
            type: 2
            ,content: '加载中',shadeClose:false
        });
        $.ajax({
            type: "get",
            url: "{{route('Seat::initSeat', ['token'=>request()->input('token')])}}"+"&regionId=" + id +"&stime=" + stime +"&etime=" + etime,
            dataType: "json",
            headers: {
                'X-CSRF-TOKEN': csrf_token
            },
            success: function (data) {
                layer.close(index);
                initChart(data);
            },
            error: function () {
                console.log("请求失败！");
            }
        });
    }

    function initChart(data) {
        $('#seats').html('');
        $('.seat_show_two').html('');
        var  Num=0;
        var  heightNum = 6;
        var  widthNum = "{{$chartData->region->cols}}";

        if (data.allAttr){
            var testhtml='';
            testhtml+='<ul>';
            $.each(data.allAttr,function(index,value){
                testhtml+='<li><i style="background-color:'+value.color +' "></i><span>'+value.name+'</span></li>';
            });
            testhtml+='</ul>';
            $('.seat_show_two').html(testhtml);
        }
        if(JSON.stringify(data.list)==='[]'){
            $('#seats').html('<div style="padding-top:2rem;">抱歉，此区域暂无位置开放</div>');
            $('#crossnum').html('');
            $('#num').html('');
            return false;
        }
        var  totleWidth = widthNum * 46;
        heightNum = Math.ceil(data.list.length/widthNum);
        var html = '';
        html += '<ul class="touchs" id="touchs">';
        $.each(data.list,function(index,value){
            var selected = (value.state=="1" && value.id ==oldNumid ? 'selected' : ''); //已经预约
            var selectedTwo = (value.status=="2" ? 'selectedTwo' : ''); //使用中
            var selectedThree = (value.status=="3" ? 'selectedThree' : ''); // 停用
            html += '<li class="' + selected + selectedThree + selectedTwo +'">';
            html += '<div class="test">';
            if(value.attr){
                $.each(value.attr,function(index,value){
                    html += '<i style="background-color: '+ value.color +'"></i>';
                });
            }
            html += '</div>';
            html += '<input type="checkbox" name="seat-' + index + '" id="seat-' + index + '" data-id="'+value.id+'"data-num="'+value.numid +'"/>';
            html += '<label for="seat-' + index + '"></label>';
            html += '<p >' + value.numid + '</p>';
            html += '</li>';

        });
        html += '<div class="the_best"></div><div class="crossnum" id="crossnum"></div></ul>';
        $('#seats').html(html);

        $('.selected').children('input').attr({'disabled': 'disabled', 'checked': 'checked'});
        $('.selectedThree').children('input').attr({'disabled': 'disabled'});
        $('.selectedTwo').children('input').attr({'disabled': 'disabled'});
        $('.seats >ul').css('width', totleWidth + 'px');


        /* 标尺纵轴 */
        var num = '';
        num += '<ul>';
        for (var i = 1; i <= heightNum; i++) {
            num += '<li>' + i + '</li>';
        }
        num += '</ul>';
        $('#num').html(num);

        /* 标尺横轴 */
        var crossnum = '';
        crossnum += '<ul>';
        for (var j = 1; j <= widthNum; j++) {
            crossnum += '<li>' + j + '</li>';
        }
        crossnum += '</ul>';
        $('#crossnum').html(crossnum);
        $('.seats').css('height', parseInt(heightNum*54+160)+'px');
        initEvent(totleWidth,widthNum);

        $('.seats li input').on('click', function () {
            var checkArr=$('.seats li').not('.selected').children('input:checked');
            var checklen = checkArr.length;
            if (checklen <=0){
                goOK.addClass('failure');
                goOK.removeClass('nextUrl');
            }else {
                goOK.addClass('nextUrl');
                goOK.removeClass('failure');
            }
            if ( checklen > 1 ) {
                popu('超过上限，您目前只能修改1个位置');
                return false;
            }

        });


        //默认值
        $('input[data-id="'+oldNumid+'"]').prop('checked',true);
        goOK.addClass('nextUrl');

    }



    //公用弹出层
    function popu(content) {
        layer.open({
            content: content
            , skin: 'msg'
            , time: 3
        });
    }

    function initEvent(totleWidth,widthNum) {
        var flag = false;
        var cur = {
            x: 0,
            y: 0
        }
        var nx, ny, dx, dy, x, y;
        function down() {
            flag = true;
            var touch;
            if (event.touches) {
                touch = event.touches[0];
            } else {
                touch = event;
            }
            cur.x = touch.clientX;
            cur.y = touch.clientY;
            dx = div2.offsetLeft;
            dy = div2.offsetTop;
        }

        function move() {
            if (flag) {
                var touch;
                if (event.touches) {
                    touch = event.touches[0];
                } else {
                    touch = event;
                }
                nx = touch.clientX - cur.x;
                ny = touch.clientY - cur.y;
                x = dx + nx;
                y = dy + ny;
                if (10 > x && x > -(totleWidth/2+widthNum*5) ) {
                    div2.style.left = x + "px";
                }
//                div2.style.top = y +"px";
                //阻止页面的滑动默认事件
                document.addEventListener("touchmove", function () {
//                    event.preventDefault();
                }, false);
            }
        }

        //鼠标释放时候的函数
        function end() {
            flag = false;
        }
        var div2 = document.getElementById("touchs");
        div2.addEventListener("mousedown", function () {
            down();
        }, false);
        div2.addEventListener("touchstart", function () {
            down();
        }, false)
        div2.addEventListener("mousemove", function () {
            move();
        }, false);
        div2.addEventListener("touchmove", function () {
            move();
        }, false)
        document.body.addEventListener("mouseup", function () {
            end();
        }, false);
        div2.addEventListener("touchend", function () {
            end();
        }, false);
    }

    initSeat();


</script>
<script type="text/javascript">

    $(document).on('tap', '.nextUrl', function(){

        event.preventDefault();   // 阻止浏览器默认事件，重要
        var checkArr=$('.seats li').not('.selected').children('input:checked');
        var data=new Array();
        var str='';
        checkArr.each(function (e) {
            data.push($(this).data('id'));
            data.push($(this).data('num'));
            str+=$(this).data('num');

        })

        layer.open({
            content: '您确定要更新预约的 '+str+' 号位置 ?'
            ,btn: ['是的', '取消']
            ,yes: function(index){
                layer.open({
                    type: 2
                    ,content: '预约更新中...',shadeClose:false
                });
                $.ajax({
                    type: "post",
                    url: "{{route('Seat::updateBooking',['token'=>request()->input('token')])}}",
                    data: {"data": data,"id":id,"etime":etime,"stime":stime,"oldBookingId":oldBookingId},
                    dataType: "json",
                    headers: {
                        'X-CSRF-TOKEN': csrf_token
                    },
                    success: function (data, textStatus, jqXHR) {
                        layer.closeAll();
                        layer.open({
                            content: data.message
                            ,skin: 'msg'
                            ,time: 2 //2秒后自动关闭
                        });
                        if (false == data.status) {
                            setTimeout(function(){
                                location.reload();
                            },2000);
                            return true;
                        }
                        setTimeout(function(){
                            location.href="{{route('Seat::index',['token'=>request()->input('token')])}}";
                        },2000);
                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        console.log("请求失败！");
                    }
                });

            }
        });
    });

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
