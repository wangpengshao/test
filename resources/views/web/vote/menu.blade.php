<div class="text_a clearfix">
    <a href="{!! $urlArr['indexUrl'] !!}" class="{{request()->route()->named('Vote::index') ? 'active':''}}">投票列表</a>
    <a href="{!! $urlArr['rankUrl'] !!}" class="{{request()->route()->named('Vote::rank') ? 'active':''}}">投票排名</a>
    @if($groupList->count() > 1)
        <a href="javascript:;" id="des">分组切换</a>
        <select id="groupSwitch" style="opacity:0;width: 0px;height: 0px">
            <option value="">保持现状</option>
            @foreach($groupList as $val)
                <option value="{{$val->id}}">{{$val->title}}</option>
            @endforeach
        </select>
    @endif
</div>
