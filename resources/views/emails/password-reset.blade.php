@extends('emails.layout')
@section('content')

    @include('emails.components.section-label', [
        'text'  => '// password reset requested',
        'color' => '#ff6100',
    ])

    <h1 style="margin:0 0 28px 0; font-family:'VT323','JetBrains Mono','Fira Code','Cascadia Code','SF Mono',Menlo,Consolas,'Courier New',monospace; font-size:48px; color:#1a1a1a; line-height:1.05; letter-spacing:0.02em; font-weight:normal;">Reset your <em style="font-style:normal; color:#ff6100;">password</em>.</h1>

    @include('emails.components.body-text', [
        'content' => 'Hey ' . e($name ?? 'there') . ',',
    ])

    @include('emails.components.body-text', [
        'content' => 'Someone — hopefully you — requested a password reset for your TrophyRoom account. Click the button below to set a new one.',
    ])

    @include('emails.components.body-text', [
        'content' => 'This link will expire in <span style="color:#7da91a; font-weight:bold;">60 minutes</span>.',
    ])

    @include('emails.components.cta-button', [
        'url'   => $url,
        'label' => 'Reset Password →',
        'color' => '#ff6100',
    ])

    @include('emails.components.url-fallback', [
        'url' => $url,
    ])

    @include('emails.components.disclaimer', [
        'severity' => 'normal',
        'content'  => "If you didn't request this, ignore this email — your password won't change. For your security, never share this link with anyone.",
    ])

@endsection
