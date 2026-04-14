<div class="card-datatable table-responsive pt-0">
    <table id="keys-index-table" class="user-list-table table">
        <thead class="thead">
        <tr>
            <th>Uuid</th>
            <th>Name</th>
            <th>Quantity</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
            @foreach($keys as $key)
                <tr>
                    <td>{{$key->short_id}}</td>
                    <td>{{$key->name}}</td>
                    <td>{{$key->quantity}}</td>
                    <td>{{ \Carbon\Carbon::parse($key->created_at)->format('M j, Y') }}</td>
                    <td>
                        <a class="js-show-key-btn" href="javascript:void(0);"
                           data-url="{{route('admin.keys.show', ['id' => $key->id])}}"
                           data-method="GET">
                            <i class="mr-50 mb-25" data-feather='eye'></i>
                        </a>
                        <a class="js-edit-key-btn" href="javascript:void(0);"
                           data-url="{{route('admin.keys.edit', ['id' => $key->id])}}"
                           data-method="GET">
                            <i class="mr-50 mb-25" data-feather='edit-2'></i>
                        </a>
                        <a class="js-confirmation-swal" href="javascript:void(0);"
                           data-url="{{route('admin.keys.delete', ['id' => $key->id])}}"
                           data-method="DELETE">
                            <i class="mr-50 mb-25 text-danger" data-feather='trash'></i>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
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
