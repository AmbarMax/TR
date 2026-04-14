<div class="modal show new-user-modal fade" id="create-user-modal">
    <div class="modal-dialog modal-lg modal-simple">
        <div class="modal-content p-2 p-md-2">
            <div class="modal-header">
                <div class="col-12 text-center">
                    <h2>Create new user</h2>
                </div>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="{{route('admin.users.store')}}" method="POST" id="invite-client-form" class="add-new-user row g-3 fv-plugins-bootstrap5 fv-plugins-framework" enctype="multipart/form-data">
                    @csrf
                    <div class="col-12 mb-2">
                        <div class="form-group d-flex flex-column align-items-center user-background-image position-absolute" id="avatar-image">
                            <div class="mt-1 mb-1">
                                <img id="user-avatar" class="user-avatar image-selected">
                                <input hidden="" type="file" name="avatar" class="image-input">
                            </div>
                        </div>
                        <div class="form-group" id="background-image">
                            <div class="mt-1 mb-1 user-avatar-image">
                                <img id="user-background" class="user-background image-selected">
                                <input hidden="" type="file" name="background" class="image-input">
                            </div>
                        </div>
                        <div class="img-btn">
                            <button type="button" class="btn btn-outline-secondary js-upload-image-avatar"> Change Avatar </button>
                            <button type="button" class="btn btn-outline-secondary js-upload-image-background">Change Background</button>
                        </div>
                    </div>

                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label class="form-label" for="modalEditUserFirstName"> Name *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="user"></i></span>
                            </div>
                            <input required type="text" class="form-control" name="name" placeholder="Joe"/>
                        </div>
                        <span class="is-invalid" role="alert"><strong>@error('name'){{ $message }}@enderror</strong></span>
                    </div>
                    <div class="col-12 mb-2 fv-plugins-icon-container">
                        <label>Username *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="user"></i></span>
                            </div>
                            <input required type="text" class="form-control" name="username" placeholder="joe"/>
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
                    <div class="col-12 mb-2 col-md-6">
                        <label>Phone number </label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="phone"></i></span>
                            </div>
                            <input type="text" class="form-control" name="phone_number" placeholder="+1230000000000"/>
                        </div>
                        <span class="is-invalid" role="alert"><strong>@error('phone_number'){{ $message }}@enderror</strong></span>
                    </div>
                    <div class="col-12 mb-2 col-md-6">
                        <label for="fp-birth-date-picker">Birth Date</label>
                        <input
                            type="text"
                            id="fp-birth-date-picker"
                            class="form-control js-birth-date-picker"
                            name="date_of_birth"
                        />
                    </div>
                    <div class="col-12 mb-2 col-md-6">
                        <label for="fp-email-verified-at-picker">Email verified at</label>
                        <input
                            type="text"
                            id="fp-email-verified-at-picker"
                            class="form-control js-email-verified-at-picker"
                            name="email_verified_at"
                        />
                    </div>
                    <div class="col-12 mb-2 col-md-6">
                        <label>Password *</label>
                        <div class="input-group input-group-merge">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i data-feather="lock"></i></span>
                            </div>
                            <input required type="password" class="form-control" name="password"/>
                        </div>
                        <span class="is-invalid" role="alert"><strong>@error('password'){{ $message }}@enderror</strong></span>
                    </div>

                    <div class="col-12 mb-2 col-md-6">
                        <label for="fp-role-picker">Role</label>
                        <select id="fp-role-picker" class="form-control" name="role">
                            <option value="" selected>Select role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id}}" name="role">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 text-center">
                        <button type="submit" id="create-user-submit" class="btn btn-primary me-sm-3 me-1 waves-effect waves-light">Create</button>
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
