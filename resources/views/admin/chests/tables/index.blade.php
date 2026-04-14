<div class="card-datatable table-responsive pt-0">
    <table id="chests-index-table" class="user-list-table table">
        <thead class="thead">
        <tr>
            <th>Uuid</th>
            <th>Name</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Key</th>
            <th>Expiration date</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
            @foreach($chests as $chest)
                <tr>
                    <td>{{$chest->short_id}}</td>
                    <td>{{$chest->name}}</td>
                    <td>{{$chest->description}}</td>
                    <td>{{$chest->quantity}}</td>
                    <td>{{$chest->key->name}}</td>
                    @if($chest->expiration_date != null)
                        <td>{{ \Carbon\Carbon::parse($chest->expiration_date)->format('M j, Y') }}</td>
                    @else
                        <td>-</td>
                    @endif
                    <td>{{ \Carbon\Carbon::parse($chest->created_at)->format('M j, Y') }}</td>
                    <td>
                        <a class="js-show-chest-btn" href="javascript:void(0);"
                           data-url="{{route('admin.chests.show', ['id' => $chest->id])}}"
                           data-method="GET">
                            <i class="mr-50 mb-25" data-feather='eye'></i>
                        </a>
                        <a class="js-edit-chest-btn" href="javascript:void(0);"
                           data-url="{{route('admin.chests.edit', ['id' => $chest->id])}}"
                           data-method="GET">
                            <i class="mr-50 mb-25" data-feather='edit-2'></i>
                        </a>
                        <a class="js-confirmation-swal" href="javascript:void(0);"
                           data-url="{{route('admin.chests.delete', ['id' => $chest->id])}}"
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
