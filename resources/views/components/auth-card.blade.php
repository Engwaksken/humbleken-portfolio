@props(['heading', 'intro' => null, 'brandName', 'logoUrl' => null])
<section aria-labelledby="auth-title" class="section flex flex-1 items-center px-4 py-8 sm:py-12">
    <div class="mx-auto w-full max-w-md">
        <div class="card p-5 shadow-[0_24px_60px_-24px_rgb(20_38_61/0.3)] sm:p-8">
            <a href="{{ route('home') }}" class="mx-auto mb-6 flex min-h-12 max-w-full items-center justify-center" aria-label="{{ $brandName }} home">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $brandName }}" class="max-h-14 w-auto max-w-full object-contain sm:max-h-16">
                @else
                    <span class="text-xl font-bold tracking-tight text-ink">{{ $brandName }}</span>
                @endif
            </a>
            <h1 id="auth-title" class="text-center text-2xl font-semibold tracking-tight sm:text-3xl">{{ $heading }}</h1>
            @if ($intro)
                <p class="mt-2 text-center leading-7 text-muted">{{ $intro }}</p>
            @endif
            @if (session('status') && session('status') !== 'verification-link-sent')
                <p role="status" class="mt-6 rounded-xl bg-brand-tint px-4 py-3 text-sm font-medium text-brand-dark">{{ session('status') }}</p>
            @endif
            <div class="mt-6">
                {{ $slot }}
            </div>
        </div>
        @isset($footer)
            <div class="mt-6 text-center text-sm text-muted">{{ $footer }}</div>
        @endisset
    </div>
</section>
