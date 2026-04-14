@extends('layouts.app-admin')

@section('title', 'Chests')

@section('header-button')
    @include('admin.parts.header-button', [
    'name' => 'Create', 'class' => 'js-chest-create',
    'target' => 'create-chest-modal', 'title' => 'Chests'])
@endsection

@section('content')
    <section class="app-user-list">
        <div class="card">
            @include('admin.chests.tables.index', ['chests' => $chests])
        </div>
    </section>
    @include('admin.chests.modals.create-chest')
    @include('admin.chests.modals.update-chest')
    @include('admin.chests.modals.show-chest')
@endsection

@push('scripts')
    @vite(['resources/admin/js/pages/chests.js'])
@endpush
