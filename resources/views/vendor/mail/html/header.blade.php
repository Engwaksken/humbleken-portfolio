@props(['url'])
{{-- Shows the site logo when one is uploaded, otherwise the brand name in the brand colour. --}}
@php($logoUrl = \App\Support\EmailBranding::logoUrl())
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block; color: {{ \App\Support\EmailBranding::color() }};">
@if ($logoUrl)
<img src="{{ $logoUrl }}" alt="{{ \App\Support\EmailBranding::name() }}" style="max-height: 56px; max-width: 240px; height: auto; border: 0;">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
