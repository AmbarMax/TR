<div class="content-header-left col-md-9 col-12 mb-2">
    <h2>{{$title}}</h2>
</div>
@if(isset($name))
    <div class="content-header-right float-right text-md-right col-md-3 col-12 d-md-block d-none">
        <div class="form-group breadcrumb-right">
            @if(isset($href))
                <a class="btn-icon btn btn-primary btn-round" type="button" href="{{$href}}">
                    {!! $name !!}
                </a>
            @else
                <button class="btn-icon btn btn-primary btn-round {{$class}}" type="button" data-toggle="modal" data-target="#{{$target}}">
                    {!! $name !!}
                </button>
            @endif
        </div>
    </div>
@endif
