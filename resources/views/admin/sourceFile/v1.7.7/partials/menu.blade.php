@if(Admin::user()->visible(\Illuminate\Support\Arr::get($item, 'roles', [])) && Admin::user()->can(\Illuminate\Support\Arr::get($item, 'permission')))
    @php
        $isshowWxmenu=1;
    @endphp
    @foreach($item['roles'] as $ro)
        @if($ro['slug']=='wechat.config' )
            @php
                $isshowWxmenu=2;
            @endphp
            @if(request()->session()->get('wxtoken'))
                @php
                    $isshowWxmenu=3;
                @endphp
            @endif
        @endif
    @endforeach
    {{--类型  1 跟 3 才显示出来菜单--}}
    @if($isshowWxmenu==1|| $isshowWxmenu==3)
        @if(!isset($item['children']))
            <li>
                @if(url()->isValidUrl($item['uri']))
                    <a href="{{ $item['uri'] }}" target="_blank">
                        @else
                            <a href="{{ admin_url($item['uri']) }}">
                                @endif
                                <i class="fa {{$item['icon']}}"></i>
                                @if (Lang::has($titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item['title'])))))
                                    <span>{{ __($titleTranslation) }}</span>
                                @else
                                    <span>{{ admin_trans($item['title']) }}</span>
                                @endif
                            </a>
            </li>
        @else
            <li class="treeview">
                <a href="#">
                    <i class="fa {{ $item['icon'] }}"></i>
                    @if (Lang::has($titleTranslation = 'admin.menu_titles.' . trim(str_replace(' ', '_', strtolower($item['title'])))))
                        <span>{{ __($titleTranslation) }}</span>
                    @else
                        <span>{{ admin_trans($item['title']) }}</span>
                    @endif
                    <i class="fa fa-angle-left pull-right"></i>
                </a>
                <ul class="treeview-menu">
                    @foreach($item['children'] as $item)
                        @include('admin::partials.menu', $item)
                    @endforeach
                </ul>
            </li>
        @endif
    @endif
@endif
