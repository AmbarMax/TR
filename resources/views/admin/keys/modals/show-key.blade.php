<div class="modal show show-key-modal fade" id="show-key-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
                <div class="col-12 text-center"></div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            <div class="modal-body row">
                <div class="col-12 mb-2">
                        <div class="form-group" id="key-image">
                            <label class="d-flex justify-content-center">Image key</label>
                            <div class="d-flex justify-content-center mt-1 mb-1 js-key-image">
                                <img id="image" class="key key-closed image-selected">
                            </div>
                        </div>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label>Name:</label>
                        <div class="key-data show-title"></div>
                    </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label>Quantity:</label>
                        <div class="key-data show-quantity"></div>
                    </div>

                <div class="col-12 text-center">
                    <button type="submit" data-dismiss="modal" data-bs-dismiss="modal" class="btn btn-danger me-sm-3 me-1 waves-effect waves-light">Close</button>
                </div>


            </div>
        </div>
    </div>
</div>

<style>
    label {
        font-size: 1.125rem;
    }

    .key-data {
        font-weight: 600;
        font-size: 1.125rem;
    }

    .image-selected {
        object-fit: cover;
    }

    .modal-header {
        background-color: unset!important;
    }

    h2 {
        font-weight: 600;
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
        z-index: 15;
    }
</style>
