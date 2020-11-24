<script src="{{ admin_asset ("wechatAdmin/js/sweetalert2.js") }}"></script>
@foreach($js as $j)
    @if($j!='vendor/laravel-admin/sweetalert2/dist/sweetalert2.min.js')
        <script src="{{ admin_asset ("$j") }}"></script>
    @endif
@endforeach
