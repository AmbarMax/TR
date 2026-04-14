<div class="card-datatable table-responsive pt-0">
    <table id="users-index-table" class="user-list-table table">
        <thead class="thead">
        <tr>
            <th>Uuid</th>
            <th>Avatar</th>
            <th>Display Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone Number</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        </thead>
        <thbody>
            @foreach($users as $user)
                <tr>
                    <td>{{$user->short_id}}</td>
                    <td>
                        <span class="avatar">
                            <img class="round"
                                 src = "{{ $user->getAvatarFile(\App\Enums\AvatarType::Small()) }}"
                                 style="background-position: center;background-size: cover;"
                                 height="40" width="40">
                        </span>
                    </td>
                    <td>{{$user->name}}</td>
                    <td>{{$user->username}}</td>
                    <td>{{$user->email}}</td>
                    <td>{{$user->phone_number}}</td>
                    <td>{{ \Carbon\Carbon::parse($user->created_at)->format('M j, Y') }}</td>
                    <td>
                        <a class="js-show-user-btn" href="javascript:void(0);"
                           data-url="{{route('admin.users.show', ['id' => $user->id])}}"
                           data-method="GET">
                            <i class="mr-50 mb-25" data-feather='eye'></i>
                        </a>
                        <a class="js-edit-user-btn" href="javascript:void(0);"
                           data-url="{{route('admin.users.edit', ['id' => $user->id])}}"
                           data-method="GET">
                            <i class="mr-50 mb-25" data-feather='edit-2'></i>
                        </a>
                        <a class="js-confirmation-swal" href="javascript:void(0);"
                           data-url="{{route('admin.users.delete', ['id' => $user->id])}}"
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
