{{-- Automatic reply to a client. The text is written by the site owner in Settings, Auto replies. --}}
@php($brand = \App\Support\EmailBranding::name())
@php($color = \App\Support\EmailBranding::color())
@php($logoUrl = \App\Support\EmailBranding::logoUrl())
@php($footerText = \App\Support\EmailBranding::footerText())
@php($contactLines = \App\Support\EmailBranding::contactLines())
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $brand }}</title>
</head>
<body style="margin:0;padding:24px 12px;background:#f4f5f7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:8px;border-top:4px solid {{ $color }};">
                    <tr>
                        <td style="padding:28px 32px 8px;font-size:18px;font-weight:700;color:{{ $color }};">
                            <a href="{{ config('app.url') }}" style="color:{{ $color }};text-decoration:none;">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $brand }}" style="max-height:56px;max-width:240px;height:auto;border:0;">
                                @else
                                    {{ $brand }}
                                @endif
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 32px 32px;font-size:15px;line-height:1.6;">
                            @foreach (preg_split("/\n{2,}/", $body) as $paragraph)
                                <p style="margin:0 0 16px;">{!! nl2br(e($paragraph)) !!}</p>
                            @endforeach
                        </td>
                    </tr>
                </table>
                <div style="max-width:560px;margin:16px auto 0;font-size:12px;line-height:1.6;color:#6b7280;">
                    @if ($footerText)
                        <p style="margin:0 0 8px;">{!! nl2br(e($footerText)) !!}</p>
                    @endif
                    @if ($contactLines)
                        <p style="margin:0 0 8px;">{{ implode(' | ', $contactLines) }}</p>
                    @endif
                    <p style="margin:0;">This is an automatic reply to confirm I received your message.</p>
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
