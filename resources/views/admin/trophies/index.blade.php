@extends('layouts.app-admin')

@section('title', 'Trophies')

@section('header-button')
    @include('admin.parts.header-button', [
    'name' => 'Create', 'class' => 'js-trophy-create',
    'target' => 'create-trophy-modal', 'title' => 'Trophies'])
@endsection

@section('content')
    <div class="container">
        <section id="trophies" class="row">
            @foreach($trophies as $trophy)
                @include('admin.trophies.card')
            @endforeach
        </section>
    </div>
    @include('admin.trophies.modals.create-trophy')
    @if(count($trophies) != 0)
        @include('admin.trophies.modals.show-trophy')
        @include('admin.trophies.modals.update-trophy')
    @endif

@endsection

@push('scripts')
    @vite(['resources/admin/js/pages/trophies.js'])
@endpush
