@extends('web.collectCard.app')
@section('bodyClass', 'bgLinear')
@section('content')
    <div class="bgPicSm bgPicSmLf"></div>
    <div class="bgPicSm bgPicSmRt"></div>
    <ul class="machineUl"></ul>
@endsection

@section('jsResources')
    <script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript" charset="utf-8">
        let latitude, longitude;
        wx.config({!! $app->jssdk->buildConfig(['hideAllNonBaseMenuItem', 'updateAppMessageShareData'], false) !!});
        wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
            wx.hideAllNonBaseMenuItem();
            wx.getLocation({
                type: 'wgs84', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
                success: function (res) {
                    latitude = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                    longitude = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                    show(latitude, longitude);
                }
            });
        });

        function pushHistory() {
            window.addEventListener("popstate", function (e) {
                self.location.reload();
            }, false);
            let state = {title: "", url: "#"};
            window.history.replaceState(state, "", "#");
        };
        let csrf_token = "{{csrf_token()}}";

        function show(latitude, longitude) {
            showLoading('初始化...');
            $.ajax({
                type: 'POST',
                url: window.location.href,
                data: {latitude: latitude, longitude: longitude},
                dataType: "json",
                headers: {
                    'X-CSRF-TOKEN': csrf_token
                },
                success: function (response) {
                    hideLoading();
                    if (response.status == false) {
                        toast({time: 4000, content: response.message})
                        return false;
                    }
                    let html = '';
                    $.each(response.data, function (index, value) {
                        html += '<li class="disFlex machineLi"><div class="machineIcon"></div><div class="machineCon">';
                        html += '<div class="machineRoom">' + value['name'] + '</div>';
                        html += '<div class="machineAddr"><i class="maAddrIcon"></i> <span class="maAddrFont">' + value['region'] + '</span></div></div>';
                        html += '<div class="machineDist"><p>离我</p><p>' + value['distance'] + ' m</p></div></li>'
                        console.log(value);
                    });
                    if (html) {
                        $('.machineUl').append(html);
                    }
                },
                error: function (e) {
                }
            });
        }
        $(function () {
            pushHistory();
        });
    </script>
    <script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
@endsection