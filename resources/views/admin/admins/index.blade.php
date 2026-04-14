@extends('layouts.app-admin')

@section('title', 'Admins')

@section('header-button')
    @include('admin.parts.header-button', [
    'name' => 'Create', 'class' => 'js-admin-create',
    'target' => 'create-admin-modal', 'title' => 'Admin'])
@endsection

@section('content')
    <section class="app-admins-list">
        <div class="card">
            @include('admin.admins.tables.index', ['users' => $users])
        </div>
    </section>
    @include('admin.admins.modals.create-user')
    @include('admin.admins.modals.update-user')
@endsection

@push('scripts')
    @vite(['resources/admin/js/pages/admins.js'])
@endpush
