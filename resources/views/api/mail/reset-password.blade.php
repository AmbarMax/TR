<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset</title>
    @vite(['resources/views/api/mail/css/styles.css'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">

</head>
<body>
<div class="wrapper">
    <div class="header gradient-grey-bg">
        <img src="{{Vite::apiMail('logo.svg')}}" alt="Forgot password V2" />
    </div>

    <div class="content">
        <div class="content-header content-block">
            <h2 class="mail_title">Reset Password</h2>
        </div>
        <div class="content-block center-block">
            <div class="content-wrapper">
                <div class="block-icon gradient-grey-bg">
                    <img src="{{Vite::apiMail('lock.svg')}}" alt="Forgot password V2" />
                </div>
            </div>
            <div class="content-wrapper">
                <h1 class="greeting_text">Dear {{$name}},</h1>
            </div>
            <div class="content-wrapper">
                <p class="mail_content">You are receiving this email because we received a password reset request for your account.</p>
            </div>
            <div class="content-button">
                <a href="{{$link}}" class="btn-link">Reset password</a>
            </div>

            <div class="content-wrapper">
                <p class="mail_notes">This password reset link will expire in 60 minutes. If you did not request a password reset, no further action is required.</p>
            </div>

            <div class="content-wrapper">
                <h2 class="mail_regards">Regards, Ambar</h2>
            </div>
        </div>
    </div>

</div>


</body>
</html>
