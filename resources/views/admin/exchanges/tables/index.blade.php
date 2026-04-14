<div class="card-datatable table-responsive pt-0">
    <table id="exchanges-index-table" class="user-list-table table">
        <thead class="thead">
        <tr>
            <th>Uuid</th>
            <th>Uru amount</th>
            <th>Cryptocurrency Amount	</th>
            <th>Wallet number</th>
            <th>Status</th>
            <th>User name</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
            @foreach($exchanges as $exchange)
                <tr>
                    <td>{{$exchange->short_id}}</td>
                    <td>{{$exchange->input_amount}} {{$exchange->input_currency}}</td>
                    <td>{{$exchange->output_amount}} {{$exchange->output_currency}}</td>
                    <td>{{$exchange->wallet_number}}</td>
                    <td>{{$exchange->statusName}}</td>
                    <td>{{$exchange->userName}}</td>
                    <td>{{ \Carbon\Carbon::parse($exchange->created_at)->format('M j, Y') }}</td>
                    <td>
                        @if ($exchange->status == \App\Enums\ExchangeStatus::PENDING)
                        <a class="js-edit-exchange-btn" href="javascript:void(0);"
                           data-url="{{route('admin.exchanges.edit', ['id' => $exchange->id])}}"
                           data-method="GET">
                            <i class="mr-50 mb-25" data-feather='edit-2'></i>
                        </a>
                        @endif
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
