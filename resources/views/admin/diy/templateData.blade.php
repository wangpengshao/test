<div class="form-group {!! !$errors->has($label) ?: 'has-error' !!}">

    <label for="{{$id}}" class="col-sm-2 control-label">{{$label}}</label>

    <div class="{{$viewClass['field']}}">

        @include('admin::form.error')
        <select class="form-control {{ $class }}" id="{{$id}}" name="{{$id}}" style="width: 100%;">
            <option value="">空</option>
            @foreach($currentList as $select => $option)
                <option value="{{$option['template_id']}}" {{ (string)$option['template_id'] === request($name, $value) ?'selected':'' }}>
                    {{$option['template_id']."=>".$option['title']}}
                </option>
            @endforeach
        </select>
        @include('admin::form.help-block')
    </div>
</div>

<template class="{{$column}}-tpl">
    <div class="has-many-{{$column}}-form fields-group">
        <div class="form-group  ">
            <label class="col-sm-2  control-label">__LABEL__</label>
            <div class="col-sm-8">
                <div class="input-group"><span class="input-group-addon"><i class="fa fa-pencil fa-fw"></i></span>
                    <input type="text" name="{{$jsonKey}}[__JSONKEY__]" value="__JSONVAL__" class="form-control ">
                </div>
            </div>
        </div>
    </div>
</template>

<template class="{{$column}}-tpl-emArr">
    <input type="hidden" value="" name="{{$jsonKey}}[]">
</template>
