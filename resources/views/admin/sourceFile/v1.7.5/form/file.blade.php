<div class="{{$viewClass['form-group']}} {!! !$errors->has($errorKey) ? '' : 'has-error' !!}">

    <label for="{{$id}}" class="{{$viewClass['label']}} control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')

        <input type="file" class="{{$class}}" name="{{$name}}" {!! $attributes !!} />

        @include('admin::form.help-block')

    </div>

    @if( request()->session()->get('wxtoken')  && !str_is("hideMaterial*", $attributes) )
        <button onclick="showModal('{{route("wechat.imgList")}}','{{$class}}','素材','70%','80%')" type="button"
                class="btn btn-primary  ">
            <span class="glyphicon glyphicon-file" aria-hidden="true"></span>
        </button>
    @endif
</div>
