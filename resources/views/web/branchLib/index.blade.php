<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <title>图书馆位置定位</title>
    <link rel="stylesheet" href="{{asset('wechatWeb/LibraryLbs/css/index.css')}}">
</head>
<body>
<div id="container"></div>
<div class="libList">
    <ul>
        @foreach($list as $val)
            <li id="{{$val['id']}}" data-lng="{{$val['lng']}}" class="choose {{$val['p_id'] != 1 ?'active':'' }}"
                data-lat="{{$val['lat']}}">
                <div class="listImg"><img src="{{empty($val['logo']) ? $defaultImg  :$val['logo'] }}"></div>
                <div class="listInfo">
                    <h3>{{$val['name']}}</h3>
                    <div class="poiDetail">
                        <i class="addrIcon"></i>
                        <span class="AddrTxt"> {{$val['address']}}</span>
                    </div>
                    <div class="poiDetail">
                        <i class="telephoneIcon"></i>
                        <span class="telephoneTxt"> {{$val['telephone']}}</span>
                    </div>
                    <div>驾车: <span class="driving-a">计算中</span> <i class="timeIcon"></i><span
                                class="driving-b">计算中</span></div>
                    <div>步行: <span class="walking-a">计算中</span>
                    </div>
                </div>
                <div class="poiDetailToHere" data-name="{{$val['name']}}" data-keys="{{$val['keys']}}">
                    <i class="toHere"></i>
                </div>
            </li>
        @endforeach
        <div><p class="libFtFont">没咯，别扯了</p></div>
    </ul>
</div>

</body>
<script charset="utf-8" src="https://map.qq.com/api/js?v=2.exp&key={!! $key !!}"></script>
<script src="https://res.wx.qq.com/open/js/jweixin-1.4.0.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" src="{{asset('common/js/jquery-3.4.1.min.js')}}"></script>
<script type="text/javascript" charset="utf-8">
    let currentLat, currentLng, Map, Marker;
    let lat = "{{$list[0]['lat']}}";
    let lng = "{{$list[0]['lng']}}";
    let key = "{{$key}}"
    let listJson = @json($list);
    wx.config(<?php echo $app->jssdk->buildConfig(array('getLocation'), false) ?>);
    wx.ready(function () {
        // config信息验证后会执行ready方法，所有接口调用都必须在config接口获得结果之后，config是一个客户端的异步操作，
        // 所以如果需要在页面加载时就调用相关接口，则须把相关接口放在ready函数中调用来确保正确执行。对于用户触发时才调用的接口，
        // 则可以直接调用，不需要放在ready函数中。
        wx.getLocation({
            type: 'gcj02', // 默认为wgs84的gps坐标，如果要返回直接给openLocation用的火星坐标，可传入'gcj02'
            success: function (res) {
                currentLat = res.latitude; // 纬度，浮点数，范围为90 ~ -90
                currentLng = res.longitude; // 经度，浮点数，范围为180 ~ -180。
                console.log(res);
                // let speed = res.speed; // 速度，以米/每秒计
                // let accuracy = res.accuracy; // 位置精度
                getDistance(currentLat, currentLng);
            }
        });
    });

    function init() {
        let myLatlng = new qq.maps.LatLng(lat, lng);      //设置地图中心点
        // 定义工厂模式函数
        let myOptions = {
            zoom: 18,               //设置地图缩放级别
            center: myLatlng,      //设置中心点样式
            mapTypeId: qq.maps.MapTypeId.ROADMAP,  //设置地图样式详情参见MapType
            mapZoomType: qq.maps.MapZoomType.CENTER,
        }
        //获取dom元素添加地图信息
        Map = new qq.maps.Map(document.getElementById("container"), myOptions);
        Marker = new qq.maps.Marker({
            map: Map,
            position: Map.getCenter(),
            animation: qq.maps.MarkerAnimation.DROP,
        });
    }

    function setCenter(lat, lng) {
        let LagLng = new qq.maps.LatLng(lat, lng);
        Map.panTo(LagLng);
        Marker.setPosition(LagLng);
    }

    window.onload = init;    // dom文档加载结束开始加载 此段代码
    $(".choose").click(function () {
        //样式替换
        $('.active').removeClass("active");
        $(this).addClass("active");
        let lat = $(this).data('lat');
        let lng = $(this).data('lng');
        setCenter(lat, lng);
    });


    function getDistance(fromLat, formLng) {
        let to = '';
        let keysGetID = {};
        $.each(listJson, function (key, val) {
            keysGetID[val['lat'] + ',' + val['lng']] = val['id'];
            to += val['keys'];
            if (key != listJson.length - 1) {
                to += ';';
            }
        })
        let form = fromLat + ',' + formLng;
        //先计算驾车
        let data = {
            "mode": "driving",
            "from": form,
            "to": to,
            "output": "jsonp",
            "key": key
        };
        console.log(data);
        let url = "https://apis.map.qq.com/ws/distance/v1/?";
        $.ajax({
            type: "get",
            dataType: 'jsonp',
            data: data,
            jsonp: "callback",
            url: url,
            success: function (json) {
                if (json.status !== 0) {
                    return alert('距离计算异常');
                }
                let response = json.result.elements;
                $.each(response, function (key, val) {
                    let finder = '#' + keysGetID[val['to']['lat'] + ',' + val['to']['lng']];
                    $(finder + ' .driving-a').text(rangeConversion(val['distance']));
                    $(finder + ' .driving-a').addClass('blueText');
                    $(finder + ' .driving-b').text(formatSeconds(val['duration']));
                    $(finder + ' .driving-b').addClass('blueText');
                })
                console.log(json);
                //业务处理
            },
            error: function (err) {
                //业务处理
            }

        });
        //计算行走
        data = {
            "mode": "walking",
            "from": form,
            "to": to,
            "output": "jsonp",
            "key": key
        };
        url = "https://apis.map.qq.com/ws/distance/v1/?";
        $.ajax({
            type: "get",
            dataType: 'jsonp',
            data: data,
            jsonp: "callback",
            url: url,
            success: function (json) {
                if (json.status !== 0) {
                    return alert('距离计算异常');
                }
                let response = json.result.elements;
                $.each(response, function (key, val) {
                    let finder = '#' + keysGetID[val['to']['lat'] + ',' + val['to']['lng']];
                    $(finder + ' .walking-a').text(rangeConversion(val['distance']));
                    $(finder + ' .walking-a').addClass('blueText');
                })
                console.log(json);
                //业务处理
            },
            error: function (err) {
                //业务处理
            }

        });
    }

    function formatSeconds(value) {
        let theTime = parseInt(value);// 需要转换的时间秒
        let theTime1 = 0;// 分
        let theTime2 = 0;// 小时
        let theTime3 = 0;// 天
        if (theTime > 60) {
            theTime1 = parseInt(theTime / 60);
            // theTime = parseInt(theTime % 60);
            if (theTime1 > 60) {
                theTime2 = parseInt(theTime1 / 60);
                theTime1 = parseInt(theTime1 % 60);
                if (theTime2 > 24) {
                    //大于24小时
                    theTime3 = parseInt(theTime2 / 24);
                    theTime2 = parseInt(theTime2 % 24);
                }
            }
        }
        let result = '';
        // if (theTime > 0) {
        //     result = "" + parseInt(theTime) + "秒";
        // }
        if (theTime1 > 0) {
            result = "" + parseInt(theTime1) + "分" + result;
        }
        if (theTime2 > 0) {
            result = "" + parseInt(theTime2) + "小时" + result;
        }
        if (theTime3 > 0) {
            result = "" + parseInt(theTime3) + "天" + result;
        }
        return result;
    }

    function rangeConversion(distance) {
        if (distance < 1000) {
            return '约' + distance + '米';
        }
        return '约' + (Math.round(distance / 100) / 10).toFixed(1) + "公里";
    }

    //点击去这里 调起地图
    $('.poiDetailToHere').click(function () {
        let url = 'https://apis.map.qq.com/uri/v1/routeplan?type=bus&referer=微门户LBS定位&key=' + key;
        url += '&tocoord=' + $(this).data('keys');
        url += '&to=' + $(this).data('name');
        window.location.href = url;
    })
</script>
</html>
