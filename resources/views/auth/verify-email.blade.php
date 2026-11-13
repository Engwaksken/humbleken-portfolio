<x-layouts.site :title="'Confirm your email — '.$brandName">
    <x-auth-card heading="Confirm your email" intro="We sent a confirmation link to {{ auth()->user()->email }}. Follow it to finish setting up your account." :brand-name="$brandName" :logo-url="$logoUrl">
        @if (session('status') === 'verification-link-sent')
            <p role="status" class="mb-5 text-sm font-medium">A new confirmation link has been sent.</p>
        @endif
        <div class="flex flex-wrap items-center gap-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-button>Send the link again</x-button>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-semibold text-brand hover:text-brand-dark focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Sign out</button>
            </form>
        </div>
    </x-auth-card>
</x-layouts.site>
