<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<a href="#" id="test" onclick="test()">测试支付</a>
</body>

<script type="text/javascript" charset="utf-8"   src="http://res.wx.qq.com/open/js/jweixin-1.4.0.js"></script>
<script type="text/javascript" charset="utf-8">



    function test() {
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest', <?= $json ?>,
            function(res){
                if(res.err_msg == "get_brand_wcpay_request:ok" ) {
                    // 使用以上方式判断前端返回,微信团队郑重提示：
                    // res.err_msg将在用户支付成功后返回
                    // ok，但并不保证它绝对可靠。
                }
            }
        );
    }

</script>
</html>
