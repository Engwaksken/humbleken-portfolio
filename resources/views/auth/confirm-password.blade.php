<x-layouts.site :title="'Confirm your password — '.$brandName">
    <x-auth-card heading="Confirm your password" intro="Please confirm your password to continue.">
        <form method="POST" action="{{ route('password.confirm.store') }}" class="grid gap-5" novalidate>
            @csrf
            <x-form.errors />
            <x-form.field name="password" label="Password" type="password" autocomplete="current-password" required />
            <x-button>Confirm</x-button>
        </form>
    </x-auth-card>
</x-layouts.site>
