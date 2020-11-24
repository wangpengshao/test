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
    <title>预约记录</title>
    <script src="{{asset('wechatWeb/deposit/wap/js/common.js')}}" type="text/javascript" charset="utf-8"></script>
    <script src="{{asset('wechatWeb/deposit/wap/js/jquery-1.11.1.min.js')}}" type="text/javascript" charset="utf-8"></script>
    <script src="{{asset('wechatWeb/deposit/wap/js/leftScrollDel.js')}}" type="text/javascript" charset="utf-8"></script>
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/deposit/wap/css/base.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('wechatWeb/deposit/wap/css/index.css')}}" />
    <link rel="stylesheet" href="{{asset('wechatWeb/deposit/wap/css/iosSelect.css')}}">
    <style>
        html,body{
            -moz-user-select: none;
            -khtml-user-select: none;
            user-select: none;
        }
        .dayStage{
            font-weight: 700;
            color: #5fafeb;
        }
        .red{
            color: red;
        }
        .line-wrapper {
            width: 100%;
            height:3.5rem;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            position: relative;
            overflow: hidden;
            margin-bottom: .32rem;
            box-shadow: 0 0px 10px 0px #d2d2d2;
        }
        .line-scroll-wrapper {
            white-space: nowrap;
            clear: both;
        }
        .line-btn-delete {
            float: left;
            width: 2rem;
            height:3.5rem;
            background: red;
        }

        .line-btn-delete button {
            width: 100%;
            height: 100%;
            border: none;
            font-size:.36rem;
            color: #fff;
            background:none;
        }
        .line-normal-wrapper {
            float: left;
        }
        .line-normal-left-wrapper {
            width: 100%;
            float: left;
            overflow: hidden;

        }
        .line-normal-info-wrapper {
            width: 100%;
            float: left;
            -webkit-box-sizing: border-box;
            box-sizing: border-box;
            position: relative;
        }
        .dayStageLine2 {
            background: #fff;
            padding: 0 .44rem;
            margin-bottom: .32rem;
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
<body>
<div class="headTab">
    <ul class="headTabUl">
        <li class="headTabLi">
            <a href="/webWechat/deposit/wap/order/refund/{{$token}}" class="headTabLink">预约</a>
        </li>
        <li class="headTabLi headTabCur">
            <a href="/webWechat/deposit/wap/order/refund/depositLog/{{$token}}" class="headTabLink">记录</a>
        </li>
    </ul>
    <div class="readCardArea rdid" >
        <input type="text" placeholder="请输入您办证所留的身份证号码" id="rdid" @if (isset($data['idCard'])) value="{{$data['idCard']}}" @endif />
        <button type="submit" class="search"></button>
    </div>
</div>

<div class="conArea">
    <notempty name="data">
        @if (isset($data))
        <ul>
            @foreach ($data as $item)
                <li class="line-wrapper">
                    <div class="line-scroll-wrapper">
                        <div class="line-normal-wrapper">
                            <div class="line-normal-left-wrapper">
                                <div class="line-normal-info-wrapper">
                                    <div class="dayStageLine2">
                                        <div class="dayStageLineTop">
                                            <span class="dayStage hisTm">{{$item['yuyue_date']}}&nbsp;{{$item['yuyue_time']}}</span>
                                            <switch name="item[status]">
                                                @switch($item['status'])
                                                    @case(0)<span class="surpNum surpNumIn">待处理</span>@break
                                                    @case(1)<span class="surpNum hisTm">已退款</span>@break
                                                    @case(2)<span class="surpNum red">逾约</span>@break
                                                    @case(3)<span class="surpNum hisTm">已取消</span>@break
                                                    @default 默认情况
                                                @endswitch
                                            </switch>
                                        </div>
                                        <div class="hisCon">
                                            <div class="hisConLine">
                                                <span>办理时间:</span>
                                                <span class="surpNum dayTm">{{$item['create_time']}}</span>
                                            </div>
                                            <div class="hisConLine">
                                                <span>读者证号:</span>
                                                <span class="surpNum dayTm">{{$item['rdid']}}</span>
                                            </div>
                                            <div class="hisConLine" >
                                                <span>押金:</span>
                                                <span class="surpNum dayTm">{{$item['deposit']}}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>ii
                        </div>
                        @if ($item['status']==0)
                            <div class="line-btn-delete" onclick="cancel(this,'{{$item['id']}}','{{$item['rdid']}}','3')" data-id="{{$item['rdid']}}"><button>取消预约</button></div>
                            @else
                            <div class="line-btn-delete" onclick="cancel(this,'{{$item['id']}}','{{$item['rdid']}}','4')" data-id="{{$item['rdid']}}"><button>删除记录</button></div>
                        </if>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
        @else
        <div class="orderStatuHint" id="tips" >
            <div class="orderStatuImg orderFailImg"></div>
            <p class="statuFail">暂无数据!</p>
        </div>
        @endif
    </notempty>
</div>
<script src="{{asset('wechatWeb/deposit/wap/js/layer.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/wap/js/iosSelect.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/wap/js/validateIdCard.js')}}" type="text/javascript" charset="utf-8"></script>
<script>
    function cancel(obj,id,rdid,type){
        var tips = type == 3 ? "你确定要取消本次预约吗？" : "你确定要删除当前预约记录吗？";

        layer.open({
            content: tips
            ,btn: ['确定', '再想想']
            ,yes: function(index){
                layer.close(index);
                var index2 = layer.open({type: 2, content: '处理中', shade: 'background-color: rgba(0,0,0,.3)'});
                $.ajax({
                    url: "/webWechat/deposit/user/order/refund/cancelDeposit",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'type': type,
                        'rdid': rdid,
                        'depositId': id,
                        _token:'{{csrf_token()}}'
                    },
                    success: function (res) {
                        setTimeout(function () {
                            layer.open({
                                content: res.data.msg
                                ,skin: 'msg'
                                ,time: 2
                            });
                            layer.close(index2);
                            $(obj).prev('div').css('margin-left',0)
                            if(res.data.errCode == 200) $(obj).parents('li').remove();
                        }, 1500)

                    },
                    error: function () {
                        setTimeout(function () {
                            layer.open({
                                content: '网络繁忙，稍后再试'
                                ,skin: 'msg'
                                ,time: 2
                            });
                        }, 1500)
                    }
                })
            },
            no: function(index){
                location.reload();
                layer.close(index);
            }
        });

    }

    $('.search').click(function(){
        // var rdid = $('#rdid').val().trim();
        // window.location.href="{weimicms::U('Deposit/depositLog',array('token'=>$_GET['token']))}"+'&rdid='+rdid;

        var idCard = $('#rdid').val().trim();
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
                            var temp = {'id': res.data.result.rdid, 'value': res.data.result.rdid};
                            data.push(temp);
                    }
                    new IosSelect(1,
                        [data], {
                            title: '选择读者证号',
                            callback: function (selectOneObj) {
                                window.location.href="/webWechat/deposit/wap/order/refund/depositLog/{{$token}}/"+selectOneObj.id;
                                /*$(".rdidNum").text(selectOneObj.id);
                                if($('li.chosedStage').length > 0){
                                    $('.confStageBtn').removeAttr('disabled')
                                }*/
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
</script>
</body>
</html>