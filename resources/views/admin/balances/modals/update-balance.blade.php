<div class="modal show new-balance-modal fade" id="edit-balance-modal" tabindex="-1" aria-modal="true" role="dialog" >
    <div class="modal-dialog modal-sm modal-simple modal-edit-balance">
        <div class="modal-content p-2 p-md-2">
            <div class="modal-header bg-white">
                <div class="col-12 text-center bg-white">
                    <h2>Edit User Balance</h2>
                </div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="edit-balance-form" class="row g-3 fv-plugins-bootstrap5 fv-plugins-framework" enctype="multipart/form-data">
                    @csrf
                    <div class="col-4 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalEditAmbar">Ambar</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"></span>
                            </div>
                            <input required type="number" class="form-control" name="ambar" min="0" />
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">@error('ambar'){{ $message }}@enderror</div>
                    </div>

                    <div class="col-4 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalEditUru">Uru</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"></span>
                            </div>
                            <input required type="number" class="form-control" name="uru" min="0" />
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">@error('uru'){{ $message }}@enderror</div>
                    </div>


                    <div class="col-4 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalEditRune">Rune</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"></span>
                            </div>
                            <input required type="number" class="form-control" name="rune" min="0" />
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">@error('rune'){{ $message }}@enderror</div>
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" class="btn btn-primary me-sm-3 me-1 waves-effect waves-light">Submit</button>
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

    label {
        font-size: 1rem;
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
    }
</style>
