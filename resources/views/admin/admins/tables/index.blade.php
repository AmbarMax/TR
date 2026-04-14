<div class="card-datatable table-responsive pt-0">
    <table id="admins-index-table" class="user-list-table table">
        <thead class="thead">
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
        </thead>
        <thbody>
            @foreach($users as $user)
                <tr>
                    <td>{{$user->name}}</td>
                    <td>{{$user->email}}</td>
                    @if($user->super_admin)
                        <td>Super admin</td>
                    @else
                        <td>Admin</td>
                    @endif
                    <td>
                        <a class="js-edit-admin-btn" href="javascript:void(0);"
                           data-url="{{route('admin.admins.edit', ['id' => $user->id])}}"
                           data-method="GET">
                            <i class="mr-50 mb-25" data-feather='edit-2'></i>
                        </a>
                        <a class="js-confirmation-swal" href="javascript:void(0);"
                           data-url="{{route('admin.admins.delete', ['id' => $user->id])}}"
                           data-method="DELETE">
                            <i class="mr-50 mb-25 text-danger" data-feather='trash'></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </thbody>
    </table>
</div>

<style>
    .dataTables_length {
        padding-left: 1.5rem;
        margin-top: 0.5rem;
        padding-right: 1.5rem!important;
    }
    .dataTables_filter {
        padding-right: 1.5rem!important;
    }
    .dataTables_info, .dataTables_paginate {
        padding-left: 1.5rem!important;
        padding-right: 1.5rem!important;
    }
</style>
