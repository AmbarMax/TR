<div class="modal show new-user-modal fade" id="edit-exchange-modal" tabindex="-1" aria-modal="true" role="dialog" >
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
            <div class="modal-header">
                <div class="col-12 text-center">
                    <h2>Edit Exchange Status</h2>
                </div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="edit-exchange-form" class="row g-3 fv-plugins-bootstrap5 fv-plugins-framework" enctype="multipart/form-data">
                    @csrf

                    <div class="col-12 mb-2 col-md-4" style="margin-top: 30px;">
                        <label>Uru Amount:</label>
                        <div class="user-data show-uru"></div>
                    </div>
                    <div class="col-12 mb-2 col-md-4" style="margin-top: 30px;">
                        <label>Cryptocurrency Amount:</label>
                        <div class="user-data show-cryptocurrency"></div>
                    </div>
                    <div class="col-12 mb-2 col-md-4" style="margin-top: 30px;">
                        <label>Wallet number:</label>
                        <div class="user-data show-wallet"></div>
                    </div>

                    <div class="col-12 mb-2 col-md-6">
                        <label for="fp-role-picker">Status</label>
                        <select id="fp-role-picker-exchange-status" class="form-control" name="status">
                            <option value="0">
                                Pending
                            </option>
                            <option value="1">
                                Paid
                            </option>
                            <option value="2">
                                Canceled
                            </option>
                        </select>
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1 waves-effect waves-light">Update</button>
                        <button type="reset" class="btn btn-label-secondary waves-effect" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close">
                            Cancel
                        </button>
                    </div>

                    <input type="hidden"></form>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-header {
        background-color: unset!important;
    }
    label {
        font-size: 1rem;
    }
    h2 {
        font-weight: 600;
    }
    .user-data {
        font-weight: 600;
        font-size: 1.125rem;
    }
</style>
