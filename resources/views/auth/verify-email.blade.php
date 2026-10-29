<x-layouts.site :title="'Confirm your email — '.$brandName">
    <x-auth-card heading="Confirm your email" intro="We sent a confirmation link to {{ auth()->user()->email }}. Follow it to finish setting up your account.">
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
                <button type="submit" class="text-sm text-[#077784] underline underline-offset-2 hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]">Sign out</button>
            </form>
        </div>
    </x-auth-card>
</x-layouts.site>
