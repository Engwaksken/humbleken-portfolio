@props([
    'title' => null,
    'description' => null,
    'ogType' => 'website',
])
@php
    $linkClass = 'transition hover:text-brand focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f7fb">
    <title>{{ $title ?? $brandName }}</title>
    <meta name="description" content="{{ $description ?? $tagline }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:site_name" content="{{ $brandName }}">
    <meta property="og:title" content="{{ $title ?? $brandName }}">
    <meta property="og:description" content="{{ $description ?? $tagline }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($faviconUrl)
        <link rel="icon" href="{{ $faviconUrl }}">
    @endif
    @if ($logoUrl)
        <meta property="og:image" content="{{ $logoUrl }}">
    @endif
    {{ $head ?? '' }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ \App\Support\Theme::headTags() }}
</head>
<body class="bg-[#f4f7fb] text-[#14263d] antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-3 focus:text-[#14263d] focus:shadow-lg focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Skip to content</a>

    @if (\App\Http\Middleware\PublicMaintenanceMode::isEnabled())
        <p role="status" class="bg-[#7a4100] px-6 py-2 text-center text-sm font-medium text-white">
            Maintenance mode is on. Visitors see the "back soon" page; you can see the site because you are signed in as an administrator.
        </p>
    @endif

    <header class="border-b border-[#14263d]/10 bg-white">
        <div class="mx-auto flex w-full max-w-screen-2xl flex-col gap-4 px-4 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-10 2xl:px-14">
            <a href="{{ route('home') }}" class="w-fit text-base font-bold tracking-tight text-[#14263d] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand" aria-label="{{ $brandName }} home">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="h-10 w-auto max-w-[12rem] object-contain">
                @else
                    {{ $brandLead }}@if ($brandRemainder !== '') <span class="text-brand">{{ $brandRemainder }}</span>@endif
                @endif
            </a>
            <nav aria-label="Main navigation" class="flex flex-wrap items-center gap-x-4 gap-y-3 text-sm font-medium sm:gap-x-6 lg:justify-end 2xl:gap-x-8">
                <a class="{{ $linkClass }}" href="{{ route('services.index') }}" @if (request()->routeIs('services.*')) aria-current="page" @endif>Services</a>
                @if ($hasPublishedProjects)
                    <a class="{{ $linkClass }}" href="{{ route('projects.index') }}" @if (request()->routeIs('projects.*')) aria-current="page" @endif>Projects</a>
                @endif
                @if ($hasPublishedArticles)
                    <a class="{{ $linkClass }}" href="{{ route('articles.index') }}" @if (request()->routeIs('articles.*')) aria-current="page" @endif>Articles</a>
                @endif
                <a class="{{ $linkClass }}" href="{{ route('approach') }}" @if (request()->routeIs('approach')) aria-current="page" @endif>Approach</a>
                <a class="{{ $linkClass }}" href="{{ route('booking') }}" @if (request()->routeIs('booking')) aria-current="page" @endif>Book</a>
                <a class="font-semibold text-brand underline decoration-brand decoration-2 underline-offset-4 hover:text-brand-dark focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand" href="{{ route('contact') }}" @if (request()->routeIs('contact')) aria-current="page" @endif>Contact</a>
                @auth
                    <a class="{{ $linkClass }}" href="{{ route('account.dashboard') }}" @if (request()->routeIs('account.*')) aria-current="page" @endif>Your account</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="{{ $linkClass }}">Sign out</button>
                    </form>
                @else
                    <a class="{{ $linkClass }}" href="{{ route('login') }}">Sign in</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="main-content" tabindex="-1">
        {{ $slot }}
    </main>

    <footer class="border-t border-[#14263d]/10 bg-white px-4 py-10 text-sm text-[#506176] sm:px-6 lg:px-10" aria-label="Site footer">
        <div class="mx-auto grid w-full max-w-screen-2xl gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-12">
            <div class="sm:col-span-2 lg:col-span-2">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 font-bold text-[#14263d] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand" aria-label="{{ $brandName }} home">
                    <svg aria-hidden="true" viewBox="0 0 24 24" class="h-6 w-6 fill-none stroke-current" stroke-width="1.8"><path d="M4 12a8 8 0 1 1 2.34 5.66M4 5v5h5M12 8v4l3 2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    {{ $brandName }}
                </a>
                <p class="mt-3 max-w-xl leading-7">Practical technical consulting and engineering solutions to help your business move forward.</p>
            </div>
            <div>
                <h2 class="font-semibold text-[#14263d]">Explore</h2>
                <ul class="mt-3 space-y-2">
                    <li><a class="{{ $linkClass }}" href="{{ route('services.index') }}">Services</a></li>
                    <li><a class="{{ $linkClass }}" href="{{ route('approach') }}">Our approach</a></li>
                    <li><a class="{{ $linkClass }}" href="{{ route('booking') }}">Book a consultation</a></li>
                    <li><a class="{{ $linkClass }}" href="{{ route('contact') }}">Contact</a></li>
                </ul>
            </div>
            <div>
                <h2 class="font-semibold text-[#14263d]">Your account</h2>
                <ul class="mt-3 space-y-2">
                    @auth
                        <li><a class="{{ $linkClass }}" href="{{ route('account.dashboard') }}">Account dashboard</a></li>
                    @else
                        <li><a class="{{ $linkClass }}" href="{{ route('login') }}">Sign in</a></li>
                    @endauth
                </ul>
            </div>
        </div>
        <div class="mx-auto mt-8 flex w-full max-w-screen-2xl flex-col gap-2 border-t border-[#14263d]/10 pt-5 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ now()->year }} {{ $brandName }}. All rights reserved.</p>
            <a class="{{ $linkClass }} inline-flex w-fit items-center gap-2" href="{{ route('contact') }}"><svg aria-hidden="true" viewBox="0 0 24 24" class="h-4 w-4 fill-none stroke-current" stroke-width="2"><path d="M4 6h16v12H4zM4 7l8 6 8-6" stroke-linecap="round" stroke-linejoin="round"/></svg>Get in touch</a>
        </div>
    </footer>

    <x-accessibility-tools />
    <x-chatbot-widget />
</body>
</html>
