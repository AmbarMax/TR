<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light only">
    <meta name="supported-color-schemes" content="light only">
    <title>TrophyRoom</title>
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=VT323&display=swap" rel="stylesheet">
    <style type="text/css">
      @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=VT323&display=swap');
      /* Block Gmail auto-link styling on email addresses */
      a[x-apple-data-detectors],
      a[href^="mailto:"],
      .footer-email {
        color: inherit !important;
        text-decoration: none !important;
        font-weight: inherit !important;
        pointer-events: none !important;
      }
    </style>
</head>
<body bgcolor="#ffffff" style="margin:0; padding:0; background-color:#ffffff; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;">

@php
    $td = $stripDirection ?? 'orange-first';
    $topLeftColor     = $td === 'green-first' ? '#7da91a' : '#ff6100';
    $topRightColor    = $td === 'green-first' ? '#ff6100' : '#7da91a';
    $bottomLeftColor  = $td === 'green-first' ? '#ff6100' : '#7da91a';
    $bottomRightColor = $td === 'green-first' ? '#7da91a' : '#ff6100';
    $taglineText      = $tagline ?? 'Keep your proof';
    $taglineHex       = $taglineColor ?? '#8a8e96';
@endphp

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#ffffff" style="background-color:#ffffff; width:100%;">
    <tr>
        <td align="center" style="padding:0;">

            {{-- Top strip --}}
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%;">
                <tr>
                    <td width="50%" height="4" bgcolor="{{ $topLeftColor }}" style="height:4px; line-height:4px; font-size:4px; background-color:{{ $topLeftColor }}; mso-line-height-rule:exactly;">&nbsp;</td>
                    <td width="50%" height="4" bgcolor="{{ $topRightColor }}" style="height:4px; line-height:4px; font-size:4px; background-color:{{ $topRightColor }}; mso-line-height-rule:exactly;">&nbsp;</td>
                </tr>
            </table>

            {{-- Main container --}}
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" bgcolor="#1f2024" style="max-width:600px; width:100%; background-color:#1f2024;">

                {{-- Header (frame oscuro) --}}
                <tr>
                    <td align="center" bgcolor="#1f2024" style="padding:40px 32px 28px 32px; background-color:#1f2024;">
                        <img src="https://app.trophyroom.gg/images/mail/isologo.png" alt="TrophyRoom" width="80" height="80" style="display:block; width:80px; height:80px; border:0; outline:none; text-decoration:none;">
                        <div style="margin-top:18px; font-family:'VT323','JetBrains Mono','Fira Code','Cascadia Code','SF Mono',Menlo,Consolas,'Courier New',monospace; font-size:36px; letter-spacing:0.12em; color:#feeddf; line-height:1;">TROPHYROOM</div>
                        @if(! empty($taglineText))
                            <div style="margin-top:12px; font-family:'Share Tech Mono','JetBrains Mono','Fira Code','Cascadia Code','SF Mono',Menlo,Consolas,'Courier New',monospace; font-size:11px; letter-spacing:0.24em; color:{{ $taglineHex }}; text-transform:uppercase; line-height:1;">{{ $taglineText }}</div>
                        @endif
                    </td>
                </tr>

                {{-- Content slot (fondo blanco) --}}
                <tr>
                    <td bgcolor="#ffffff" style="padding:36px 32px 36px 32px; background-color:#ffffff; border-top:1px solid #3a3c40; border-bottom:1px solid #3a3c40;">
                        @yield('content')
                    </td>
                </tr>

                {{-- Footer (frame oscuro) --}}
                <tr>
                    <td align="center" bgcolor="#1f2024" style="padding:28px 32px; background-color:#1f2024;">
                        <div style="font-family:'VT323','JetBrains Mono','Fira Code','Cascadia Code','SF Mono',Menlo,Consolas,'Courier New',monospace; font-size:20px; letter-spacing:0.14em; color:#5a5e66; line-height:1;">TROPHYROOM</div>
                        <div class="footer-email" style="margin-top:14px; font-family:'Share Tech Mono','JetBrains Mono','Fira Code','Cascadia Code','SF Mono',Menlo,Consolas,'Courier New',monospace; font-size:10px; color:#5a5e66; line-height:1.4;">Sent by <span style="color:#5a5e66; text-decoration:none;">noreply&#64;trophyroom.gg</span></div>
                        <div style="margin-top:2px; font-family:'Share Tech Mono','JetBrains Mono','Fira Code','Cascadia Code','SF Mono',Menlo,Consolas,'Courier New',monospace; font-size:10px; color:#5a5e66; line-height:1.4;">&copy; {{ date('Y') }} Ambar Labs Inc.</div>
                    </td>
                </tr>

            </table>

            {{-- Bottom strip (inverso del top) --}}
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" border="0" style="max-width:600px; width:100%;">
                <tr>
                    <td width="50%" height="4" bgcolor="{{ $bottomLeftColor }}" style="height:4px; line-height:4px; font-size:4px; background-color:{{ $bottomLeftColor }}; mso-line-height-rule:exactly;">&nbsp;</td>
                    <td width="50%" height="4" bgcolor="{{ $bottomRightColor }}" style="height:4px; line-height:4px; font-size:4px; background-color:{{ $bottomRightColor }}; mso-line-height-rule:exactly;">&nbsp;</td>
                </tr>
            </table>

        </td>
    </tr>
</table>

</body>
</html>
