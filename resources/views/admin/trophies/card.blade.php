<div class="col-12 col-md-6 col-lg-3">
    <a class="js-show-trophy-btn" href="javascript:void(0);"
       data-url="{{route('admin.trophies.show', ['id' => $trophy->id])}}"
       data-method="GET">

        <div class="card h-100">
            <div class="item-img trophy-img text-center">
                <img src="{{ url('storage/trophies/'.$trophy->image) }}" class="img-fluid" alt="{{$trophy->image}}" />
            </div>
            <div class="card-body">
                <div class="item-name">
                    <h3>{{$trophy->name}}</h3>
                </div>
                <div class="item-wrapper">
                    <div class="item-cost">
                        <h6 class="item-price">{{$trophy->price}} Ambar</h6>
                    </div>
                </div>
                <div class="item-wrapper">
                    <div class="item-cost">
                        <h6 class="item-price">{{$trophy->receive}} Uru</h6>
                    </div>
                </div>
                <div class="design-group mb-50">
                    @foreach($trophy->badges as $badge)
                        <div class="badge badge-light-primary">{{$badge->name}}</div>
                    @endforeach
                </div>

            </div>
        </div>

    </a>
</div>

<style>
    .trophy-img img{
        object-fit: cover;
        width: 100%;
        height: 200px;
    }
    .badge {
        white-space: normal;
    }

</style>
