@props([
    'title' => null,
    'description' => null,
])
@php
    $linkClass = 'transition hover:text-[#087e8b] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087e8b]';
    $home = request()->routeIs('home') ? '' : route('home');
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fb">
    <title>{{ $title ?? $brandName }}</title>
    <meta name="description" content="{{ $description ?? $tagline }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ $brandName }}">
    <meta property="og:title" content="{{ $title ?? $brandName }}">
    <meta property="og:description" content="{{ $description ?? $tagline }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f4f7fb] text-[#14263d] antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:text-[#14263d] focus:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#087e8b]">Skip to content</a>

    <header class="border-b border-[#14263d]/10 bg-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between lg:px-8">
            <a href="{{ $home }}#home" class="w-fit text-base font-bold tracking-tight text-[#14263d] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087e8b]" aria-label="{{ $brandName }} home">
                {{ $brandLead }}@if ($brandRemainder !== '') <span class="text-[#087e8b]">{{ $brandRemainder }}</span>@endif
            </a>
            <nav aria-label="Main navigation" class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm font-medium sm:gap-8">
                <a class="{{ $linkClass }}" href="{{ $home }}#services">Services</a>
                @if ($hasPublishedProjects)
                    <a class="{{ $linkClass }}" href="{{ route('projects.index') }}" @if (request()->routeIs('projects.*')) aria-current="page" @endif>Projects</a>
                @endif
                <a class="{{ $linkClass }}" href="{{ $home }}#approach">Approach</a>
                <a class="font-semibold text-[#087e8b] underline decoration-[#087e8b] decoration-2 underline-offset-4 hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#087e8b]" href="{{ $home }}#contact">Contact</a>
            </nav>
        </div>
    </header>

    <main id="main-content" tabindex="-1">
        {{ $slot }}
    </main>

    <footer class="bg-white px-6 py-6 text-center text-sm text-[#506176]">
        <p>© {{ now()->year }} {{ $brandName }}. Technical consulting and engineering solutions.</p>
    </footer>
</body>
</html>
