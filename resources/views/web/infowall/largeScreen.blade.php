<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>大屏弹幕墙</title>
    <script type="text/javascript" src="{{asset('wechatWeb/infowall/js/resize.js')}}"></script>
    <link rel="stylesheet" href="{{asset('wechatWeb/infowall/css/pc-danmu.css?1')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/infowall/css/base.css?1')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/infowall/css/pcindex.css?1')}}">
</head>

<body>
<div class="pcApp">
    <div class="pc-title">
        <div class="pc-title-img"></div>
    </div>
    <div class="danmu" data-pid="">
    </div>
    <div class="pc-button-tips">
        <div class="pc-tips-text">
            <p>到馆弹幕数 <span class="inLib">{{$inner_news}}</span> 条 </p>
            <p>馆外弹幕数 <span class="outLib">{{$out_news}}</span> 条</p>
        </div>
        <div class="pc-qrcode">
            <div id="qrcode">
            </div>
            <p>扫码送祝福</p>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{asset('wechatWeb/infowall/js/jquery-2.2.4.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/infowall/js/danmu.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/infowall/js/qrcode.min.js')}}"></script>
<script>
    let image = "{{asset('wechatWeb/infowall/imgs/user-header.jpg')}}"; // 默认头像图片
    let token = "{{$token}}";
    let a_id = '{{$a_id}}';
    let danmuArr = [],//弹幕数组
        danmuIndex = 0,//弹幕索引
        danmuLen = 50,//弹幕长度
        showArr = [];//新弹幕
    window.onload = function() {
        let data = "{{$url}}";
        let url = data+'?token='+token+'&a_id='+a_id+'&site=1';
        // 生成二维码
        var pic = new QRCode('qrcode', {
            text: url,
            width: 128,
            height: 128,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.H
        });
        $("#qrcode").attr({ src: pic});
        //    继续轮询
        setInterval(() => {
            getDanmuData();
        },3*1000);

        let height = $('body').height() - $('.pc-title').height() - $('.pc-button-tips').height();
        window.row = new ScrollContainer($(".danmu"), height);
    }
    function itemCreator(item) {
        return !!item ? `<div class="danmu-item-box">
                    <div class="danmu-item ${item.self ? 'selfItem' : (item.adder ? 'inlib' : 'outlib')}">
                      <img src="${item.img}">
                        <div class="danmu-item-center">
                            <p class="danmu-text-title">
                                <span class="userName">${item.user}</span>
                                <span>${item.topic}</span>
                            </p>
                            <p class="danmu-text-topic">${item.wish}</p>
                        </div>
                    </div>
                </div>` : '<div class="danmu-item-box" style="width:100px"></div>';
    }

    function itemGenerator() {
        let item = {};
        if(showArr.length > 0){
            item = showArr.reverse().splice(0, 1)[0];
            showArr.reverse();
            if(danmuIndex>danmuLen/2&&!!item){
                danmuArr[danmuLen-danmuIndex] = item;
            }
            return item;
        }
        danmuIndex >= danmuArr.length && (danmuIndex = 0);
        item = danmuArr[danmuIndex];
        if(!!item) danmuIndex++;
        return item;
    }

    function ScrollContainer($root, height) {
        //计算行数
        var rows = [];
        var speed = 3;
        this.resize = function() {
            let danmuH = Math.ceil(height);
            $root.css('height', danmuH + 'px');
            let row = Math.round(danmuH / 125);
            rows.forEach(i => i.destroy());
            rows = [];
            for (let i = 0; i < row; i++) {
                rows.push(new ScrollRow($root, speed))
            };
        }
        this.resize();
    }

    function ScrollRow($parent, speed) {
        var $root = $('<div class="danmuItem"><div class="danmu-item-box defaultItem"></div></div>');
        $parent.append($root);
        var movedDistance = 0;
        var run = true;

        function loop() {
            // move
            movedDistance += speed;
            var first = $root.children().eq(0);
            first.css("margin-left", "-" + movedDistance + "px");
            //append
            var rowWith = $root.children().toArray().reduce((acc, item) => acc + $(item).width(), 0);
            var containerWidth = $parent.width();
            if (containerWidth + movedDistance >= rowWith) {
                var dat = itemGenerator();
                $root.append(itemCreator(dat))
            }
            //remove
            if (movedDistance >= first.width()) {
                var beyondW = movedDistance-first.width();
                first.remove();
                movedDistance = beyondW;
            }
            run && window.requestAnimationFrame(loop);
        }
        var handle = window.requestAnimationFrame(loop)
        this.destroy = function() {
            run = false;
            window.cancelAnimationFrame(handle)
            $root.remove();
        }
    }

    function setIntervalGetData(){
        if(danmuArr.length < danmuLen){
            getDanmuData();
        }
        let setint = setInterval(() => {
            getDanmuData();
        },10*1000);
    }

    function getDanmuData(){
        var p_id = $('.danmu').attr('data-pid');
        $.ajax({
            url: "/webWechat/infowall/largeScreen/getDanmu",
            type: "POST",
            dataType: "json",
            // async:false,
            data: {
                _token: '{{csrf_token()}}',
                'token':token,
                'pid':p_id ? p_id : 1,
            },
            success: function (result) {
                if (result.data.danmu != '') {
                    var pid = result.data.pid;
                    var inner_news = result.data.inner_news;
                    var out_news = result.data.out_news;
                    $('.inLib').text(inner_news);
                    $('.outLib').text(out_news);
                    $('.danmu').attr('data-pid',pid);
                    var danmu = result.data.danmu;
                    if(danmu.length>0){
                        let arr = danmu.map(it => {
                            return {
                                self: false, //是否是当前读者的弹幕
                                adder: it.site == 1 ? true : false, //馆内弹幕
                                user: it.has_one_user ? it.has_one_user.username || '佚名' : '佚名', // 用户名
                                topic: it.topic, //话题
                                wish: it.content, //心愿
                                img: it.has_one_user ? it.has_one_user.headimgurl : image, //用户头像
                            }
                        })
                        if(danmuArr.length < danmuLen){
                            danmuArr = [...danmuArr,...arr];
                            if(danmuArr.length > danmuLen){
                                danmuArr.reverse().length = danmuLen;
                                danmuArr.reverse();
                                return
                            }
                        }
                        showArr = [...showArr,...arr];
                        return;
                    }
                }
            }
        });
    }

</script>
</body>

</html>