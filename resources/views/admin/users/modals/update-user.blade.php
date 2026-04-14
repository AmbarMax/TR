<div class="modal show new-user-modal fade" id="edit-user-modal" tabindex="-1" aria-modal="true" role="dialog" >
    <div class="modal-dialog modal-lg modal-simple modal-edit-user">
        <div class="modal-content p-2 p-md-2">
            <div class="modal-header">
                <div class="col-12 text-center">
                    <h2>Edit User Information</h2>
                </div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" id="edit-user-form" class="row g-3 fv-plugins-bootstrap5 fv-plugins-framework" enctype="multipart/form-data">
                    @csrf
                    <div class="col-12 mb-2">
                        <div class="form-group d-flex flex-column align-items-center user-background-image position-absolute" id="avatar-image-update">
                            <div class="mt-1 mb-1">
                                <img src="https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_640.png" alt="" class="user-avatar image-selected">
                                <input hidden="" type="file" name="avatar" class="image-input">
                            </div>
                        </div>
                        <div class="form-group" id="background-image-update">
                            <div class="mt-1 mb-1 user-avatar-image">
                                <img class="user-background image-selected">
                                <input hidden="" type="file" name="background" class="image-input">
                            </div>
                        </div>
                        <div class="img-btn">
                            <button type="button" class="btn btn-outline-secondary js-upload-image-avatar-update"> Change Avatar </button>
                            <button type="button" class="btn btn-outline-secondary js-upload-image-background-update">Change Background</button>
                        </div>
                    </div>

                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalEditUserFirstName">Name *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="user"></i></span>
                            </div>
                            <input required type="text" class="form-control" name="name" placeholder="Joe"/>
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">@error('name'){{ $message }}@enderror</div>
                    </div>

                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalEditUserName">Username *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="user"></i></span>
                            </div>
                            <input required type="text" class="form-control" name="username" placeholder="joe"/>
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">@error('username'){{ $message }}@enderror</div>
                    </div>
                    <div class="col-12 col-md-6">
                        <label class="form-label" for="modalEditUserEmail">Email *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="mail"></i></span>
                            </div>
                            <input required type="text" class="form-control" name="email" placeholder="joedoe@gmail.com"/>
                        </div>
                        <div class="fv-plugins-message-container invalid-feedback">@error('email'){{ $message }}@enderror</div>
                    </div>


                    <div class="col-12 mb-2 col-md-6">
                        <label class="form-label" for="modalEditUserPhone">Phone Number</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="phone"></i></span>
                            </div>
                            <input type="text" class="form-control" name="phone_number" placeholder="+1230000000000"/>
                        </div>
                    </div>

                    <div class="col-md-6 col-12 mb-2">
                        <label for="fp-birth-date-picker">Birth Date</label>
                        <input
                            type="text"
                            id="fp-birth-date-picker"
                            data-format="format_date_of_birth"
                            class="form-control js-birth-date-picker"
                            name="date_of_birth"
                        />
                    </div>

                    <div class="col-md-6 col-12 mb-2">
                        <label for="fp-email-verified-at-picker">Email verified at</label>
                        <input
                            type="text"
                            id="fp-email-verified-at-picker"
                            data-format="format_email_verified_at"
                            class="form-control js-email-verified-at-picker"
                            name="email_verified_at"
                        />
                    </div>

                    <div class="col-12 mb-2 col-md-6">
                        <label class="form-label" for="modalEditUserEmail">Password</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="lock"></i></span>
                            </div>
                            <input type="password" class="form-control" name="password"/>
                        </div>
                    </div>

                    <div class="col-12 mb-2 col-md-6">
                        <label style="margin-bottom: 8px">Two-factor authentication</label></br>
                        <div id="2fa-active">
                            <label>Active</label>
                            <button style="margin-left: 10px" type="button" class="js-disable-2fa btn-sm btn-danger me-sm-3 me-1 waves-effect waves-light">Disable</button>
                        </div>
                        <div id="2fa-inactive">
                            <label>Inactive</label>
                        </div>
                    </div>

                    <div class="col-12 mb-2 col-md-6">
                        <label for="fp-role-picker">Role</label>
                        <select id="fp-role-picker" class="form-control" name="roles">
                            <option value="" id="empty-role-updated">Select role</option>
                        @foreach($roles as $role)
                                <option value="{{ $role->id }}">
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
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
