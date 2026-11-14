{{-- Footer under every branded email (Markdown; HTML in it is escaped by the parser). --}}
@if ($footerText = \App\Support\EmailBranding::footerText())
{{ $footerText }}

@endif
@if ($contactLines = \App\Support\EmailBranding::contactLines())
{{ implode(' | ', $contactLines) }}

@endif
© {{ date('Y') }} {{ \App\Support\EmailBranding::name() }}
