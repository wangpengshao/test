<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>开始预约-选择预约时间</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/css/seatBooking.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/lib/layer-mobile/need/layer.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/seatBooking/lib/laydate/theme/default/laydate.css')}}">
    <style>
        html{
            position: fixed;
            top: 0;
            width: 100%;
        }
        body {
            color: #666666;
        }
        .diyinput {
            border: 1px solid #d3d3d3;
            border-radius: .7rem;
            font-size: .7rem;
            padding: .8rem 0;
            text-align: center;
            box-shadow: 1px 1px 2px #ababab;
        }

        .processMin {
            /*display: block;*/
            color: #666666;
            font-weight: bold;
            width: 20%;
            border: 1px solid #d3d3d3;
            border-radius: .7rem;
            font-size: .8rem;
            padding: .5rem 0;
            box-shadow: 1px 1px 2px #ababab;
            z-index: 9999;
        }


    </style>
</head>
<body style="height: 100%;">
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
                <div class="e innerC">3</div>
            </div>
            <div class="clear"></div>
        </div>
        <div>
            <div class="fc">选择使用时间</div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<div style="text-align: center;margin-top: 1rem;">
    <div id="test2"></div>
</div>
<input type="text" style="display: none" id="ymd">
<div style="text-align: center;">
    <span style="display: block;padding: .6rem;"></span>
    <div class="layui-input-inline">
        <input readonly type="text" class="layui-input diyinput" id="test-limit1" placeholder="开始时间" lay-key="1">&nbsp
        <span style="color:#d3d3d3; ">~</span> &nbsp
        <input readonly type="text" class="layui-input diyinput" id="test-limit2" placeholder="结束时间" lay-key="2">
    </div>
    <span style="display: block;padding: .6rem;"></span>
    <div>预约时间&nbsp&nbsp <span id="testView"></span></div>
</div>


<div class="clear"></div>
<!--<style></style>-->
<div class="btBtnArea">
    <div class="stepTowPev" onclick="location.href=backUrl">上一步</div>
    <div class="stepTowNext" id="goThree">下一步</div>
</div>
<!--<span style="display: block;padding: 5rem;"></span>-->
<!--<span style="display: block;padding: 1rem;"></span>-->
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/js/jquery-2.1.3.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/lib/laydate/laydate.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/seatBooking/lib/layer-mobile/layer.js')}}"></script>
</body>
<script type="text/javascript">
    var datastr;
    var startD;
    var endD;
    var next=$('#goThree');
    const backUrl="{!! route('Seat::startBooking',['token'=>request()->input('token')]) !!}";
    const minStatus="{{$region->s_time}}";
    const maxStatus="{{$region->e_time}}";
    const longDay="{{$globalConfig->day_t}}";
    const minH="{{$timeConfig['minH']}}";
    const minM="{{$timeConfig['minM']}}";
    const maxH="{{$timeConfig['maxH']}}";
    const maxM="{{$timeConfig['maxM']}}";

    var dataA = laydate.render({
        elem: '#test-limit1'
        , type: 'time'
        , min: minStatus
        , max: maxStatus
        , btns: ['clear', 'confirm'], done: function (value, date, endDate) {
            $('#test-limit2').val('');
            next.addClass('failure');
            next.removeClass('nextUrl');
            var dataMin=$.extend(true,{},date);
            var dataMax=$.extend(true,{},date);
            dataMin.hours += parseInt(minH);
            dataMin.minutes += parseInt(minM);
            if( dataMin.minutes >= 60){
                dataMin.hours +=Math.floor(dataMin.minutes/60);
                dataMin.minutes=(dataMin.minutes%60);
            }
            dataMax.hours += parseInt(maxH);
            dataMax.minutes += parseInt(maxM);
            if( dataMin.minutes >= 60){
                dataMax.hours +=Math.floor(dataMax.minutes/60);
                dataMax.minutes=(dataMax.minutes%60);
            }
            startD=date.hours+'时'+date.minutes+'分';
            endD=null;
            dataB.config.min.hours = dataMin.hours;
            dataB.config.min.minutes = dataMin.minutes;
            dataB.config.max.hours = dataMax.hours;
            dataB.config.max.minutes = dataMax.minutes;
            lay('#testView').html(datastr);
        }
    });
    var dataB = laydate.render({
        elem: '#test-limit2'
        , type: 'time'
        , min: minStatus
        , max: minStatus
        , btns: ['clear', 'confirm'], done: function (value, date, endDate) {
            if(value){
                endD=date.hours+'时'+date.minutes+'分';
                var str=startD+' 至 '+endD;
                lay('#testView').html(datastr + ' ' + str);
                next.removeClass('failure');
                next.addClass('nextUrl');
                return;
            }
            next.addClass('failure');
            next.removeClass('nextUrl');
            lay('#testView').html(datastr);
        },ready:function (data) {
            if($('#test-limit1').val()==''){
                dataB.hint('请先选择开始时间'); //在控件上弹出value值
                return;
            }
        }
    });
    laydate.render({
        elem: '#test2'
        , position: 'static'
        , min: 0
        , max: parseInt(longDay), btns: ['confirm'], showBottom: false
        , change: function (value, date) { //监听日期被切换
            intChangStr(date)
        }, done: function (value, date, endDate) {
        }, ready: function (date) {
            intChangStr(date);
        } ,mark: {
            '2017-11-11': '活动'
        }
    });

    //初始化
    function intChangStr(date) {console.log(date)
        $('#ymd').val(date.year+'-'+date.month+'-'+date.date);
        var year = date.year;
        var month = date.month;
        var day = date.date;
        datastr = year + '年' + month + '月' + day + '日';
        lay('#testView').html(datastr);
        dataA.config.min.hours = parseInt(minStatus.slice(0,2));
        dataA.config.min.minutes = parseInt(minStatus.slice(3,5));

        //当前日期时间
        var dateObj = new Date();
        var currY = dateObj .getFullYear();
        var currM = dateObj .getMonth() + 1;
        var currD = dateObj .getDate();
        var currH = dateObj .getHours();
        var currI = dateObj .getMinutes();
        if(day == currD){
            dataA.config.min.hours = currH;
            dataA.config.min.minutes = currI;
        }

        $('#test-limit2').val('');
        $('#test-limit1').val('');
        next.addClass('failure');
        next.removeClass('nextUrl');
    }

    $(document).on("touchstart", function(e) {
        if(!$(e.target).hasClass("disable")) $(e.target).data("isMoved", 0);
    });
    $(document).on("touchmove", function(e) {
        if(!$(e.target).hasClass("disable")) $(e.target).data("isMoved", 1);
    });
    $(document).on("touchend", function(e) {
        if(!$(e.target).hasClass("disable") && $(e.target).data("isMoved") == 0) $(e.target).trigger("tap");
    });

    $(document).on('tap', '.nextUrl', function(){
        event.preventDefault();   // 阻止浏览器默认事件，重要
        var sTime = newDate($('#ymd').val()+' '+$('#test-limit1').val());
        var eTime = newDate($('#ymd').val()+' '+$('#test-limit2').val());
        var Stime = Date.parse(new Date(sTime))/1000;
        var Etime = Date.parse(new Date(eTime))/1000;
        if (Stime - Date.parse(new Date())/1000 < 600){
            layer.open({
                content: '抱歉，不能预约已过去的时间哦 !'
                ,btn: '我知道了'
            });
            return false;
        }
        location.href="{!! route('Seat::startBookingThree', ['token'=>request()->input('token'),'id'=>request()->input('id')])!!}"+'&stime='+Stime+'&etime='+Etime;
    });

    function newDate(strdate) {
        var arr = strdate.split(/[- : \/]/);
        var date = new Date(arr[0], arr[1]-1, arr[2], arr[3], arr[4], arr[5]);
        return date;
    }
</script>
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script  type="text/javascript">
    wx.config({!! $app->jssdk->buildConfig(['checkJsApi', 'hideAllNonBaseMenuItem'], false) !!});
    wx.ready(function() {
        wx.hideAllNonBaseMenuItem();

    });//end_ready

</script>
</html>
