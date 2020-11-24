<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge,chrome=1">
    <!--#以上代码IE=edge告诉IE使用最新的引擎渲染网页，chrome=1则可以激活Chrome Frame.-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!--以上代码告诉IE浏览器，IE8/9及以后的版本都会以最高版本IE来渲染页面。  -->
    <title>预约</title>
    <link rel="stylesheet" href="{{asset('wechatWeb/deposit/user/css/index.css')}}" type="text/css" media="all">
    <link rel="stylesheet" href="{{asset('wechatWeb/deposit/user/css/reset_pc.css')}}" type="text/css" media="all">
</head>
<body style="height:100%;">
<div class="outBg " id="first">
    <div class="centerCon resGetArea resGetTmArea">
        <div class="resGetTop">
            <ul class="resGetUl">
                <li class="resGetLi active" id="form"><a href="javascript:void(0)" class="resGetLink">预约退证</a></li>
                <li class="resGetLi " id="logs"><a href="javascript:void(0)" class="resGetLink">预约记录</a></li>
            </ul>
        </div>
        <div class="resGetCon form">
            <div class="resGetTit"><i class="resLine resLeftLine"></i>请补充您的预约信息<i class="resLine resRightLine"></i>
            </div>
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <form action="" method="post" class="resForm">
                @csrf
                <div class="resFmGrop position-r">
                    <span class="resFmTit">身份证号</span>
                    <input type="text" class="resFmInp" name="idCard" id="idCard" value="" placeholder="请输入身份证号"/>
                    <span class="confirm"></span>
                </div>
                <div class="resFmGrop">
                    <span class="resFmTit">读者证号</span>
                    <div class="resFmInp choResDateArea choeResOut">
                        <i class="choseDown"></i>
                        <div class="choseConArea rdidCon" data-value="">请选择读者证号</div>
                        <ul class="choseRdid choseResUl">
                            <li class="choseLi curChose">请选择读者证号</li>
                        </ul>
                    </div>
                </div>
                <div class="resFmGrop">
                    <span class="resFmTit">预约日期</span>
                    <div class="resFmInp choResDateArea choeResOut">
                        <i class="choseDown"></i>
                        <div class="choseConArea dateCon">请选择预约日期</div>
                        <ul class="choseDateUl choseResUl">
                            <li class="choseLi curChose">请选择预约日期</li>
                        </ul>
                    </div>
                </div>
                <div class="resFmGrop">
                    <span class="resFmTit">预约时段</span>
                    <div class="resFmInp choResTimeArea choeResOut">
                        <i class="choseDown"></i>
                        <div class="choseConArea timeCon">请选择预约时段</div>
                        <ul class="choseTmUl choseResUl">
                            <li class="choseLi curChose">请选择时段</li>
                        </ul>
                    </div>
                </div>
                <div class="resFmGrop moneyLine">
                    <span class="resFmTit">押金</span>
                    <p class="moneyArea"><span class="moneyNum">00.00</span>元</p>
                    <div class="resHint">*提示：输入身份证号点击"对勾"、选择读者证、日期、时段点击“提交”即可</div>
                </div>
                <div class="resFmGrop">
                    <span class="resFmTit"></span>
                    <input type="button" class="resFmInp resFmBtn" value="提  交"/>
                </div>
            </form>
        </div>
        <div class="resGetCon logs">
            <div class="search">
                <input type="text" placeholder="请输入身份证号查询" id="searchId">
                <button id="search">查询</button>
            </div>
            <div class="rdidLists">
            </div>
            <div class="content">

            </div>
        </div>
    </div>
</div>
<div class="outBg " id="suc" style="display: none;">
    <div class="centerCon resGetArea">
        <div class="resGetTop">
            <ul class="resGetUl">
                <li class="resGetLi "><a href="javascript:void(0)" class="resGetLink">预约退证</a></li>
            </ul>
        </div>
        <div class="resStatuArea">
            <div class="resSucImg"></div>
            <p class="resSucHint">预约成功！</p>
            <div class="resSucMes">
                <p>业务类型：<span class="colorRed">押金退还</span></p>
                <p>读者证号：<span class="colorRed" id="rid"></span></p>
                <p>预约日期：<span class="colorRed" id="sucmes"></span></p>
            </div>
        </div>
        <div><p class="tips">温馨提示：读者退押金前需归还图书和缴纳逾期罚款；本人办理退押金须携带身份证和借书证，代办人须携带双方身份证及借书证原件。</p></div>
    </div>
</div>
<script src="{{asset('wechatWeb/deposit/user/js/jquery-1.11.1.min.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/user/js/placeholderfriend.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/user/js/index.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/user/js/layer.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('wechatWeb/deposit/user/js/validateIdCard.js')}}" type="text/javascript" charset="utf-8"></script>
<script>
    $(function () {
        var initData = null;
        $.ajax({
            url: "/webWechat/deposit/user/order/refund/ajaxIndex/{{$token}}",
            data: {
                token: "{{$token}}",
                _token: '{{csrf_token()}}'
            },
            type: 'post',
            dataType: 'json',
            async: false,
            success: function (data) {
                initData = data;
            },
            error: function () {
                console.log(1)
            }
        });
        var dateLi = '<li class="choseLi curChose">请选择预约日期</li>';
        $.each(initData.changeData, function (i, v) {

            dateLi += '<li class="choseLi" data-value="' + i + '"><span>' + i + '</span> <span>' + getWeek(i) + '</span></li>';
        })
        $('.choseDateUl').html(dateLi)

        function getblock(a) {
            var hours = a.split(':')[0];
            if (hours >= 12) {
                return '下午 ';
            } else {
                return '上午 ';
            }
        }

        function getWeek(time) {
            var str = '';
            var myDate = new Date(time);
            switch (myDate.getDay()) {
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

        selChose('.choResDateArea');
        selChose('.choResTimeArea');

        function selChose(area) {
            $(area).click(function (e) {
                if (!$(this).hasClass('hasTap')) {
                    $(this).addClass('hasTap').parents('.resFmGrop').siblings().find('.choeResOut').removeClass('hasTap');
                } else {
                    $(this).removeClass('hasTap');
                }
                $(document).one('click', function (e) {
                    $(area).removeClass('hasTap');
                })
                e.stopPropagation();/*stopPropagation();方法可以阻止把事件分派到其他节点*/
            });
        }

        selSingle1('.choseDateUl li', '.dateCon');

        function selSingle1(li, area) {
            $(li).each(function () {
                $(area).text($(li).eq(0).text());
                $(this).click(function (e) {
                    $(this).addClass('curChose').siblings().removeClass('curChose');
                    $(area).text($(this).text());
                    var value = $(this).attr('data-value');
                    var objDate = new Date(value);
                    var week = objDate.getDay();
                    week = 'week' + week;
                    var weekData = initData['data'][week].split('-');
                    if (weekData[0] == 'false') {
                        $('div.timeCon').html('请选择预约日期')
                        $('.choseTmUl').html('');
                        alert(value + ' 闭馆，请重新选择日期');
                        return false;
                    } else if (initData.changeData[value] == 'false') {
                        $('div.timeCon').html('请选择预约日期')
                        $('.choseTmUl').html('');
                        alert(value + ' 闭馆，请重新选择日期');
                        return false;
                    } else {
                        $('#date1').attr('data-value', value);
                        $('#work').html(weekData[1] + ' - ' + weekData[2])
                        var str = '<li class="choseLi curChose">请选择时段</li>';
                        $.each(initData['changeData'][value], function (i, v) {
                            str += '<li class="choseLi" data-value="' + v + '">' + getblock(v) + ' ' + v.substr(0, 5) + '</li>';
                        })
                        $('.choseTmUl').html(str)
                        selSingle2('.choseTmUl li', '.timeCon');
                    }
                });
            });
        }

        function selSingle2(li, area) {
            $(li).each(function () {
                $(area).text($(li).eq(0).text());
                $(this).click(function (e) {
                    $(this).addClass('curChose').siblings().removeClass('curChose');
                    $(area).text($(this).text());
                });
            });
        }

        $("#rdid").bind("blur", function () {
            if ($('#rdid').val().trim().length > 0) {
                $.ajax({
                    url: "/webWechat/deposit/user/order/refund/ajaxGetMoney",
                    type: 'POST',
                    dataType: 'json',
                    data: {rdid: $("#rdid").val()},
                    success: function (data) {
                        if (data.status == 'successful') {
                            $('.moneyNum').html(data.data.deposit)
                            $('.resHint').html('*提示：输入读者证号、日期、时段点击“提交”即可')
                        } else {
                            $('.moneyNum').html('00.00')
                            $('.resHint').html('*读者证号不存在')
                        }
                    },
                    error: function () {
                        $('.moneyNum').html('00.00')
                        $('.resHint').html('*网络繁忙，稍后再试')
                    }
                })
            }
        });
        $('.resFmBtn').click(function () {
            //var rdid = $("input[name='rdid']").val();
            var rdid = $('.rdidCon')[0].dataset.value;
            var date = $(".choseDateUl >li.curChose").attr('data-value');
            var time = $(".choseTmUl  >li.curChose").attr('data-value');

            if (rdid.trim().length == 0) {
                $('.resHint').html('*请选择读者证号')
                return;
            }
            if (!date || date.length == 0) {
                $('.resHint').html('*请选择日期')
                return;
            }
            if (!time || time.length == 0) {
                $('.resHint').html('*请选择时段')
                return;
            }
            $('.resFmBtn').attr('disabled', true).val('数据提交中...')
            $.ajax({
                url: "/webWechat/deposit/user/order/refund/subscribe",
                type: 'post',
                dataType: 'json',
                data: {
                    rdid: rdid,
                    date: date,
                    time: time,
                    token: "{{$token}}",
                    _token: '{{csrf_token()}}',
                },
                success: function (res) {
                    if (res.status == 'successful') {
                        $('#first').css('display', 'none');
                        $('#suc').css('display', 'block');
                        $('#rid').text(res.data.result.rdid);
                        $('#sucmes').html(res.data.result.yuyue_date + " " + getWeek(res.data.result.yuyue_date) + " " + res.data.result.yuyue_time)
                    } else {
                        $('.resHint').html(res.message)
                        $('.resFmBtn').val('提交').removeAttr('disabled')
                    }
                },
                error: function () {
                    $('.resHint').html('网络繁忙，稍后再试')
                    $('.resFmBtn').val('提交').removeAttr('disabled')
                }
            })
        })
        $('#form').click(function () {
            $('#logs').removeClass('active');
            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
            }
            $('.logs').css('display', 'none');
            $('.form').css('display', 'block');

        })
        $('#logs').click(function () {
            $('#form').removeClass('active');
            if (!$(this).hasClass('active')) {
                $(this).addClass('active');
            }
            $('.form').css('display', 'none');
            $('.logs').css('display', 'block');
        })
        $('#search').click(function () {
            var searchId = $('#searchId').val().trim();
            if (searchId.length < 1) return;
            if (!validateIdCard(searchId)) {
                alert('请输入有效的身份证号');
                return;
            }
            $.ajax({
                url: "/webWechat/deposit/user/order/refund/getReadersByIdcard",
                type: 'post',
                dataType: 'json',
                data: {
                    idCard: searchId,
                    token: "{{$token}}",
                    _token: '{{csrf_token()}}'
                },
                success: function (res) {
                    if (res.status == 'successful') {
                        var items = '';
                        if (res.data.result) {
                            items += '<label class="rdid-item"><input type="radio" name="rdid" data-value="' + res.data.result.rdid + '"><i>✓</i>' + res.data.result.rdid + '</label>';
                        }
                        $('.rdidLists').html(items)
                        $('.rdidLists>label').eq(0).find('input').prop('checked', true)
                        getDepositLog(res.data.result.rdid)
                    }
                },
                error: function () {
                    alert('网络繁忙，稍后再试')
                }
            })

        })

        $('.content').on('click', '.cancel', cancel)

        function cancel() {
            var type = $(this).attr('data-type');
            var rdid = $(this).attr('data-rdid');
            var depositId = $(this).attr('data-id');
            var tips = type == 3 ? "你确定要取消本次预约吗？" : "你确定要删除当前预约记录吗？";
            layer.confirm(tips, {
                btn: ['确定', '再想想'] //按钮
            }, function () {
                layer.closeAll();
                var index = layer.load(0, {
                    shade: [0.2, '#fff']
                });
                $.ajax({
                    url: "/webWechat/deposit/user/order/refund/cancelDeposit",
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        'type': type,
                        'rdid': rdid,
                        'depositId': depositId,
                        _token: '{{csrf_token()}}'
                    },
                    success: function (res) {
                        setTimeout(function () {
                            layer.msg(res.data.msg);
                            layer.close(index);
                            setTimeout(function () {
                                if (res.data.errCode == 200) window.location.reload()
                            }, 3000)
                        }, 1500)

                    },
                    error: function () {
                        setTimeout(function () {
                            layer.msg('网络繁忙，稍后再试');
                        }, 1500)
                    }
                })

            }, function () {
            })
        }

        function getDepositLog(id) {
            $.ajax({
                url: "/webWechat/deposit/user/order/refund/record/" + id,
                type: 'POST',
                dataType: 'json',
                data: {
                    rdid: id,
                    token: "{{$token}}",
                    _token: '{{csrf_token()}}'
                },
                success: function (res) {
                    var str = '';
                    if (res.data.errCode == 0) {
                        $.each(res.data.result, function (i, v) {
                            var status = '';
                            var cancelStr =
                                '<div  class="cancel" title="删除记录" data-id="' + v.id +
                                '" data-rdid="' + v.rdid + '" data-type="4"></div>';
                            if (v.status == 0) {
                                status = '待处理';
                                cancelStr =
                                    '<div class="cancel" title="取消预约" data-id="' + v.id +
                                    '" data-rdid="' + v.rdid + '" data-type="3"></div>';
                            } else if (v.status == 1) {
                                status = '已退款';
                            } else if (v.status == 2) {
                                status = '逾约';
                            } else {
                                status = '已取消';
                            }
                            str += '<div class="item" style="height: 110px;">' +
                                '<div class="resLeft">' +
                                '<p>' + v.yuyue_date + '</p>\n' +
                                '<p class="padMiddle">星期二   ' + v.yuyue_time + '</p>\n' +
                                '<p>读者证号: <span class="refund">' + v.rdid +
                                '</span></p>' +
                                '</div>' +
                                '<div class="resRight">' +
                                '<p class="">' + status + '</p>' +
                                '<p>押金: <span class="resNum">' + v.deposit +
                                '</span></p>' +
                                '</div>' + cancelStr +
                                '</div>';
                        })
                    }
                    if (res.errCode == 1002) {
                        str = '<p class="tips">暂无数据</p>';
                    }
                    $('.content').html(str)
                },
                error: function () {
                    $('.content').html('<p class="tips">网络繁忙，稍后再试</p>')
                }
            })
        }

        $('.rdidLists').on('click', '.rdid-item', function () {
            var rdid = this.children[0].dataset.value;
            getDepositLog(rdid)
        })
    });
    $('.confirm').on('click', function () {
        var idCard = $('#idCard').val();
        if (!validateIdCard(idCard)) {
            alert('请输入有效的身份证号');
            return;
        }
        $.ajax({
            url: "/webWechat/deposit/user/order/refund/getReadersByIdcard",
            type: 'post',
            data: {
                idCard,
                token: "{{$token}}",
                _token: '{{csrf_token()}}',
            },
            dataType: 'json',
            success:function(res){
                if(res.status == 'successful'){
                    var dateLi = '<li class="choseLi curChose">请选择读者证号</li>';
                    if(res.data){
                        dateLi += '<li class="choseLi" data-value="'+ res.data.result.rdid +'"><span>'+ res.data.result.rdname +'</span> <span>'+ res.data.result.rdid +'</span></li>';
                    }
                    $('.choseRdid').html(dateLi)
                }
            },
            error: function () {
                $('.resHint').html('网络繁忙，稍后再试')
                $('.resFmBtn').val('提交').removeAttr('disabled')
            }
        })
    })
    $('.choseRdid').on('click', '.choseLi', function () {
        var lastVal = $('.rdidCon').text();
        var curVal = this.dataset.value;
        $('.rdidCon')[0].dataset.value = curVal;
        $('.rdidCon').text(curVal);
        $(this).addClass('curChose').siblings().removeClass('curChose');
        if (curVal !== lastVal && curVal.length > 0) {
            $.ajax({
                url: "/webWechat/deposit/user/order/refund/ajaxGetMoney",
                type: 'POST',
                dataType: 'json',
                data: {
                    token: "{{$token}}",
                    rdid: curVal,
                    _token: '{{csrf_token()}}'
                },
                success: function (data) {
                    if (data.status == 'successful') {
                        $('.moneyNum').html(data.data.deposit)
                        $('.resHint').html('*提示：输入读者证号、日期、时段点击“提交”即可')
                    } else {
                        $('.moneyNum').html('00.00')
                        $('.resHint').html('*读者证号不存在')
                    }
                },
                error: function () {
                    $('.moneyNum').html('00.00')
                    $('.resHint').html('*网络繁忙，稍后再试')
                }
            })
        }
    })
</script>
</body>
</html>








