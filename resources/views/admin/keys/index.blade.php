@extends('layouts.app-admin')

@section('title', 'Keys')

@section('header-button')
    @include('admin.parts.header-button', [
    'name' => 'Create', 'class' => 'js-chest-create',
    'target' => 'create-key-modal', 'title' => 'Keys'])
@endsection

@section('content')
    <section class="app-user-list">
        <div class="card">
            @include('admin.keys.tables.index', ['keys' => $keys])
        </div>
    </section>
    @include('admin.keys.modals.create-key')
    @include('admin.keys.modals.update-key')
    @include('admin.keys.modals.show-key')
@endsection

@push('scripts')
    @vite(['resources/admin/js/pages/keys.js'])
@endpush
