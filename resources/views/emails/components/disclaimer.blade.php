{{-- Disclaimer — bordered left strip with severity-driven color
     Variables: $severity ('normal'|'notice'|'critical'), $content (raw HTML allowed) --}}
@php
    $sev = $severity ?? 'normal';
    $borderColor = match ($sev) {
        'critical' => '#ff6100',
        'notice'   => '#7da91a',
        default    => '#d4d4d8',
    };
@endphp
<div style="border-left:2px solid {{ $borderColor }}; padding-left:14px; margin-top:24px;">
    <p style="font-family:'Share Tech Mono','JetBrains Mono','Fira Code','Cascadia Code','SF Mono',Menlo,Consolas,'Courier New',monospace; font-size:12px; color:#6e6259; line-height:1.6; margin:0;">{!! $content !!}</p>
</div>
