@extends('emails.layout')
@section('content')

    @include('emails.components.section-label', [
        'text'  => '// two-factor authentication · disable request',
        'color' => '#ff6100',
    ])

    <h1 style="margin:0 0 28px 0; font-family:'VT323','JetBrains Mono','Fira Code','Cascadia Code','SF Mono',Menlo,Consolas,'Courier New',monospace; font-size:48px; color:#1a1a1a; line-height:1.05; letter-spacing:0.02em; font-weight:normal;">Disable <em style="font-style:normal; color:#ff6100;">2FA</em>?</h1>

    @include('emails.components.body-text', [
        'content' => 'Hey ' . e($name ?? 'there') . ',',
    ])

    @include('emails.components.body-text', [
        'content' => 'You requested to disable two-factor authentication on your account. Confirming this will lower your account security to single-password protection.',
    ])

    @include('emails.components.body-text', [
        'content' => 'This link will expire in <span style="color:#7da91a; font-weight:bold;">60 minutes</span>.',
    ])

    @include('emails.components.cta-button', [
        'url'   => $url,
        'label' => 'Confirm Disable 2FA →',
        'color' => '#ff6100',
    ])

    @include('emails.components.url-fallback', [
        'url' => $url,
    ])

    @include('emails.components.disclaimer', [
        'severity' => 'critical',
        'content'  => '<strong style="font-weight:bold; color:#ff6100;">CRITICAL:</strong> if you did NOT request this, do nothing and your 2FA stays active. Then go to Settings → Security and review your access logs. Consider rotating your password immediately.',
    ])

@endsection
