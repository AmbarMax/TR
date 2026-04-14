<div class="card-datatable table-responsive pt-0">
    <table id="assignment-of-trophies-index-table" class="balance-list-table table">
        <thead class="thead">
        <tr>
            <th>Uuid</th>
            <th>Username</th>
            <th>Numbers of trophies</th>
            <th>Actions</th>
        </tr>
        </thead>
        <thbody>
            @foreach($users as $user)
                <tr>
                    <td>{{$user['short_id']}}</td>
                    <td>{{$user['username']}}</td>
                    <td>{{$user->trophies()->count()}}</td>
                    <td>
                        <a class="js-edit-assignment-of-trophies-btn" href="javascript:void(0);"
                           data-url="{{route('admin.assignment-of-trophies.edit', ['id' => $user['id']])}}"
                           data-method="GET">
                            <i class="mr-50 mb-25" data-feather='edit-2'></i>
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
