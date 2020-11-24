<div class="row">
    @foreach($list as $k => $v)
        <div class="col-md-{{round(12/$loop->count)}}">
            <div class="small-box bg-{{$v['color']}}">
                <div class="inner">
                    <h3>{{$v['info']}}</h3>
                    <p>{{$v['title']}}</p>
                </div>
                <div class="icon">
                    <i class="fa fa-{{$v['icon']}}"></i>
                </div>
            </div>
        </div>
    @endforeach
</div>