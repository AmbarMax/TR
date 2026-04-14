@extends('layouts.app-admin')

@section('title', 'Assignment of trophies')


@section('content')
    <section class="app-balance-list">
        <div class="card">
            @include('admin.assignment-of-trophies.tables.index', ['users' => $users])
        </div>
    </section>
    @include('admin.assignment-of-trophies.modals.update-assignment-of-trophies')
@endsection

@push('scripts')
    @vite(['resources/admin/js/pages/assignment-of-trophies.js'])
@endpush
