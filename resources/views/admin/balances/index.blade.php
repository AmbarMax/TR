@extends('layouts.app-admin')

@section('title', 'Balances')


@section('content')
    <section class="app-balance-list">
        <div class="card">
            @include('admin.balances.tables.index', ['users' => $users])
        </div>
    </section>
    @include('admin.balances.modals.update-balance')
@endsection

@push('scripts')
    @vite(['resources/admin/js/pages/balance.js'])
@endpush
