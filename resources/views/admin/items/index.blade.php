@extends('layouts.app-admin')

@section('title', 'Items')

@section('header-button')
    @include('admin.parts.header-button', [
    'name' => 'Create', 'class' => 'js-item-create',
    'target' => 'create-item-modal', 'title' => 'Items'])
@endsection

@section('content')
    <div class="container">
        <section id="trophies" class="row">
            @foreach($items as $item)
                @include('admin.items.card')
            @endforeach
        </section>
    </div>
    @include('admin.items.modals.create-item')
    @if(count($items) != 0)
        @include('admin.items.modals.show-item')
        @include('admin.items.modals.update-item')
    @endif

@endsection

@push('scripts')
    @vite(['resources/admin/js/pages/items.js'])
@endpush
