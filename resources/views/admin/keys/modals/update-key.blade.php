<div class="modal show new-key-modal fade" id="edit-key-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
            <div class="col-12 text-center">
                <h2>Update key</h2>
            </div>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body">
                <form action="{{route('admin.keys.update', ['id' => 0])}}" method="POST" id="edit-key-form"
                      class="row g-3 fv-plugins-bootstrap5 fv-plugins-framework"
                      enctype="multipart/form-data">

                    @csrf
                    <div class="col-12 mb-2">
                        <div class="form-group" id="key-image">
                            <div class="d-flex justify-content-center mt-1 mb-1 js-key-image">
                                {{--<div class="key-image-alt"></div>--}}
                                <img style="display: block" id="image" class="key key-closed image-selected-closed key-image" alt="">
                                <input hidden="" type="file" name="image" class="image-input-closed input-img">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center img-btn">
                            <button type="button" class="btn btn-outline-secondary js-upload-key-closed">Set key image</button>
                        </div>
                    </div>

                    <div class="col-8 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreatekeyTitle">Title *</label>
                        <div class="input-group input-group-merge">
                            <input required type="text" class="form-control show-title" name="name"/>
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">
                            @error('name'){{ $message }}@enderror</div>
                    </div>

                    <div class="col-4 mb-2 fv-plugins-icon-container" id="max-nft-supply">
                        <label class="form-label" for="modalCreatekeyPrice"> Quantity *</label>
                        <div class="input-group input-group-merge">
                            <input required type="number" class="form-control show-quantity" name="quantity" value="1"/>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('max_supply'){{ $message }}@enderror</strong>
                        </span>
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1 waves-effect waves-light">Update</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<style>
    /*img {*/
    /*    object-fit: cover;*/
    /*}*/
    .key {
        height: 200px; width: 200px; border-radius: 5px;
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

