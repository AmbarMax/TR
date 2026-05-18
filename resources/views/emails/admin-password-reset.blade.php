@extends('emails.layout', [
    'tagline'        => 'Admin console',
    'taglineColor'   => '#7da91a',
    'stripDirection' => 'green-first',
])

@section('content')

    @include('emails.components.section-label', [
        'text'  => '// admin access · password reset',
        'color' => '#7da91a',
    ])

    <h1 style="margin:0 0 28px 0; font-family:'VT323','JetBrains Mono','Fira Code','Cascadia Code','SF Mono',Menlo,Consolas,'Courier New',monospace; font-size:48px; color:#1a1a1a; line-height:1.05; letter-spacing:0.02em; font-weight:normal;">Reset admin <em style="font-style:normal; color:#7da91a;">credentials</em>.</h1>

    @include('emails.components.body-text', [
        'content' => 'A password reset was requested for your admin account.',
    ])

    @include('emails.components.body-text', [
        'content' => "This grants full access to the TrophyRoom admin console. If you didn't initiate this, contact the security lead immediately.",
    ])

    @include('emails.components.body-text', [
        'content' => 'This link will expire in <span style="color:#7da91a; font-weight:bold;">60 minutes</span>.',
    ])

    @include('emails.components.cta-button', [
        'url'   => $url,
        'label' => 'Reset Admin Password →',
        'color' => '#7da91a',
    ])

    @include('emails.components.url-fallback', [
        'url' => $url,
    ])

    @include('emails.components.disclaimer', [
        'severity' => 'notice',
        'content'  => '<strong style="font-weight:bold; color:#7da91a;">SECURITY NOTICE:</strong> admin accounts have elevated privileges. Never share this link. If you suspect unauthorized access, revoke this token from the admin console.',
    ])

@endsection
