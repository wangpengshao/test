<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <script type="text/javascript" src="{{asset('wechatWeb/infowall/js/resize.js')}}"></script>
    <title>许愿墙</title>
    <link rel="stylesheet" href="{{asset('wechatWeb/infowall/css/swiper.min.css?1')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/infowall/css/danmu.css?1')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/infowall/css/base.css?1')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/infowall/css/index.css?1')}}">
</head>

<body>
<div class="app" id="app">
    <div class="danmuBg">
        <div class="danmu-title"></div>
        <div class="danmu-tips">
            到馆弹幕数 <span class="inLibNum">{{$inner_news}}</span> 条，馆外弹幕数 <span class="outLibNum">{{$out_news}}</span> 条
        </div>
        <div class="danmu-box">
            <div class="top-danmu">
                <div class="danmu-item-box defaultitem"></div>
            </div>
            <div class="button-danmu">
                <div class="danmu-item-box defaultitem"></div>
            </div>
        </div>
        <div class="rules-btn"> 规则</div>
    </div>
    <div class="activity-info">
        <div class="activity-title">
            <span>活动时间</span>
        </div>
        <p class="activity-time text-tips">{{$config['start_at']}}-{{$config['end_at']}}</p>
        <p class="activity-instr text-tips">
            {!! $config['describe'] !!}
        </p>
    </div>
    <div class="index-btn-box">
        <div class="index-btn"> 我要许愿 </div>
        <div class="res-posters" style="display: none">
            <img class="posters-bg" src="{{asset('wechatWeb/infowall/imgs/icon-posters.png')}}" alt="">
        </div>
    </div>
    <!--    姓名手机号弹窗 -> 只有正确填写才进入首页 -->
    <div class="dialog getUserInfo" style="display: none">
        <div class="dialog-box">
            <div class="dialog-main UserInfo-main">
                <div class="UserInfo-title">动动手指填写信息</div>
                <div class="info-field">
                    <div class="fieldBox">
                        <div class="field">
                            <i class="field-icon userName"></i>
                            <input type="text" class="name" placeholder="请输入姓名" value="{{$fansInfo['nickname']}}">
                            <i class="field-edit"></i>
                        </div>
                    </div>
                    <div class="fieldBox">
                        <div class="field">
                            <i class="field-icon userpwd"></i>
                            <input type="text" placeholder="请输入手机号" class="phone">
                        </div>
                    </div>
                </div>
                <!-- 正确填写后  新增 active类 -->
                <!-- <div class="info-btn active"> -->
                <div class="info-btn">确定</div>
            </div>
        </div>
    </div>

    <!--  姓名手机号弹窗 end  -->
    <!-- 规则弹窗 -->
    <div class="dialog rules-box" style="display: none">
        <div class="dialog-box">
            <div class="dialog-main rules-main">
                <div class="dialog-close">111</div>
                <div class="rules-title">活动规则</div>
                <div class="rules-center">
                    {!! $config['rule'] !!}
                </div>
            </div>
        </div>
    </div>
    <!--  规则弹窗 end  -->
    <!-- 发布成功结果 -> 生成海报 -->
    <div class="dialog result-successBox" style="display: none">
        <div class="dialog-box">
            <div class="dialog-main result-issuccess">
                <div class="result-issuccess-close"></div>
                <div class="result-issuccess-posters">
                    <img class="result-issuccess-posters-bg" src="{{asset('wechatWeb/infowall/imgs/bg-posters-bg.png')}}" alt="">
                    <div class="result-data">
                        <div class="data-time">
                            <span class="data-day">{{$date['day']}}</span>
                            <span class="data-week">{{$date['weekday']}}</span>
                        </div>
                        <div class="data-data">
                            <span class="data-month">{{$date['month']}} </span>/
                            <span class="data-years">{{$date['year']}}</span>
                        </div>
                    </div>
                    <div class="result-posters-info">
                        <div class="posters-user-info">
                            <img src="{{$fansInfo['headimgurl']}}" alt="">
                            <div>
                                <p class="user-name">
                                    @if($user != '')
                                        {{$user['username']}}
                                    @endif
                                </p>
                                <p class="user-lib">{{$wxname}}</p>
                            </div>
                        </div>
                        <div class="posters-topic-info">
                            <div class="posters-topic-text">
                                <div class="posters-topic-header">
                                    <div class="posters-topic">
                                        <p class="topic-tips">我在许愿墙上参与话题</p>
                                        <p class="posters-topic">#今年听到最感人的一句话#</p>
                                    </div>
                                </div>
                                <div class="poster-wlsh">
                                    说不怕是假的，但我们会一直坚持下去，直到春暖花开。
                                </div>
                            </div>
                            <div class="qrcode" id="qrcode"></div>
                        </div>
                    </div>
                </div>
                <img class="poster-save-img" style="display: none" src="" alt="">
                <div class="result-issuccess-imgtips">长按保存海报即可分享</div>
            </div>
        </div>
    </div>
    <!-- 发表失败结果 -->
    <div class="dialog result-errorBox" style="display: none">
        <div class="dialog-box">
            <div class="dialog-main result-iserror">
                <div class="dialog-close"></div>
                <div class="result-iserroe-title">
                    发布失败
                </div>
                <div class="result-iserror-tips">
                    无网络连接，请查看网络
                </div>
                <div class="result-iserror-icon">

                </div>
            </div>
        </div>
    </div>

    <!--  底部弹窗  -->
    <!--  选择心愿  -->
    <div class="buttomdialog seltopicBox" style="display: none">
        <div class="buttomdialog-main seltopic">
            <i class="icon-close"></i>
            <div class="seltopic-title">请选择话题</div>
            <div class="seltopic-main">
                @foreach($Danmu as $value)
                    <div class="seltopic-item" data-id="{{$value['id']}}">
                    <span class="seltopic-item-class">
                        @if($value['type']==0) 阅读
                        @elseif($value['type']==1) 未来
                        @else 温暖
                        @endif
                    </span>
                        <span class="seltopic-topic">#{{$value['p_name']}}#</span>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
    <!--  选择心愿 end  -->
    <!--  录入心愿  -->
    <div class="buttomdialog selwishBox" style="display: none">
        <div class="buttomdialog-main selwish ">
            <i class="icon-close"></i>
            <span class="right-btn selwish-change-item" data-cid="2">
                <i class="icon-change"></i>
                <i>换一批</i>
            </span>
            <div class="selwish-title">
                #今年听到最感人的一句话#
            </div>
            <div class="selwish-main">
                <div class="selwish-list">
                    <div class="selwish-item selwish-item-default wish0" style="display: none;">
                    </div>
                    <div class="selwish-item selwish-item-default wish1" style="display: none;">
                    </div>
                    <div class="selwish-item selwish-item-default wish2" style="display: none;">
                    </div>
                    <!-- 手动输入按钮 -->
                    @if($config['is_custom']==1)
                    <div class="selwish-item selwish-item-edit">
                        手动输入心愿
                    </div>
                    @endif
                </div>
                <!-- 重新选择心愿 -->
                <div class="selwish-seltopic">
                    <div class="swiper-container">
                        <div class="swiper-wrapper">
                            @foreach($Danmu as $key => $value)
                                <div class="swiper-slide {{$value['id']}}" data-id="{{$value['id']}}"
                                     data-topic="#{{$value['p_name']}}#">
                                    @if($value['type']==0) 阅读
                                    @elseif($value['type']==1) 未来
                                    @else 温暖
                                    @endif#{{$value['p_name']}}#
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!--  录入心愿 end  -->
    <!-- 手动输入心愿 -->
    <div class="buttomdialog inputwishBox" style="display: none">
        <div class="buttomdialog-main inputwish">
            <i class="icon-close"></i>
            <span class="right-btn inputwish-title-btn">
                发表
            </span>
            <div class="inputwish-title">
            </div>
            <div class="inputBox">
                <textarea class="inputBox-textarea" maxlength="25" placeholder="请输入您的愿望"></textarea>
                <span class="numTips">0/25</span>
            </div>
        </div>
    </div>
    <!-- 手动输入心愿 end -->
    <!--  底部弹窗  -->
</div>
</body>
<script type="text/javascript" src="{{asset('wechatWeb/infowall/js/jquery-2.2.4.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/infowall/js/common.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/infowall/js/swiper.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/infowall/js/danmu.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/infowall/js/html2canvas.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/infowall/js/qrcode.min.js')}}"></script>
<!--弹幕-->
<script>
    let token = "{{$token}}";
    let site = "{{$site}}"; // 馆内、馆外标记
    let img = "{{$fansInfo['headimgurl']}}";
    let image = "{{asset('wechatWeb/infowall/imgs/user-header.jpg')}}"; // 默认头像图片
    var danmuArr = [];
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
        if (danmuArr.length < 1) {
            // 弹幕数据不足  请求弹幕
            //相当于请求数据回来对数据进行操作
            setTimeout(getDanmu, 2000);
        }
        let item = danmuArr.splice(0, 1)[0];
        return item
    }

    function ScrollContainer($root) {
        //计算行数
        var rows = [];
        var speed = 2;
        this.resize = function () {
            let row = 2;
            rows.forEach(i => i.destroy());
            rows = [];
            for (let i = 0; i < row; i++) {
                rows.push(new ScrollRow($root, speed))
            }
            ;
        }
        this.resize();
    }

    function ScrollRow($parent, speed) {
        var $root = $('<div class="top-danmu"><div class="danmu-item-box defaultitem"></div></div>');
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
                first.remove();
                movedDistance = 0;
            }
            run && window.requestAnimationFrame(loop);
        }

        var handle = window.requestAnimationFrame(loop)
        this.destroy = function () {
            run = false;
            window.cancelAnimationFrame(handle)
            $root.remove();
        }
    }

    // 获取弹幕信息
    function getDanmu() {
        $.ajax({
            url: "/webWechat/infowall/getDanmu",
            type: "POST",
            dataType: "json",
            data: {
                _token: '{{csrf_token()}}',
                'token': token,
                'a_id': "{{$config['id']}}",
            },
            success: function (result) {
                if (result.data.danmu != '') {
                    var danmu = result.data.danmu;
                    // 模拟数据用的 相当于请求弹幕数据
                    danmu.map(it => {
                        danmuArr.push({
                            self: false, //是否是当前读者的弹幕
                            adder: it.site == 1 ? true : false, //馆内弹幕
                            user: it.has_one_user ? it.has_one_user.username || '佚名' : '佚名', // 用户名
                            topic: it.topic, //话题
                            wish: it.content, //心愿
                            img: it.has_one_user ? it.has_one_user.headimgurl : image, //用户头像
                        })
                    })
                }
            }
        });
    }

    function saveImg() {
        let imgDemo = document.querySelector('.result-issuccess-posters');
        let w = imgDemo.offsetWidth;
        let h = imgDemo.offsetHeight;
        let canvas = document.createElement("canvas");
        let scale = window.devicePixelRatio;
        canvas.width = w * scale;
        canvas.height = h * scale;
        canvas.style.width = w + 'px';
        canvas.style.height = h + 'px';
        canvas.getContext("2d").scale(scale, scale);
        html2canvas(imgDemo, {
            useCORS: true,
            allowTaint: true,
            logging: false,
            dpi: scale * 10,
            scale: scale,
            width: w,
            height: h,
            backgroundColor: 'rgba(0, 0, 0, 0)'
        }).then(canvas => {
            let dataURL = canvas.toDataURL("image/png", 1.0);
            let img = new Image();
            img.src = dataURL;
            img.style.width = w + "px";
            img.style.height = h + "px";
            img.onload = () => {
                $('.poster-save-img').attr('src', dataURL).show();
                $('.result-issuccess-posters').hide();
            };
        });
    }
</script>
<!--页面交互-->
<script>
    let check = "{{$config['is_check']}}";
    let sign = "{{$sign}}";
    let openid = "{{$fansInfo['openid']}}";
    let user_id = "{{$user['id']}}";
    let username = "{{$user['username']}}";
    window.onload = function () {
        let data = "{{$url}}";
        let a_id = "{{$config['id']}}";
        let url = data+'?token='+token+'&a_id='+a_id+'&site=2';
        var damo = document.getElementById("qrcode");
        // console.log($(damo).width()*10,$(damo).height());
        var qrcode = new QRCode(damo, {
            text: url,
            width: $(damo).width() * 10,
            height: $(damo).width() * 10,
            colorDark: "#000000",
            colorLight: "#ffffff",
            correctLevel: QRCode.CorrectLevel.L
        });
        $($(damo).children('img')[0]).css('width',$(damo).width()+'px');
        $($(damo).children('img')[0]).css('height',$(damo).height()+'px');
        getDanmu();
        window.row = new ScrollContainer($(".danmu-box"));
    }

    // 添加用户数据
    if (sign == 1) {
        $('.getUserInfo').fadeIn();
    }
    $('input').bind('input propertychange', function () {
        var name = $('.name').val();
        var phone = $('.phone').val();
        // 验证手机号码格式是否正确
        var geshi = phoneFun(phone);
        if (name != '' && geshi != 1) {
            // 改变按钮颜色
            $('.info-btn').css('background', '#5BCBA9')
        }
    })

    // 判断手机号码是否符合规定
    function phoneFun(phones) {
        var myreg = /^[1][3,4,5,7,8,9][0-9]{9}$/;
        if (!myreg.test(phones)) {
            return 1;
        }
    }

    // 添加手机姓名信息
    $('.info-btn').on('click', function () {
        var name = $('.name').val();
        var phone = $('.phone').val();
        if (name == '' || phone == '') {
            toast("姓名或手机号码不允许为空", 1000);
            return;
        }
        // 将姓名信息添加到海报页面中
        $('.user-name').text(name);
        $.ajax({
            url: "/webWechat/infowall/addUserInfo",
            type: "POST",
            dataType: "json",
            data: {
                _token: '{{csrf_token()}}',
                'name': name,
                'phone': phone,
                'openid': openid,
                'nickname': "{{$fansInfo['nickname']}}",
                'headimgurl': "{{$fansInfo['headimgurl']}}",
                'token': token,
                'l_id': "{{$config['id']}}",
            },
            success: function (result) {
                if (result.status == true) {
                    // 将user_id添加到按钮中
                    $('.selwish-title').attr('data-uid', result.data.user_id);
                    $('.selwish-title').attr('data-user', name);
                    $('.getUserInfo').fadeOut();
                } else {
                    toast(result.data.message, 1000);
                }
            }
        });
    })

    //心愿swiper
    function initSwiper() {
        new Swiper('.swiper-container', {
            spaceBetween: 10,
            freeMode: true,
            slidesPerView: 'auto',
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            on: {
                tap: function (event) {
                    tapSwiper(event);
                },
            },
        });
    };

    // swiper click
    function tapSwiper(e) {
        let target = e.target || e.srcElement;
        if (!$(target).hasClass('active')) {
            $(target).addClass('active').siblings().removeClass('active');
            /*
            * 获取target的参数去加载心愿列表
            * ajax
            *
            */
        }
    }

    //规则弹出隐藏
    $('.rules-btn').on('click', function () {
        $('.rules-box').fadeIn()
    });
    $('.rules-box .dialog-close').on('click', function () {
        $('.rules-box').fadeOut();
    });

    //许愿弹出隐藏
    $('.index-btn').on('click', function () {
        $('.seltopicBox').fadeIn();
    });
    $('.seltopicBox .icon-close').on('click', function () {
        $('.seltopicBox').fadeOut();
    });

    //心愿窗口弹出隐藏
    $('.seltopic-item').on('click', function (e) {
        /*获取相对应的数据
        ajax请求
        */
        $('.seltopicBox').fadeOut();
        // 根据弹幕id值去获取相应的二级话题
        var id = $(this).attr('data-id');
        // 更新获取换一批话题的联动初始值
        $('.selwish-change-item').attr('data-cid', 2);
        // 联动话题增加类
        $('.swiper-slide').removeClass('active');
        $("." + id).addClass('active');
        // 将话题放入海报中  心愿选择提示栏中
        var topic = $(e.currentTarget).children('.seltopic-topic').text();
        $('.selwish-title').text(topic);
        $('.inputwish-title').text(topic);
        $('.top-topic').text(topic);
        // 调用获取二级话题的函数
        getTopic(id);
        $('.selwishBox').fadeIn(function () {
            initSwiper()
        })
    });
    $('.selwishBox .icon-close').on('click', function () {
        $('.selwishBox').fadeOut();
    });

    // 动态获取二级话题数据
    function getTopic(id) {
        $.ajax({
            url: "/webWechat/infowall/getTopic",
            type: "POST",
            dataType: "json",
            data: {
                _token: '{{csrf_token()}}',
                'id': id,
            },
            success: function (result) {
                $.each(result.data.topic, function (i, v) {
                    $(".wish" + i).text(v);
                    $(".wish" + i).show();
                })
                // 将数组长度放置于换一批按钮上
                var length = result.data.length;
                $('.selwish-change-item').attr('data-id', id);
                $('.selwish-change-item').attr('data-len', length);
            }
        });
    }

    //手动输入心愿弹窗隐藏
    $('.selwishBox .selwish-item-edit').on('click', function () {
        $('.selwishBox').fadeOut();
        $('.inputwishBox').fadeIn()
    });

    //选择心愿直接生成海报
    $('.selwish-item-default').on('click', function (e) {
        $('.selwishBox').hide();
        var text = $(this).text();
        var topic = $('.selwish-title').text();
        var type = 2;
        $('.poster-wlsh').text(text);
        saveWish(text, topic, type)
    });
    //换一批心愿
    $('.selwish-change-item').on('click', function (e) {
        var id = $(this).attr('data-id');
        var cid = $(this).attr('data-cid');
        var len = $(this).attr('data-len');
        if (len == 1) {
            toast('抱歉，无其它数据', 1000);
            return;
        }
        // 如果当前页码比最大页码值大，则取1，取原先数组
        if (cid > len) {
            cid = 1;
        }
        var page = 0;
        $.ajax({
            url: "/webWechat/infowall/page/getTopic",
            type: "POST",
            dataType: "json",
            data: {
                _token: '{{csrf_token()}}',
                'id': id,
                'cid': cid
            },
            success: function (result) {
                $.each(result.data.ftopic, function (i, v) {
                    page = i;
                    $(".wish" + i).text(v);
                    $(".wish" + i).show();
                })
                for (var i = 1; i < 3; i++) {
                    if (page < 2) {
                        page = parseInt(page) + 1;
                        $(".wish" + page).hide();
                    }
                }
                var nid = parseInt(cid) + 1;
                $('.selwish-change-item').attr('data-cid', nid);
            }
        });
    });

    //底部选择心愿换话题
    $('.swiper-slide').on('click', function () {
        var id = $(this).attr('data-id');
        var topic = $(this).attr('data-topic');
        $('.selwish-change-item').attr('data-cid', 2);
        // $(this).addClass('active');
        $('.selwish-title').text(topic);
        getTopic(id);
    })

    $('.inputwishBox .icon-close').on('click', function () {
        $('.inputwishBox').fadeOut()
    });

    // 保存心愿
    function saveWish(text, topic, type) {
        if (user_id == '') {
            var uid = $('.selwish-title').attr('data-uid');
            var name = $('.selwish-title').attr('data-user');
        } else {
            var uid = user_id;
            var name = username;
            var status = "{{$user['status']}}";
        }
        if (status == 2) {
            $('.result-iserror-tips').text('抱歉，你已经被禁止发弹幕了');
            $('.result-errorBox').fadeIn();
            return;
        }
        $.ajax({
            url: "/webWechat/infowall/wishWord/saveWish",
            type: "POST",
            dataType: "json",
            data: {
                _token: '{{csrf_token()}}',
                'content': text,
                'id': '{{$config['id']}}',
                'user_id': uid,
                'site': site,
                'token': token,
                'topic': topic,
                'type': type,
            },
            success: function (result) {
                if (result.status == true) {
                    let num = 0; // 当前馆弹幕数
                    let newNum = 0; // 当前馆的新弹幕数;
                    if (site == 1) {
                        num = $('.inLibNum').text();
                        newNum = parseInt(num) + 1;
                        $('.inLibNum').text(newNum);
                    } else {
                        num = $('.outLibNum').text();
                        newNum = parseInt(num) + 1;
                        $('.outLibNum').text(newNum);
                    }
                    $('.inputwishBox').fadeOut()
                    $('.result-successBox').fadeIn(() => {
                        $('.poster-save-img').hide();
                        $('.result-issuccess-posters').show();
                        saveImg();
                        let number = true; // 避免多次执行事件。
                        $('.result-successBox .result-issuccess-close').on('click', function () {
                            if (number) {
                                number = false;
                                // 显示海报按钮
                                $('.res-posters').show();
                                if (check == 1 && type == 1) {
                                    toast("弹幕上墙需要经过审核后才能展示噢，请耐心等待～", 2000);
                                    $('.result-successBox').hide();
                                    return;
                                }
                                $('.result-successBox').hide(function () {
                                    if (site == 1) {
                                        var adder = true;
                                    } else {
                                        var adder = false;
                                    }
                                    danmuArr.unshift({
                                        self: true, //是否是当前读者的弹幕
                                        adder: adder, //馆内弹幕
                                        user: name, // 用户名
                                        topic: topic, //话题
                                        wish: text, //心愿
                                        img: img, //用户头像
                                    })
                                });
                            }
                            return
                        });
                    });
                } else {
                    $('.result-iserror-tips').text(result.message);
                    $('.result-errorBox').fadeIn();
                }
            }
        });
        $('.posters-bg').on('click',function () {
            $('.result-successBox').fadeIn(() => {
                $('.poster-save-img').hide();
                $('.result-issuccess-posters').show();
                saveImg();
                $('.result-successBox .result-issuccess-close').on('click', function () {
                    $('.result-successBox').hide();
                });
            });
        })
    }

    //发表
    $('.inputwish-title-btn').on('click', function () {
        let text = $('.inputBox-textarea').val();
        let topic = $('.inputwish-title').text();
        $('.poster-wlsh').text(text);
        var type = 1;
        if (text.length > 0) {
            // 想看当前活动是否为手动审核，如果不是，则调用百度AI接口进行敏感词过滤
            saveWish(text, topic, type);
            $('.inputBox-textarea').val('');
            return
        }
        toast('请输入心愿');
    });
    // 关闭发表错误弹窗
    $('.result-errorBox .dialog-close').on('click', function () {
        $('.result-errorBox').fadeOut();
    });
    // 关闭海报
    // $('.result-successBox .result-issuccess-close').on('click', function () {
    //     $('.result-successBox').fadeOut();
    // });
    //监听输入长度
    $(".inputBox-textarea").bind("input", function () {
        let leng = $(".inputBox-textarea").val().length;
        if (leng === 0) {
            $(".numTips").text(`0/25`);
            return
        }
        if (leng <= 25) {
            if (leng == 25) {
                $(".numTips").css("color", "rgba(255, 84, 81, 1)");
            } else {
                $(".numTips").css("color", "rgba(153, 153, 153, 1)");
            }
            $(".numTips").text(`${leng}/25`);
            return
        }
    });

    function resetDiv() {
        //ios 兼容，input失去焦点后界面上移不恢复的问题
        setTimeout(() => {
            let scrollHeight =
                document.documentElement.scrollTop || document.body.scrollTop || 0;
            window.scrollTo(0, Math.max(scrollHeight - 1, 0));
        }, 100);
    };

    $('input').blur(function () {
        resetDiv()
    });

    $(".inputBox-textarea").blur(function () {
        resetDiv();
    });


</script>
</html>
