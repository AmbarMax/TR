<div class="modal show new-chest-modal fade" id="create-chest-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
            <div class="col-12 text-center">
                <h2>Create new chest</h2>
            </div>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body">
                <form action="{{route('admin.chests.store')}}" method="POST" id="crate-chest-form"
                      class="add-new-chest row g-3 fv-plugins-bootstrap5 fv-plugins-framework"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="col-6 mb-2">
                        <div class="d-flex justify-content-center img-btn">
                            <label class="form-label">
                                The image of a closed chest
                            </label>
                        </div>
                        <div class="form-group" id="chest-image">
                            <div class="d-flex justify-content-center mt-1 mb-1 js-chest-image">
                                {{--<div class="chest-image-alt"></div>--}}
                                <img style="display: block" id="image" class="chest image-selected-closed chest-image" alt="">
                                <input hidden="" type="file" name="image_closed" class="image-input-closed input-img">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center img-btn">
                            <button type="button" class="btn btn-outline-secondary js-upload-chest-closed">Set image</button>
                        </div>
                    </div>

                    <div class="col-6 mb-2">
                        <div class="d-flex justify-content-center img-btn">
                            <label class="form-label">
                                The image of an open chest
                            </label>
                        </div>
                        <div class="form-group" id="chest-image">
                            <div class="d-flex justify-content-center mt-1 mb-1 js-chest-image">
                                {{--<div class="chest-image-alt"></div>--}}
                                <img style="display: block" id="image" class="chest image-selected-opened chest-image" alt="">
                                <input hidden="" type="file" name="image_opened" class="image-input-opened input-img">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center img-btn">
                            <button type="button" class="btn btn-outline-secondary js-upload-chest-opened">Set image</button>
                        </div>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreatechestTitle"> Title *</label>
                        <div class="input-group input-group-merge">
                            <input required type="text" class="form-control" name="name"/>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('name'){{ $message }}@enderror</strong>
                        </span>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container" id="max-nft-supply">
                        <label class="form-label" for="modalCreatechestPrice"> Quantity *</label>
                        <div class="input-group input-group-merge">
                            <input required type="number" class="form-control" name="max_supply" value="1"/>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('max_supply'){{ $message }}@enderror</strong>
                        </span>
                    </div>

                    <div class="col-6 mb-2">
                        <label class="form-label" for="modalCreatechestBadges"> Key *</label>

                        <select class="js-create-key-select" required name="key">
                            <option value="" disabled selected>Select items</option>
                            @foreach($keys as $key)
                                <option value="{{$key->id}}">{{$key->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 mb-2 col-md-6">
                        <label for="fp-expiration-date-at-picker">Expiration date</label>
                        <input
                            type="text"
                            id="fp-expiration-date-at-picker"
                            class="form-control js-expiration-date-at-picker"
                            name="expiration_date"
                        />
                    </div>

                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreatechestBadges"> Items *</label>

                        <select class="js-create-chest-select" required name="items[]">
                            <option value="" disabled selected>Select items</option>
                            @foreach($items as $item)
                                <option value="{{$item->id}}">{{$item->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreatechestDescription"> Description</label>
                        <div class="input-group input-group-merge">
                            <textarea type="text" class="form-control" name="description"></textarea>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('description'){{ $message }}@enderror</strong>
                        </span>
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1 waves-effect waves-light">Create</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<style>
    b {
        display: none;
    }
    .chest-image {
        object-fit: cover;
        overflow: hidden;
        border-radius: 5px;
        background-color: lightgray;
        display: none;
    }
    .chest-image-alt {
        height: 200px;
        width: 200px;
        border-radius: 5px;
        background-color: lightgray;
        display: block;
    }

    .chest {
        height: 200px; width: 200px; border-radius: 0px;
    }
    .btn-close {
        position: absolute;
        scale: 2;
        border: unset;
        background-color: unset;
        margin: 0!important;
        right: 15px;
        top: 15px;
        box-shadow: unset!important;
    }
</style>
<script>
    function maxSupplyDisplay() {
        let isNft = document.getElementById('flexSwitchCheckDefault').checked;
        let maxSupplyInput = document.getElementById('max-nft-supply');
        if (isNft) {
            maxSupplyInput.classList.remove('hidden');
            maxSupplyInput.setAttribute('required', 'required');
        } else {
            maxSupplyInput.classList.add('hidden');
            maxSupplyInput.removeAttribute('required');
        }
    }
    const inputImg = document.querySelector('.input-img');

    inputImg.addEventListener('change', () => {
        document.querySelector('.chest-image-alt').style.display = 'none';
        document.querySelector('.chest-image').style.display = 'block';
    });
</script>
