@props(['heading', 'intro' => null])
<section aria-labelledby="auth-title" class="mx-auto max-w-md px-6 py-16 lg:py-20">
    <h1 id="auth-title" class="text-3xl font-semibold tracking-tight">{{ $heading }}</h1>
    @if ($intro)
        <p class="mt-3 leading-7 text-[#506176]">{{ $intro }}</p>
    @endif
    @if (session('status') && session('status') !== 'verification-link-sent')
        <p role="status" class="mt-6 border-l-2 border-[#077784] bg-white px-4 py-3 text-sm font-medium">{{ session('status') }}</p>
    @endif
    <div class="mt-8 bg-white p-6 sm:p-8">
        {{ $slot }}
    </div>
    @isset($footer)
        <div class="mt-6 text-sm text-[#506176]">{{ $footer }}</div>
    @endisset
</section>
