@extends('layouts.app-admin')

@section('title', 'Exchanges')

@section('header-button')
    @include('admin.parts.header-button', ['title' => 'Exchanges'])
@endsection

@section('content')
    <section class="app-user-list">
        <div class="card">
            @include('admin.exchanges.tables.index', ['exchanges' => $exchanges])
        </div>
    </section>
    @include('admin.exchanges.modals.update-status')
@endsection

@push('scripts')
    @vite(['resources/admin/js/pages/exchanges.js'])
@endpush
