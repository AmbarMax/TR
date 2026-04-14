<div class="modal show new-item-modal fade" id="edit-item-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
            <div class="col-12 text-center">
                <h2>Update item</h2>
            </div>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <div class="modal-body">
                <form action="{{route('admin.items.update', ['id' => $item->id])}}" method="POST" id="edit-item-form"
                      class="row g-3 fv-plugins-bootstrap5 fv-plugins-framework"
                      enctype="multipart/form-data">

                    @csrf
                    <div class="col-12 mb-2">
                        <div class="form-group" id="item-image">
                            <div class="d-flex justify-content-center mt-1 mb-1 js-item-image">
                                <img id="image" class="item image-selected item-img">
                                <input hidden="" type="file" name="image" class="image-input">
                            </div>
                        </div>
                        <div class="d-flex justify-content-center img-btn">
                            <button type="button" class="btn btn-outline-secondary js-upload-item">Set Image</button>
                        </div>
                    </div>

                    <div class="col-12 mb-2 col-md-8 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateitemTitle">Title *</label>
                        <div class="input-group input-group-merge">
                            <input required type="text" class="form-control show-title" name="name"/>
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">
                            @error('name'){{ $message }}@enderror</div>
                    </div>

                    <div class="col-12 mb-2 col-md-4">
                        <label for="fp-role-picker">Type</label>
                        <select id="fp-role-picker-item-status" class="form-control" name="status">
                            <option value="0">
                                Skin
                            </option>
                            <option value="1">
                                Discount
                            </option>
                        </select>
                    </div>

                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalCreateitemDescription"> Description</label>
                        <div class="input-group input-group-merge">
                            <textarea type="text" class="form-control show-description" name="description"></textarea>
                        </div>
                        <span class="is-invalid" role="alert">
                            <strong>@error('description'){{ $message }}@enderror</strong>
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
    .item {
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

