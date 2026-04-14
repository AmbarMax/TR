@extends('layouts.app-admin')

@section('title', 'Users')

@section('header-button')
    @include('admin.parts.header-button', [
    'name' => 'Create', 'class' => 'js-user-create',
    'target' => 'create-user-modal', 'title' => 'Users'])
@endsection

@section('content')
    <section class="app-user-list">
        <div class="card">
            @include('admin.users.tables.index', ['users' => $users])
        </div>
    </section>
    @include('admin.users.modals.create-user')
    @include('admin.users.modals.update-user')
    @include('admin.users.modals.show-user')
@endsection

@push('scripts')
    @vite(['resources/admin/js/pages/users.js'])
@endpush
