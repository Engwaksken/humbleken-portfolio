<x-layouts.site :title="'Reset your password — '.$brandName">
    <x-auth-card heading="Reset your password" intro="Enter your account email and we will send you a link to choose a new password.">
        <form method="POST" action="{{ route('password.email') }}" class="grid gap-5" novalidate>
            @csrf
            <x-form.errors />
            <x-form.field name="email" label="Email" type="email" autocomplete="email" required />
            <x-button>Send reset link</x-button>
        </form>
        <x-slot:footer>
            <p><a href="{{ route('login') }}" class="text-brand underline underline-offset-2 hover:text-brand-dark focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Back to sign in</a></p>
        </x-slot:footer>
    </x-auth-card>
</x-layouts.site>
