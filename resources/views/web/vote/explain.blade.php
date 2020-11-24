@extends('web.vote.app')
@section('content')
    @include('web.vote.header')
{{--    <img class="bg" src="{{$templatePath.'/img/mw_005.jpg'}}">--}}
    <div class="blank20"></div>
{{--    <div class="blank20"></div>--}}
    <section class="rules" style="margin-top: -20px;">
        @isset($config['explain_a'])
            <div class="text">
                <div class="prize">{{$config['explain_a']}}</div>
                <div class="neirong">{!! $config['explain_at'] !!}</div>
            </div>
        @endisset
        @isset($config['explain_b'])
            <div class="text">
                <div class="prize">{{$config['explain_b']}}</div>
                <div class="neirong">{!! $config['explain_bt'] !!}</div>
            </div>
        @endisset
        @isset($config['explain_c'])
            <div class="text">
                <div class="prize">{{$config['explain_c']}}</div>
                <div class="neirong">{!! $config['explain_ct'] !!}</div>
            </div>
        @endisset
        <div style=" height:60px; width:100%; display:block;"></div>
    </section>

@endsection

@section('jsResources')
    <script type="text/javascript" src="{{asset('wechatWeb/vote/js/yxMobileSlider.js')}}"></script>
    <script>
        let sliderHeight = "{{$config['img_height']}}";
        $(".slider").yxMobileSlider({
            during: 5000, height: sliderHeight
        });   //height可以设置首页幻灯片高度
        //倒计时逻辑判断
        let e_date = "{{$config['e_date']}}"
        e_date = new Date(e_date.replace(/\-/g, "/"));
        let s_date = "{{$config['s_date']}}"
        s_date = new Date(s_date.replace(/\-/g, "/"));
        let differ = 0;
        initCountDown(s_date, e_date);

        function initCountDown(s_date, e_date) {
            let now = new Date().getTime();
            if (e_date < now) {
                $('.countDown').text('活动已结束');
                return;
            }
            // countDownText
            if (s_date < now) {
                $('#countDownText').text('活动结束');
                differ = e_date - now;
                differ = parseInt(differ / 1000);
            } else {
                $('#countDownText').text('活动开始');
                differ = s_date - now;
                differ = parseInt(differ / 1000);
            }
            setInterval("timingCountDown()", 1000)
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
@endsection
