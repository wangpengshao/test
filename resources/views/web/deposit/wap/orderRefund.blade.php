<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <!-- 视口标签 -->
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
    <!-- 删除苹果默认的工具栏和菜单栏 -->
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <!-- 设置苹果工具栏颜色 -->
    <meta name="apple-mobile-web-app-status-bar-style" content="black" />
    <!-- 忽略页面中的数字识别为电话，忽略email识别 -->
    <meta name="format-detection" content="telphone=no, email=no" />
    <!-- 启用360浏览器的极速模式(webkit) -->
    <meta name="renderer" content="webkit">
    <!-- 避免IE使用兼容模式 -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- 针对手持设备优化，主要是针对一些老的不识别viewport的浏览器，比如黑莓 -->
    <meta name="HandheldFriendly" content="true">
    <!-- 微软的老式浏览器 -->
    <meta name="MobileOptimized" content="320">
    <!-- uc强制竖屏 -->
    <meta name="screen-orientation" content="portrait">
    <!-- QQ强制竖屏 -->
    <meta name="x5-orientation" content="portrait">
    <!-- UC应用模式 -->
    <meta name="browsermode" content="application">
    <!-- QQ应用模式 -->
    <meta name="x5-page-mode" content="app">
    <!-- windows phone 点击无高光 -->
    <meta name="msapplication-tap-highlight" content="no">
    <!-- 适应移动端end -->
    <title>预约退押金</title>
    <script src="{{asset('wechatWeb/deposit/wap/js/common.js')}}" type="text/javascript" charset="utf-8"></script>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/deposit/wap/css/base.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/deposit/wap/css/index.css?1')}}" />
    <link rel="stylesheet" href="{{asset('wechatWeb/deposit/wap/css/iosSelect.css')}}">
    <style type="text/css">
        .right{
            text-align: right;
        }
        .jine{
            color: #59b6db;
            font-size: 0.5rem;
        }
        .rdid{
            background: #fff;
        }
        .rdid{
            display: inline-block;
            position: relative;
            width: 100%;
            box-sizing: border-box;
            background-color: #fff;
        }
    </style>
</head>
<body style="left: 0;right: 0;">
<div class="headTab">
    <ul class="headTabUl">
        <li class="headTabLi headTabCur">
            <a href="/webWechat/deposit/wap/order/refund/{{$token}}" class="headTabLink">预约</a>
        </li>
        <li class="headTabLi">
            <a href="/webWechat/deposit/wap/order/refund/depositLog/{{$token}}" class="headTabLink" id="log">记录</a>
        </li>
    </ul>
    <div class="readCardArea rdid" >
        <input type="text" placeholder="请输入您办证所留的身份证号码" id="rdid"/>
        <button type="submit" class="search"></button>
    </div>
</div>
<div class="conArea">
    <div class="timeLine">
        <div class="wrapper wrapper01" id="retr">
            <div class="scroller">
                <ul class="clearfix date">
                    <li><a href="javascript:void(0)"><p>星期一</p><p>09-06</p></a></li>
                    <li><a href="javascript:void(0)"><p>星期二</p><p>09-07</p></a></li>
                    <li><a href="javascript:void(0)"><p>星期三</p><p>09-08</p></a></li>
                    <li><a href="javascript:void(0)"><p>星期四</p><p>09-08</p></a></li>
                    <li><a href="javascript:void(0)"><p>星期五</p><p>09-08</p></a></li>
                    <li><a href="javascript:void(0)"><p>星期六</p><p>09-08</p></a></li>
                    <li><a href="javascript:void(0)"><p>星期日</p><p>09-08</p></a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="orderStatuHint" id="tips" style="display: none;">
        <div class="orderStatuImg orderFailImg"></div>
        <p class="statuFail">今日闭馆，请改约其它日期!</p>
    </div>
    <div class="dayStageArea" id="content">
        <div class="dayStageLine">
            <div class="dayStageLineTop">
                <span class="dayStage">上午<i class="dayTm am">(10:00-12:30)</i></span>
            </div>
            <div class="dayStageCon">
                <ul class="dayStageConUl" id="am">
                    <li class="dayStageConLi canChose  chosedStage">
                        <p class="dayStageConTm" data-value="">10:00</p>
                    </li>
                    <li class="dayStageConLi  canChose">
                        <p class="dayStageConTm" data-value="">10:30</p>
                    </li>
                    <li class="dayStageConLi canChose">
                        <p class="dayStageConTm" data-value="">11:00</p>
                    </li>
                    <li class="dayStageConLi canChose">
                        <p class="dayStageConTm" data-value="">11:30</p>
                    </li>
                    <li class="dayStageConLi canChose">
                        <p class="dayStageConTm" data-value="">12:00</p>
                    </li>
                    <li class="dayStageConLi canChose">
                        <p class="dayStageConTm" data-value="">12:30</p>
                    </li>
                </ul>
            </div>
        </div>
        <div class="dayStageLine">
            <div class="dayStageLineTop">
                <span class="dayStage">下午<i class="dayTm pm">(13:00-16:00)</i></span>
            </div>
            <div class="dayStageCon">
                <ul class="dayStageConUl" id="pm">
                    <li class="dayStageConLi canChose">
                        <p class="dayStageConTm" data-value="">13:00</p>
                    </li>
                    <li class="dayStageConLi canChose">
                        <p class="dayStageConTm" data-value="">13:30</p>
                    </li>
                    <li class="dayStageConLi canChose">
                        <p class="dayStageConTm" data-value="">14:00</p>
                    </li>
                    <li class="dayStageConLi canChose">
                        <p class="dayStageConTm" data-value="">14:30</p>
                    </li>
                    <li class="dayStageConLi canChose">
                        <p class="dayStageConTm" data-value="">15:00</p>
                    </li>
                    <li class="dayStageConLi canChose">
                        <p class="dayStageConTm" data-value="">15:30</p>
                    </li>

                </ul>
            </div>
        </div>
        <div class="dayStageLine" id="deposit">
            <div class="dayStageLineTop right">
                <span style="float: left;color: #59b6db;font-size: 0.4rem;" class="rdidNum"></span>
                <span>押金：<i class="jine">00.00</i> 元</span>
            </div>
        </div>
        <div class="confStageBot">
            <input type="button" value="确认" class="orderStatuBtnIn confStageBtn" disabled/>
        </div>
    </div>
</div>

<div class="container"></div>

<script src="{{asset('wechatWeb/deposit/wap/js/jquery-1.11.1.min.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/wap/js/iscroll.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/wap/js/navbarscroll.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/wap/js/xt.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/wap/js/layer.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/wap/js/iosSelect.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/wap/js/validateIdCard.js')}}" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript" charset="utf-8">

    var data = @json($jsonData);
    var changeData = @json($changeData);
    //初始化 start
    if(data){
        var strDate = '';
        var firstDate = "{{$firstDate}}";
        $.each(changeData,function(i,v){
            strDate+='<li  data-value="'+i+'"><a href="javascript:changeDate('+'\''+i+'\''+')"><p>'+getWeek(i)+'</p><p>'+i.substr(5)+'</p></a></li>';
        })
        $('.date').html(strDate)
        changeDate(firstDate)
    }else{
        $('#content').css('display','none');
        $('#tips').css('display','block')
        $('#tips>p').html('暂无数据')
    }
    //初始化 end
    function changeDate(d){
        if(changeData[d]=='false'){
            $('#content').css('display','none')
            $('#tips').css('display','block')
        }else{
            $('#content').css('display','block')
            $('#tips').css('display','none')
            timeBlock(changeData[d],d)
            $('.confStageBtn').attr('disabled',true)
        }
    }
    function timeBlock(b,c){
        var amStr = '';
        var pmStr = '';
        $.each(b,function(i,v){
            var aTime = {},bTime = {};
            if(isIOS()){
                aTime = new Date(c.replace(/-/g,'/'));
            }else{
                aTime = new Date(c+' '+v);
            }
            var week = aTime.getDay();
            var workTime = data['week'+week].split('-')[2].split(':');
            var tm = v.split(':');

            //if(v.substr(0,2) <12){
            if(Number(tm[0])<Number(workTime[0]) || (Number(tm[0])==Number(workTime[0]) && Number(tm[1])<Number(workTime[1]))){
                amStr += '<li class="dayStageConLi canChose" onclick="chose(this)"><p class="dayStageConTm" data-value="'+v+'">'+ v.substr(0,5)+'</p></li>';
            }else{
                pmStr += '<li class="dayStageConLi canChose" onclick="chose(this)"><p class="dayStageConTm" data-value="'+v+'">'+ v.substr(0,5)+'</p></li>';
            }
        });
        if(isIOS()){
            var time = new Date(c.replace(/-/g,'/'));
        }else{
            var time = new Date(c);
        }
        var week = time.getDay();
        var workTime = data['week'+week].split('-');
        var am='',pm='';
        if(workTime[1]!=='00:00:00'){
            am = '('+workTime[1].substr(0,5)+'-'+workTime[2].substr(0,5)+')'
        }else{
            am = '(休息)'
        }
        if(workTime[3]!=='00:00:00'){
            pm = '('+workTime[3].substr(0,5)+'-'+workTime[4].substr(0,5)+')'
        }else{
            pm = '(休息)'
        }

//
//		time.setUTCMinutes(time.getMinutes()+Number(data['block']));
//		var minute = time.getMinutes().toString().length < 2 ? '0'+time.getMinutes().toString() : time.getMinutes();
//		var end = time.getHours()+':'+minute;


        $('.am').html(am);
        $('.pm').html(pm);
        $('#am').html(amStr);
        $('#pm').html(pmStr);
    }
    function isIOS(){
        var agent = window.navigator.userAgent;
        var isIos = !!agent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
        return isIos;
    }
    function getWeek(time){
        var str = '';
        var myDate = new Date(time);
        switch(myDate.getDay()) {
            case 0:
                str = '星期日';
                break;
            case 1:
                str = '星期一';
                break;
            case 2:
                str = '星期二';
                break;
            case 3:
                str = '星期三';
                break;
            case 4:
                str = '星期四';
                break;
            case 5:
                str = '星期五';
                break;
            case 6:
                str = '星期六';
                break;
        }
        return str;
    }
    function chose(e){
        $(e).addClass('chosedStage').siblings('.canChose').removeClass('chosedStage')
        $(e).parents('.dayStageLine').siblings('.dayStageLine').find('.canChose').removeClass('chosedStage')
        if($('.rdidNum').text().trim().length>0){
            $('.confStageBtn').removeAttr('disabled')
        }
    }
    $('.search').click(function(){
        var idCard = $('#rdid').val();
        if(!validateIdCard(idCard)){
            layer.open({
                content: '请输入有效的身份证号'
                ,skin: 'msg'
                ,time: 2
            });
            return;
        }
        $.ajax({
            url:"/webWechat/deposit/user/order/refund/getReadersByIdcard",
            type:'post',
            dataType:'json',
            data:{
                idCard:idCard,
                token : "{{$token}}",
                _token:'{{csrf_token()}}'
            },
            success:function(res){
                if(res.status == 'successful'){
                    var data = [];
                        if (res.data.result) {
                            var temp = {'id':res.data.result.rdid,'value':res.data.result.rdid};
                            data.push(temp);
                        }
                    if(data.length<1){
                        layer.open({
                            content: '暂无可预约读者证号'
                            ,skin: 'msg'
                            ,time: 2
                        });
                        return;
                    }
                    new IosSelect(1,
                        [data], {
                            title: '选择读者证号',
                            callback: function (selectOneObj) {
                                $(".rdidNum").text(selectOneObj.id);
                                if($('li.chosedStage').length > 0){
                                    $('.confStageBtn').removeAttr('disabled')
                                }
                                getDeposit()
                            }
                        });
                }else{
                    layer.open({
                        content: '读者证获取失败'
                        ,skin: 'msg'
                        ,time: 2
                    });
                }
            },
            error:function(){
                layer.open({
                    content: '网络繁忙，稍后再试'
                    ,skin: 'msg'
                    ,time: 2
                });
            }
        })
    })
    function getDeposit(){
        $.ajax({
            url:"/webWechat/deposit/user/order/refund/ajaxGetMoney",
            type:'POST',
            dataType:'json',
            data:{
                rdid:$(".rdidNum").text(),
                token : "{{$token}}",
                _token:'{{csrf_token()}}'
            },
            success:function(res){
                if(res.status == 'successful'){
                    $('.jine').html(res.deposit.toFixed(2))
                }else{
                    $('.confStageBtn').attr('disabled',true)
                    $('.jine').html('00.00')
                    layer.open({content: '读者证不存在',skin: 'msg',time: 2 });
                }
            },
            error:function(){
                $('.jine').html('00.00')
            }
        })
    }
    $('.confStageBtn').click(function(){
        $('.confStageBtn').attr('disabled',true);
        //var rdid = $('#rdid').val().trim();
        var rdid = $('.rdidNum').text().trim();
        var date = $('.cur').attr('data-value')
        var time = $('.chosedStage>p').attr('data-value')
        var money = $('.jine').html()
        if(rdid.length<1){
            layer.open({content: '请选择读者证号',skin: 'msg',time: 2 });
            return ;
        }

        var index = layer.open({type: 2,content: '数据提交中...',shade: 'background-color: rgba(0,0,0,.4)',shadeClose:false});
        setTimeout(function(){
            $.ajax({
                url:"/webWechat/deposit/user/order/refund/subscribe",
                type:'POST',
                dataType:'json',
                data:{
                    rdid:rdid,
                    date:date,
                    time: time,
                    money:money,
                    from:1,
                    token : "{{$token}}",
                    _token:'{{csrf_token()}}'
                },
                success:function(data){
                    layer.close(index)
                    layer.open({content: data.message,skin: 'msg',time: 2 });
                    if(data.status == 'successful'){
                        setTimeout(function(){
                            window.location.href="/webWechat/deposit/user/order/refund/{{$token}}";
                        },2000)
                    }
                    $('.confStageBtn').removeAttr('disabled')
                },
                error:function(){
                    layer.close(index)
                    $('.confStageBtn').removeAttr('disabled')
                }
            })
        },1000)
    })
    $(function(){
        //demo示例一到四 通过lass调取，一句可以搞定，用于页面中可能有多个导航的情况
        $('.wrapper').navbarscroll();

    });
    layer.open({
        content: '<p style="text-align:left;text-indent:.8rem;line-height:1.55;font-size: .4rem;">{{$data['notice']}}<p>'
        ,btn: '我知道了'
    });

</script>
</body>
</html>