<div class="modal show-user-modal fade" id="show-user-modal">
        <div class="modal-dialog modal-lg modal-simple" role="document">
            <div class="modal-content p-2 p-md-2">

                <div class="modal-header">
                    <div class="col-12 text-center">
                        <h2>Account details</h2>
                    </div>
                    <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form class="row g-3 fv-plugins-bootstrap5 fv-plugins-framework">
                        <div class="col-12 mb-2">
                            <div class="form-group d-flex flex-column align-items-center user-background-image position-absolute" id="avatar-image-show">
                                <div class="mt-1 mb-1">
                                    <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_640.png" alt="" class="user-avatar image-selected">
                                </div>
                            </div>
                            <div class="form-group" id="background-image-show">
                                <div class="mt-1 mb-1 user-avatar-image">
                                    <img class="user-background image-selected">
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mb-2 col-md-6" style="margin-top: 50px;">
                            <label>Name:</label>
                            <div class="user-data show-name"></div>
                        </div>
                        <div class="col-12 mb-2 col-md-6" style="margin-top: 50px;">
                            <label>Username:</label>
                            <div class="user-data show-username"></div>
                        </div>
                        <div class="col-12 mb-2 col-md-6">
                            <label>Email:</label>
                            <div class="user-data show-email"></div>
                        </div>
                        <div class="col-12 mb-2 col-md-6">
                            <label>Phone number:</label>
                            <div class="user-data show-phone"></div>
                        </div>
                        <div class="col-12 mb-2 col-md-6">
                            <label>Birth Date:</label>
                            <div class="user-data show-birth-date"></div>
                        </div>
                        <div class="col-12 mb-2 col-md-6">
                            <label>Email verified at:</label>
                            <div class="user-data show-email-verified-at"></div>
                        </div>
                        <div class="col-12 mb-2 col-md-6">
                            <label>Role:</label>
                            <div class="user-data show-roles"></div>
                        </div>
                    </form>
                </div>
                <div class="col-12 text-center">
                    <button type="submit" data-dismiss="modal" data-bs-dismiss="modal" class="btn btn-danger me-sm-3 me-1 waves-effect waves-light">Close</button>
                </div>
            </div>
        </div>
</div>

<style>
    label {
        font-size: 1.125rem;
    }

    .user-data {
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
    }
</style>
