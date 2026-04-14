<!DOCTYPE html>
<html lang="">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('title') | {{config('app.name')}}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{asset('favicon.ico')}}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">

    @vite(['resources/admin/scss/app.scss'])
    @stack('styles')
</head>

<body class="vertical-layout vertical-menu-modern fixed-navbar" data-open="click" data-menu="vertical-menu-modern" data-col="">

@include('admin.parts.header')
@include('admin.parts.menu')

<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
            @yield('header-button')
        </div>
        <div class="content-body">
            @yield('content')
        </div>
    </div>
</div>

@include('admin.parts.footer')
@include('admin.parts.confirmation-form')
@vite(['resources/admin/js/app.js'])

@if(session()->has('notification'))
    <div class="d-none" id="session-notification-data"
         data-type="{{session()->get('notification')['type']}}"
         data-title="{{session()->get('notification')['title']}}"
         data-message="{{session()->get('notification')['message']}}"></div>
@endif

@stack('scripts')

</body>
</html>
