<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html" ; charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0 user-scalable=no">
    <meta name="format-detection" content="telephone=no">
    <link rel="stylesheet" href="{{asset('wechatWeb/LuckyDraw/zhuanpan/css/font-awesome.min.css')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/LuckyDraw/zhuanpan/css/style.css')}}">
    <link rel="stylesheet" href="{{asset('wechatWeb/IntegralExchange/common/css/layer.css')}}">
<body>
<title>添加快递地址信息</title>
<!-- 从官方下载的文件放在项目的 jquery-mobile 目录中 -->
<link rel="stylesheet" href="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css"/>
{{--<script src="http://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>--}}
<style type="text/css">
    body {
        margin: 0px;
        background: #efefef;
        font-family: '微软雅黑';
        -moz-appearance: none;
    }

    a {
        text-decoration: none;
    }

    .address_addnav {
        height: 44px;
        width: 94%;
        padding: 0 3%;
        border-bottom: 1px solid #f0f0f0;
        border-top: 1px solid #f0f0f0;
        margin-top: 14px;
        line-height: 42px;
        color: #666;
        background: #fff;
    }

    .address_list {
        height: 50px;
        padding: 0 10px;
        border-bottom: 1px solid #f0f0f0;
        border-top: 1px solid #f0f0f0;
        margin-top: 14px;
        background: #fff;
    }

    .address_list .ico {
        height: 50px;
        width: 30px;
        float: left;
        color: #999;
        margin-right: -30px;
        z-index: 2
    }

    .address_list .ico i {
        font-size: 24px;
        margin-top: 15px;
        margin-left: 10px;
        z-index: 2;
        position: relative;
    }

    .address_list .info {
        height: 50px;
        width: 100%;
        float: left;
        position: relative;
    }

    .address_list .info .inner {
        margin-left: 40px;
        margin-right: 50px;
    }

    .address_list .info .inner .addr {
        height: 20px;
        width: 100%;
        color: #999;
        line-height: 26px;
        font-size: 14px;
        overflow: hidden;
    }

    .address_list .info .inner .user {
        height: 30px;
        width: 100%;
        color: #666;
        line-height: 30px;
        font-size: 16px;
        overflow: hidden;
    }

    .address_list .info .inner .user span {
        color: #444;
        font-size: 16px;
    }

    .address_list .btn {
        width: 45px;
        float: right;
        margin-left: -45px;
        z-index: 2;
        position: relative;
    }

    .address_list .btn .edit, .address_list .btn .remove {
        height: 50px;
        float: right;
        color: #999;
        font-size: 18px;
        margin-top: 5px;
    }

    .address_list .btn .edit {
        margin-right: 10px;
    }

    .address_addnav {
        height: 40px;
        border-bottom: 1px solid #f0f0f0;
        border-top: 1px solid #f0f0f0;
        margin-top: 14px;
        line-height: 40px;
        color: #666;
    }

    .address_main {
        height: auto;
        width: 94%;
        padding: 0px 3%;
        border-bottom: 1px solid #f0f0f0;
        border-top: 1px solid #f0f0f0;
        margin-top: 14px;
        background: #fff;
    }

    .address_main .line {
        height: 44px;
        width: 100%;
        border-bottom: 1px solid #f0f0f0;
        line-height: 44px;
    }

    .address_main .line input {
        float: left;
        height: 44px;
        width: 100%;
        padding: 0px;
        margin: 0px;
        border: 0px;
        outline: none;
        font-size: 16px;
        color: #666;
        padding-left: 5px;
    }

    .address_main .line select {
        border: none;
        height: 25px;
        width: 100%;
        color: #666;
        font-size: 16px;
    }

    .address_sub1 {
        height: 44px;
        margin: 14px 10px;
        background: #ff4f4f;
        border-radius: 4px;
        text-align: center;
        font-size: 16px;
        line-height: 44px;
        color: #fff;
    }

    .address_sub2 {
        height: 44px;
        margin: 14px 10px;
        background: #ddd;
        border-radius: 4px;
        text-align: center;
        font-size: 16px;
        line-height: 44px;
        color: #666;
        border: 1px solid #d4d4d4;
    }

    #BgDiv1 {
        background-color: #000;
        position: absolute;
        z-index: 9999;
        display: none;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        opacity: 0.6;
        filter: alpha(opacity=60);
    }

    .DialogDiv {
        position: absolute;
        z-index: 99999;
    }

    /*配送公告*/
    .U-user-login-btn {
        display: block;
        border: none;
        font-size: 1em;
        color: #efefef;
        line-height: 49px;
        cursor: pointer;
        height: 53px;
        font-weight: bold;
        border-radius: 3px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        width: 100%;
        box-shadow: 0 1px 4px #cbcacf, 0 0 40px #cbcacf;
    }

    .U-user-login-btn:hover, .U-user-login-btn:active {
        display: block;
        border: none;
        font-size: 1em;
        color: #efefef;
        line-height: 49px;
        cursor: pointer;
        height: 53px;
        font-weight: bold;
        border-radius: 3px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        width: 100%;
        box-shadow: 0 1px 4px #cbcacf, 0 0 40px #cbcacf;
    }

    .U-user-login-btn2 {
        display: block;
        border: none;
        font-size: 1em;
        color: #efefef;
        line-height: 49px;
        cursor: pointer;
        font-weight: bold;
        border-radius: 3px;
        -webkit-border-radius: 3px;
        -moz-border-radius: 3px;
        width: 100%;
        box-shadow: 0 1px 4px #cbcacf, 0 0 40px #cbcacf;
        height: 53px;
    }

    .U-guodu-box {
        padding: 5px 15px;
        background: #3c3c3f;
        filter: alpha(opacity=90);
        -moz-opacity: 0.9;
        -khtml-opacity: 0.9;
        opacity: 0.9;
        min-heigh: 200px;
        border-radius: 10px;
    }

    .U-guodu-box div {
        color: #fff;
        line-height: 20px;
        font-size: 12px;
        margin: 0px auto;
        height: 100%;
        padding-top: 10%;
        padding-bottom: 10%;
    }

    .nav-left {
        float: left;
    }

    .arrow-box {
        width: 50px;
        height: 26px;
        position: relative;
        border-radius: 10% 10%;
        background: #fff;
        text-align: center;
        line-height: 26px;
        top: 12px;
        font-size: 14px;
        left: 10px;
    }
</style>
<div id="container">
    <div class="page_topbar">
        <a href="{{route('LuckyDraw02::myRecord',['token'=>$data['token'],'l_id'=>$data['l_id']])}}" class="back">
            <div class="arrow-box nav-left">
                返回
            </div>
        </a>
        <div class="title">填写信息</div>
    </div>

    <div id="container">
        <div class="address_main">
            <input type="hidden" id="addressid" value="">
            <input type="hidden" id="spuid" value="3074">
            <div class="line"><input type="text" placeholder="收件人" id="realname" value=""></div>
            <div class="line"><input type="text" placeholder="联系电话" id="mobile" value=""></div>
            <div class="line">
                <!-- sel-provance -->
                <select id="s_province" name="s_province"></select><br>
            </div>
            <div class="line">
                <select id="s_city" name="s_city"></select><br><!-- sel -->
            </div>
            <div class="line">
                <!-- sel-area -->
                <select id="s_county" name="s_county"></select><br>
            </div>
            <script type="text/javascript" charset="utf-8"
                    src="{{asset('wechatWeb/LuckyDraw/zhuanpan/js/area.js')}}"></script>
            <script type="text/javascript">_init_area();</script>

            <div class="line"><input type="text" placeholder="详细地址" id="address" value=""></div>
        </div>

        <div class="address_sub1">提交</div>
{{--        <div class="address_sub2">取消</div>--}}
    </div>
    <br>
    <div id="toastId2" class="toasttj2" style="display: none; opacity: 0;"></div>
    <div id="BgDiv1"></div>
    <div class="U-login-con">
        <div class="DialogDiv" style="display:none; ">
            <div class="U-guodu-box">
                <div>
                    <table width="100%" cellpadding="0" cellspacing="0" border="0">
                        {{--<tr>
                            <td align="center"><img src="img/loading.gif"></td>
                        </tr>--}}
                        <tr>
                            <td valign="middle" align="center">提交中，请稍后！</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="cl"></div>
    </div>
</body>

<!-- <script src="http://code.jquery.com/jquery-2.1.4.min.js"></script> -->
<script type="text/javascript" charset="utf-8"
        src="{{asset('wechatWeb/LuckyDraw/zhuanpan/js/area.js')}}"></script>
<script type="text/javascript" charset="utf-8"
        src="{{asset('wechatWeb/LuckyDraw/zhuanpan/js/jquery.gcjs.js')}}"></script>
<script type="text/javascript" charset="utf-8"
        src="{{asset('wechatWeb/LuckyDraw/zhuanpan/js/jquery-1.7.1.min.js')}}"></script>
<script type="text/javascript" src="{{asset('wechatWeb/IntegralExchange/common/js/layer.js')}}"></script>

<script>
    const saveAddressUrl = '<?php echo route('LuckyDraw02::saveAddress'); ?>';
    const csrf_token = "<?php echo e(csrf_token(), false); ?>";
    const p_id = "{{$data['id']}}";  // 奖品列表的id   关联地址
    const token = "{{$data['token']}}";  // 奖品列表的id   关联地址
    $(function () {
        $('.address_sub1').click(function () {
            let formJson = {};
            let name = $('#realname').val();
            let phone = $('#mobile').val();
            let province = $('#s_province').val();
            let city = $('#s_city').val();
            let county = $('#s_county').val();
            let detail_address = $('#address').val();
            let address = province + city + county + detail_address;
            if (name.length == 0) {
                layer.open({
                    content: '请填写姓名！'
                    , skin: 'msg'
                    , time: 2
                });
                return;
            }
            if (phone.length == 0) {
                layer.open({
                    content: '请填写联系电话！'
                    , skin: 'msg'
                    , time: 2
                });
                return;
            }
            if (province.length == 0 || city.length == 0 || county.length == 0 || province == '省份' || city == '地级市' || county == '市、县级市') {
                layer.open({
                    content: '请选择省、市、区！'
                    , skin: 'msg'
                    , time: 2
                });
                return false;
            }
            if (detail_address.length == 0) {
                layer.open({
                    content: '请填写详细地址！'
                    , skin: 'msg'
                    , time: 2
                });
                return false;
            }
            if (!checkPhone(phone)) {
                layer.open({
                    content: '手机号码格式有问题！'
                    , skin: 'msg'
                    , time: 3
                });
                return false;
            }
            formJson['id'] = "{{$data['id']}}";
            formJson['l_id'] = "{{$data['l_id']}}";
            formJson['name'] = name;
            formJson['phone'] = phone;
            formJson['address'] = address;
            formJson['p_id'] = p_id;
            formJson['token'] = token;
            saveAddress(saveAddressUrl, formJson);
        });
    });

    // 验证手机号码格式
    function checkPhone(phone) {
        var mobileReg = /^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57])[0-9]{8}$/;
        if (mobileReg.test(phone)) {
            return true;
        }
        return false;
    }

    function saveAddress(saveUrl, formJson) {
        $.ajax({
            type: 'POST',
            url: saveUrl,
            dataType: "json",
            data: formJson,
            headers: {
                'X-CSRF-TOKEN': csrf_token
            },
            success: function (response) {
                if (response.status == true) {
                    layer.open({
                        content: '提交成功'
                        , skin: 'msg'
                        , time: 2
                    });
                    // window.location.reload();
                    window.location.href = '/webWechat/luckyDraw/myRecord02?token={{$data['token']}}&l_id={{$data['l_id']}}';
                }
            },
            error: function (e) {
                alert('服务繁忙,请稍后再试！')
            }
        });
    }
</script>
</html>
