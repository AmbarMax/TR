<div class="col-12 col-md-6 col-lg-3">
    <a class="js-show-item-btn" href="javascript:void(0);"
       data-url="{{route('admin.items.show', ['id' => $item->id])}}"
       data-method="GET">

        <div class="card h-100">
            <div class="item-img item-img text-center">
                <img src="{{ url('storage/trophies/'.$item->image) }}" class="img-fluid" alt="{{$item->image}}" />
            </div>
            <div class="card-body">
                <div class="item-name">
                    <h3>{{$item->name}}</h3>
                </div>
                <div class="item-wrapper">
                    <div class="item-cost">
                        <h6 class="item-price">{{$item->description }}</h6>
                    </div>
                </div>
                <div class="design-group mb-50">
                    {{ \App\Enums\ItemType::getName($item->type) }}
                </div>

            </div>
        </div>

    </a>
</div>

<style>
    .item-img img{
        object-fit: cover;
        width: 100%;
        height: 200px;
    }
    .badge {
        white-space: normal;
    }

</style>
