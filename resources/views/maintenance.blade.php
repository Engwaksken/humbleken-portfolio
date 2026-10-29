<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Back soon — {{ $brandName }}</title>
    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    @vite(['resources/css/app.css'])
    {{ $themeTags }}
</head>
<body class="grid min-h-dvh place-items-center bg-[#f4f7fb] px-6 text-[#14263d] antialiased">
    <main class="max-w-lg border-l-4 border-brand bg-white p-8">
        <p class="text-sm font-semibold uppercase tracking-[.16em] text-brand">{{ $brandName }}</p>
        <h1 class="mt-4 text-3xl font-semibold tracking-tight">We'll be back soon</h1>
        <p class="mt-4 leading-7 whitespace-pre-line text-[#506176]">{{ $message }}</p>
    </main>
</body>
</html>
