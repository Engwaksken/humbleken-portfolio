<x-layouts.site :title="'Choose a new password — '.$brandName">
    <x-auth-card heading="Choose a new password">
        <form method="POST" action="{{ route('password.update') }}" class="grid gap-5" novalidate>
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <x-form.errors />
            <x-form.field name="email" label="Email" type="email" autocomplete="email" :value="$request->email" required />
            <x-form.field name="password" label="New password" type="password" autocomplete="new-password" required hint="At least 8 characters." />
            <x-form.field name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" required />
            <x-button>Save new password</x-button>
        </form>
    </x-auth-card>
</x-layouts.site>
