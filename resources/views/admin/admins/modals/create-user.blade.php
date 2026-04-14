<div class="modal show new-admin-modal fade" id="create-admin-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
            <div class="modal-header">
                <div class="col-12 text-center">
                    <h2>Create new admin</h2>
                </div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.admins.store')}}" method="POST" id="invite-client-form" class="add-new-admin row g-3 fv-plugins-bootstrap5 fv-plugins-framework" enctype="multipart/form-data">
                    @csrf


                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label>Username *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="user"></i></span>
                            </div>
                            <input required type="text" class="form-control" name="name" placeholder="joe"/>
                        </div>
                        <span class="is-invalid" role="alert"><strong>@error('username'){{ $message }}@enderror</strong></span>
                    </div>
                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label>Email *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="mail"></i></span>
                            </div>
                            <input required type="text" class="form-control" name="email" placeholder="joedoe@gmail.com"/>
                        </div>
                        <span class="is-invalid" role="alert"><strong>@error('email'){{ $message }}@enderror</strong></span>
                    </div>


                    <div class="col-12 mb-2">
                        <label>Password *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="lock"></i></span>
                            </div>
                            <input required type="password" class="form-control" name="password"/>
                        </div>
                        <span class="is-invalid" role="alert"><strong>@error('password'){{ $message }}@enderror</strong></span>
                    </div>

                    <div class="col-12 mb-2">
                        <div class="input-group input-group-merge">
                            <label style="margin-right: 10px">Super admin</label>
                            <input style="margin-bottom: 3px" type="checkbox" name="super_admin"/>
                        </div>
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" id="create-admin-submit" class="btn btn-primary me-sm-3 me-1 waves-effect waves-light">Create</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<style>
    img {
        object-fit: cover;
    }
    .user-avatar {
        height: 100px; width: 100px; border-radius: 100px;
    }
    .user-background {
        width: 100%;
        height: 120px;
        background-position: center;
        background-size: contain;
        border-radius: 5px;
    }
    .user-background-image {
        margin: 68px 0 0 30px;
        z-index: 1;
    }
    .img-btn {
        display: flex;;
        justify-content: space-between;
        margin: 65px 12px 0 12px;
    }
</style>
