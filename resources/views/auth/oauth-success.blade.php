<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Signing you in — TrophyRoom</title>
    <style>
        body {
            background: #000003;
            color: #feeddf;
            font-family: 'Share Tech Mono', monospace;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .wrap { text-align: center; }
        .msg {
            font-size: 13px;
            color: #ff6100;
            letter-spacing: 0.25em;
            text-transform: uppercase;
        }
        .sub {
            font-size: 11px;
            color: #9a9590;
            letter-spacing: 0.1em;
            margin-top: 12px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="msg" id="msg">Signing you in...</div>
        <div class="sub" id="sub">Redirecting to your dashboard.</div>
    </div>
    <script>
        (function () {
            try {
                var token = @json($token);
                var redirect = @json($redirect);
                if (!token) throw new Error('missing token');
                localStorage.setItem('access_token', token);
                window.location.replace(redirect);
            } catch (e) {
                document.getElementById('msg').textContent = 'Sign-in failed';
                document.getElementById('sub').textContent = 'Returning to login...';
                setTimeout(function () {
                    window.location.replace('/login?google_error=handoff_failed');
                }, 2000);
            }
        })();
    </script>
</body>
</html>
