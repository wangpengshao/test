<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>素材库</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
    <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
    <link rel="stylesheet" href="{{ admin_asset("wechatAdmin/css/sweetalert2.min.css") }}">
    <!-- REQUIRED JS SCRIPTS -->
    <script src="{{ admin_asset ("/vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js") }}"></script>
    <script src="{{ admin_asset ("/vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js") }}"></script>
    <script src="{{ admin_asset ("wechatAdmin/js/sweetalert2.js") }}"></script>
    <script src="{{ admin_asset ("wechatAdmin/js/jquery.lazyload.min.js") }}"></script>
    {{--    <script type="text/javascript" charset="utf-8"--}}
    {{--            src="https://cdn.staticfile.org/jquery.lazyload/1.9.1/jquery.lazyload.min.js"></script>--}}
</head>

<body class="hold-transition {{config('admin.skin')}} {{join(' ', config('admin.layout'))}}">

<div class="wrapper">
    {{--<div class="row">--}}
    <div class="col-md-12">
        <ul class="nav nav-pills">
            <li role="presentation" class="@if(app('request')->route()->named('wechat.imgList')) active  @endif ">
                <a href="{{route('wechat.imgList')}}">公众号素材</a></li>
            <li role="presentation" class="@if(app('request')->route()->named('wechat.uweiIconList')) active  @endif ">
                <a href="{{route('wechat.uweiIconList')}}">图创icon库</a></li>
            {{--<li role="presentation"><a href="#">共享区</a></li>--}}
        </ul>
    </div>
    {{--</div>--}}
    @yield('content')
</div>
</body>
<script>
    $(function () {
        $("img.lazy").lazyload();
        $("[data-toggle='tooltip']").tooltip();
    });

    function newtest(e) {

        swal({
            title: '提示',
            type: 'info',
            html: '确定使用这张图片吗？',
            showCloseButton: true,
            showCancelButton: true,
            confirmButtonText: '确定',
            cancelButtonText: '取消',

        }).then(function () {
            let name = e.data('name');
            let url = e.data('url');
            window.parent.clearTest(name, url);
        }).catch(swal.noop);

    }

    function copyUrl(e) {
        let url = e.data('url');
        const input = document.createElement('input');
        document.body.appendChild(input);
        input.setAttribute('value', url);
        input.select();
        if (document.execCommand('copy')) {
            document.execCommand('copy');
            alert('复制成功');
        }
        document.body.removeChild(input);
    }
</script>
</html>
