<link rel="stylesheet" href="{{ admin_asset("wechatAdmin/css/sweetalert2.min.css") }}">
@foreach($css as $c)
    @if($c!='vendor/laravel-admin/sweetalert2/dist/sweetalert2.css')
        <link rel="stylesheet" href="{{ admin_asset("$c") }}">
    @endif
@endforeach
