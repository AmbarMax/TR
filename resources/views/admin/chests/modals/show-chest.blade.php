<div class="modal show show-chest-modal fade" id="show-chest-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
                <div class="col-12 text-center"></div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            <div class="modal-body row">
                    <div class="col-6 mb-2">
                        <label class="d-flex justify-content-center">The image of a closed chest</label>
                        <div class="form-group" id="chest-image">
                            <div class="d-flex justify-content-center mt-1 mb-1 js-chest-image">
                                <img id="image" class="chest chest-closed image-selected">
                            </div>
                        </div>
                    </div>

                <div class="col-6 mb-2">
                    <label class="d-flex justify-content-center">The image of an opened chest</label>
                    <div class="form-group" id="chest-image">
                        <div class="d-flex justify-content-center mt-1 mb-1 js-chest-image">
                            <img id="image" class="chest chest-opened image-selected">
                        </div>
                    </div>
                </div>

                <div class="col-6 mb-2 fv-plugins-icon-container">
                    <label>Title:</label>
                    <div class="chest-data show-title"></div>
                </div>

                <div class="col-6 mb-2 fv-plugins-icon-container">
                    <label>Quantity:</label>
                    <div class="chest-data show-quantity"></div>
                </div>

                <div class="col-6 mb-2 fv-plugins-icon-container">
                    <label>Key:</label>
                    <div class="chest-data show-key-name"></div>
                </div>

                <div class="col-6 mb-2 fv-plugins-icon-container">
                    <label>Expiration date:</label>
                    <div class="chest-data show-expiration-date"></div>
                </div>

                <div class="col-12 mb-2 fv-plugins-icon-container">
                    <label>Items:</label>
                    <div style="gap: 8px; display: flex" class="design-group mb-50 js-chest-show">
                    </div>
                </div>

                    <div class="col-6 mb-2 fv-plugins-icon-container">
                        <label>Description:</label>
                        <div class="chest-data show-description"></div>
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

    h2 {
        font-weight: 600;
    }

    .image-selected {
        object-fit: cover;
    }

    .chest-data {
        font-weight: 600;
        font-size: 1.125rem;
    }

    .modal-header {
        background-color: unset!important;
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
