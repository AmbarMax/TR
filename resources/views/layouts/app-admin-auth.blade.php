<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      class="{{Cookie::get('theme')}}"><head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title> @yield('title') | {{config('app.name')}}</title>
    <meta name="description" content="{{ config('app.name', 'Laravel') . ' Login' }}">
    @vite(['resources/admin/scss/app.scss', 'resources/admin/scss/pages/page-auth.scss'])
</head>
<body class="vertical-layout vertical-menu-modern blank-page navbar-floating footer-static" data-open="click"
      data-menu="vertical-menu-modern" data-col="blank-page">
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">
            @yield('content')
        </div>
    </div>
</div>
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
