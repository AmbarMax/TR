{{-- CTA button — Share Tech Mono uppercase, white text on color bg
     Variables: $url, $label, $color (default #ff6100) --}}
<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:32px 0 24px 0;">
    <tr>
        <td align="center" bgcolor="{{ $color ?? '#ff6100' }}" style="border-radius:4px; background-color:{{ $color ?? '#ff6100' }};">
            <a href="{{ $url }}" target="_blank" style="display:inline-block; padding:14px 32px; font-family:'Share Tech Mono','JetBrains Mono','Fira Code','Cascadia Code','SF Mono',Menlo,Consolas,'Courier New',monospace; font-size:13px; letter-spacing:0.12em; font-weight:bold; color:#ffffff; text-decoration:none; text-transform:uppercase; line-height:1; border-radius:4px;">{{ $label }}</a>
        </td>
    </tr>
</table>
