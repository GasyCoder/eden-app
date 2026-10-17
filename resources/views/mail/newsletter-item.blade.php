@php($en = $locale === 'en')
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $item['title'] }}</title>
</head>
<body style="margin:0;padding:0;background:#F6F8F6;font-family:Inter,'Segoe UI',Helvetica,Arial,sans-serif;color:#101412;">
    <div style="display:none;max-height:0;overflow:hidden;">{{ \Illuminate\Support\Str::limit($item['excerpt'] ?? $item['title'], 140) }}</div>
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#F6F8F6;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#FFFFFF;border-radius:12px;overflow:hidden;">
                    <tr>
                        <td style="padding:0;font-size:0;line-height:0;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
                                <td style="height:4px;width:60%;background:#13692C;"></td>
                                <td style="height:4px;width:20%;background:#D3A20A;"></td>
                                <td style="height:4px;width:20%;background:#AA0504;"></td>
                            </tr></table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px 8px;">
                            <a href="{{ $siteUrl }}" style="text-decoration:none;color:#101412;">
                                <img src="{{ $logo }}" width="36" height="36" alt="" style="vertical-align:middle;border-radius:8px;border:0;">
                                <span style="font-weight:700;font-size:15px;vertical-align:middle;margin-left:8px;">{{ $siteName }}</span>
                            </a>
                        </td>
                    </tr>
                    @if ($image)
                        <tr>
                            <td style="padding:16px 32px 0;">
                                <img src="{{ $image }}" width="536" alt="" style="display:block;width:100%;max-width:536px;height:auto;border-radius:10px;border:0;">
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td style="padding:24px 32px 8px;">
                            <p style="margin:0 0 8px;font-size:13px;font-weight:600;color:#105526;">{{ $item['kind'] }}</p>
                            <h1 style="margin:0;font-size:22px;line-height:1.3;font-weight:700;color:#101412;">{{ $item['title'] }}</h1>
                            @if (! empty($item['details']))
                                <p style="margin:12px 0 0;font-size:14px;line-height:1.6;color:#5F6A63;">{{ implode(' · ', $item['details']) }}</p>
                            @endif
                            @if (! empty($item['excerpt']))
                                <p style="margin:16px 0 0;font-size:15px;line-height:1.65;color:#2B332E;">{{ $item['excerpt'] }}</p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px 32px;">
                            <a href="{{ $item['url'] }}" style="display:inline-block;background:#13692C;color:#FFFFFF;font-size:15px;font-weight:600;text-decoration:none;padding:12px 22px;border-radius:8px;">{{ $item['cta'] }}</a>
                        </td>
                    </tr>
                </table>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">
                    <tr>
                        <td style="padding:20px 32px;font-size:12px;line-height:1.6;color:#5F6A63;text-align:center;">
                            {{ $en ? 'You receive this email because you subscribed to the EDEN newsletter.' : 'Vous recevez cet email car vous êtes abonné·e à la newsletter de l’EDEN.' }}<br>
                            <a href="{{ $unsubscribeUrl }}" style="color:#105526;">{{ $en ? 'Unsubscribe' : 'Se désabonner' }}</a>
                            &nbsp;·&nbsp;
                            <a href="{{ $siteUrl }}" style="color:#105526;">{{ parse_url($siteUrl, PHP_URL_HOST) }}</a>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
