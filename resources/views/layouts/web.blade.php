<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=VT323&display=swap" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <title>TrophyRoom</title>
    @vite('resources/web/js/app.js')
</head>
<body>
<div id="web-app"></div>
</body>
</html>

<script>
    var token = @json(Session::get('token'));
    var user = @json(Session::get('user'));
    let badges = @json(Session::get('achievements'));
    if (localStorage.getItem('access_token') == null){
        if (token != null){
            localStorage.setItem('access_token', token);
        }
        if (user != null){
            localStorage.setItem('user', user);
        }
    }
    if (badges != null){
        let user = localStorage.getItem('user');
        window.location.href = `/badges/sync/${JSON.parse(user).id}/${badges}`;
    }
    var websocket_url = @json(config('broadcasting.connections.centrifugo.websocket_client'));
    localStorage.setItem('websocket_url', websocket_url);
</script>
