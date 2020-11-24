@extends('web.collectCard.app')
@section('jsResources')
    <script type="text/javascript" src="{{asset('common/js/message.js')}}"></script>
    <script type="text/javascript" charset="utf-8">
        let indexUrl = "{!! $indexUrl !!}";
        @empty(!request()->session()->get('alertInfo'))
        alert("{{request()->session()->get('alertInfo')}}").then(function () {
            window.location.href = indexUrl;
        });
        @else
        alert("当前页面已失效，正在为你跳转首页").then(function () {
            window.location.href = indexUrl;
        });
        @endempty
    </script>
@endsection


