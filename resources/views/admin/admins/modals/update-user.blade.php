<div class="modal show new-admin-modal fade" id="edit-admin-modal" tabindex="-1" aria-modal="true" role="dialog" >
    <div class="modal-dialog modal-lg modal-simple modal-edit-admin">
        <div class="modal-content p-2 p-md-2">
            <div class="modal-header">
                <div class="col-12 text-center">
                    <h2>Edit Admin Information</h2>
                </div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="edit-admin-form" class="row g-3 fv-plugins-bootstrap5 fv-plugins-framework" enctype="multipart/form-data">
                    @csrf
                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalEditAdminName">Username *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="user"></i></span>
                            </div>
                            <input required type="text" class="form-control" id="username" name="name" placeholder="Joe"/>
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">@error('name'){{ $message }}@enderror</div>
                    </div>

                    <div class="col-12 mb-2">
                        <label class="form-label" for="modalEditAdminEmail">Email *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="mail"></i></span>
                            </div>
                            <input required type="text" class="form-control" name="email" placeholder="joedoe@gmail.com"/>
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">@error('email'){{ $message }}@enderror</div>
                    </div>



                    <div class="col-12 mb-2">
                        <label class="form-label" for="modalEditAdminPassword">Password</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="lock"></i></span>
                            </div>
                            <input type="password" class="form-control" name="password"/>
                        </div>
                    </div>

                    <div class="col-12 mb-2">
                        <div class="input-group input-group-merge">
                            <label style="margin-right: 10px">Super admin</label>
                            <input id="super_admin_checkbox" style="margin-bottom: 3px" type="checkbox" name="super_admin"/>
                        </div>
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
</style>
