@props([
    'title' => null,
    'description' => null,
    'ogType' => 'website',
    // Image for link previews; the logo is used when a page has none of its own.
    'ogImage' => null,
])
@php
    $navItems = array_values(array_filter([
        ['label' => 'Services', 'route' => 'services.index', 'active' => 'services.*'],
        $hasPublishedProjects ? ['label' => 'Projects', 'route' => 'projects.index', 'active' => 'projects.*'] : null,
        $hasPublishedArticles ? ['label' => 'Articles', 'route' => 'articles.index', 'active' => 'articles.*'] : null,
        ['label' => 'Approach', 'route' => 'approach', 'active' => 'approach'],
        ['label' => 'Book', 'route' => 'booking', 'active' => 'booking'],
    ]));
    $navLink = 'relative rounded-full px-3.5 py-2 text-sm font-medium text-ink/80 transition hover:bg-brand-tint hover:text-brand-dark aria-[current=page]:bg-brand-tint aria-[current=page]:text-brand-dark';
    $mobileLink = 'flex min-h-11 items-center justify-between rounded-xl px-4 text-base font-medium text-ink hover:bg-brand-tint aria-[current=page]:bg-brand-tint aria-[current=page]:text-brand-dark';
    $footerLink = 'text-white/75 transition hover:text-white';
@endphp
<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#ffffff">
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
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta name="twitter:card" content="summary_large_image">
    @elseif ($logoUrl)
        <meta property="og:image" content="{{ $logoUrl }}">
    @endif
    {{ $head ?? '' }}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ \App\Support\Theme::headTags() }}
</head>
<body class="flex min-h-screen flex-col bg-canvas font-sans text-ink antialiased">
    <a href="#main-content" data-a11y-exempt class="sr-only focus:not-sr-only focus:fixed focus:top-4 focus:left-4 focus:z-[100] focus:rounded-full focus:bg-ink focus:px-5 focus:py-3 focus:font-semibold focus:text-white focus:shadow-lg">Skip to content</a>

    @if (\App\Http\Middleware\PublicMaintenanceMode::isEnabled())
        <p role="status" class="bg-[#7a4100] px-4 py-2 text-center text-sm font-medium text-white">
            Maintenance mode is on. Visitors see the "back soon" page; you can see the site because you are signed in as an administrator.
        </p>
    @endif

    <header class="group/header sticky top-0 z-40 border-b border-transparent bg-white/85 backdrop-blur-lg transition-[box-shadow,border-color] supports-[backdrop-filter]:bg-white/75 data-[scrolled=true]:border-line data-[scrolled=true]:shadow-[0_6px_24px_-12px_rgb(20_38_61/0.25)]" data-site-header>
        <div class="container-site flex h-16 items-center justify-between gap-3 sm:gap-4 2xl:h-20">
            <a href="{{ route('home') }}" class="flex min-w-0 shrink items-center gap-2.5 text-lg font-bold tracking-tight text-ink" aria-label="{{ $brandName }} home">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $brandName }}" @class(['h-11 w-auto max-w-[min(56vw,15rem)] rounded-lg bg-white object-contain p-1 sm:h-12 sm:max-w-[18rem] 2xl:h-14 2xl:max-w-[22rem]', 'group-data-[scrolled=true]/header:hidden' => $stickyLogoUrl])>
                    @if ($stickyLogoUrl)
                        {{-- Shown in place of the main logo once the header sticks to the top while scrolling. --}}
                        <img src="{{ $stickyLogoUrl }}" alt="{{ $brandName }}" class="hidden h-11 w-auto max-w-[min(56vw,15rem)] rounded-lg bg-white object-contain p-1 group-data-[scrolled=true]/header:block sm:h-12 sm:max-w-[18rem] 2xl:h-14 2xl:max-w-[22rem]">
                    @endif
                @else
                    <span aria-hidden="true" class="grid size-9 place-items-center rounded-xl bg-ink text-sm font-bold text-white">{{ mb_strtoupper(mb_substr($brandName, 0, 1)) }}</span>
                    <span>{{ $brandLead }}@if ($brandRemainder !== '') <span class="text-brand">{{ $brandRemainder }}</span>@endif</span>
                @endif
            </a>

            <nav aria-label="Main navigation" class="hidden items-center gap-1 lg:flex">
                @foreach ($navItems as $item)
                    <a class="{{ $navLink }}" href="{{ route($item['route']) }}" @if (request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="hidden items-center gap-2 lg:flex">
                @auth
                    <a class="{{ $navLink }}" href="{{ route('account.dashboard') }}" @if (request()->routeIs('account.*')) aria-current="page" @endif>Your account</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="{{ $navLink }}">Sign out</button>
                    </form>
                @else
                    <a class="{{ $navLink }}" href="{{ route('login') }}">Sign in</a>
                @endauth
                <a class="btn-primary btn-sm" href="{{ route('contact') }}" @if (request()->routeIs('contact')) aria-current="page" @endif>Contact</a>
            </div>

            <button type="button" class="grid size-10 place-items-center rounded-full border border-line bg-white text-ink lg:hidden" aria-expanded="false" aria-controls="mobile-menu" aria-label="Open menu" data-menu-toggle>
                <i class="fa-solid fa-bars text-lg" aria-hidden="true" data-menu-icon-open></i>
                <i class="fa-solid fa-xmark hidden text-lg" aria-hidden="true" data-menu-icon-close></i>
            </button>
        </div>

        <div id="mobile-menu" hidden class="max-h-[calc(100dvh-4rem)] overflow-y-auto border-t border-line bg-white lg:hidden">
            <nav aria-label="Mobile navigation" class="container-site grid gap-1 py-3">
                @foreach ($navItems as $item)
                    <a class="{{ $mobileLink }}" href="{{ route($item['route']) }}" @if (request()->routeIs($item['active'])) aria-current="page" @endif>{{ $item['label'] }}<i class="fa-solid fa-chevron-right text-xs text-muted" aria-hidden="true"></i></a>
                @endforeach
                @auth
                    <a class="{{ $mobileLink }}" href="{{ route('account.dashboard') }}" @if (request()->routeIs('account.*')) aria-current="page" @endif>Your account<i class="fa-solid fa-chevron-right text-xs text-muted" aria-hidden="true"></i></a>
                @endauth
                <div class="mt-2 grid gap-2 border-t border-line pt-3 sm:grid-cols-2">
                    <a class="btn-primary w-full" href="{{ route('contact') }}">Contact me</a>
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn-ghost w-full">Sign out</button>
                        </form>
                    @else
                        <a class="btn-ghost w-full" href="{{ route('login') }}">Sign in</a>
                    @endauth
                </div>
            </nav>
        </div>
    </header>

    <main id="main-content" tabindex="-1" class="flex-1 focus:outline-none">
        @if (session('account_deleted'))
            <div class="container-site pt-6">
                <p role="status" class="card px-5 py-4 font-semibold">Your account and its data have been permanently deleted.</p>
            </div>
        @endif
        {{ $slot }}
    </main>

    <footer class="bg-secondary text-sm text-white/75" aria-label="Site footer">
        <div class="container-site grid grid-cols-2 gap-x-6 gap-y-8 py-10 sm:py-12 lg:grid-cols-12 lg:gap-10">
            <div class="col-span-2 lg:col-span-5">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 text-lg font-bold text-white" aria-label="{{ $brandName }} home">
                    <span aria-hidden="true" class="grid size-9 place-items-center rounded-xl bg-white text-sm font-bold text-secondary">{{ mb_strtoupper(mb_substr($brandName, 0, 1)) }}</span>
                    {{ $brandName }}
                </a>
                <p class="mt-3 max-w-md leading-6 sm:leading-7">{{ $tagline }}. Practical technical consulting and engineering solutions to help your business move forward.</p>
                <a href="{{ route('booking') }}" class="btn-light mt-5">Book a consultation <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i></a>
            </div>
            <div class="lg:col-span-3 lg:col-start-7">
                <h2 class="text-xs font-bold tracking-[.18em] text-white uppercase">Explore</h2>
                <ul class="mt-3 space-y-2.5">
                    <li><a class="{{ $footerLink }}" href="{{ route('services.index') }}">Services</a></li>
                    @if ($hasPublishedProjects)<li><a class="{{ $footerLink }}" href="{{ route('projects.index') }}">Projects</a></li>@endif
                    @if ($hasPublishedArticles)<li><a class="{{ $footerLink }}" href="{{ route('articles.index') }}">Articles</a></li>@endif
                    <li><a class="{{ $footerLink }}" href="{{ route('approach') }}">My approach</a></li>
                </ul>
            </div>
            <div class="lg:col-span-3">
                <h2 class="text-xs font-bold tracking-[.18em] text-white uppercase">Work with me</h2>
                <ul class="mt-3 space-y-2.5">
                    <li><a class="{{ $footerLink }}" href="{{ route('booking') }}">Book a consultation</a></li>
                    <li><a class="{{ $footerLink }}" href="{{ route('contact') }}">Contact</a></li>
                    @auth
                        <li><a class="{{ $footerLink }}" href="{{ route('account.dashboard') }}">Account dashboard</a></li>
                    @else
                        <li><a class="{{ $footerLink }}" href="{{ route('login') }}">Sign in</a></li>
                    @endauth
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="container-site flex flex-col gap-3 py-5 pb-20 sm:flex-row sm:items-center sm:justify-between sm:pb-5">
                <p>© {{ now()->year }} {{ $brandName }}. All rights reserved. <a class="{{ $footerLink }} ml-2 underline-offset-4 hover:underline" href="{{ route('privacy') }}">Privacy policy</a></p>
                <a class="{{ $footerLink }} inline-flex w-fit items-center gap-2" href="{{ route('contact') }}"><i class="fa-solid fa-envelope" aria-hidden="true"></i>Get in touch</a>
            </div>
        </div>
    </footer>

    <x-accessibility-tools />
    {{-- The assistant uses the site's favicon (square) or, failing that, the logo as its avatar. --}}
    <x-chatbot-widget :avatar="$faviconUrl ?: $logoUrl" />
</body>
</html>
