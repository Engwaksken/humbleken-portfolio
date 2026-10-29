<x-layouts.site :title="'Create an account — '.$brandName">
    <x-auth-card heading="Create an account" intro="An account lets you open support tickets and follow their progress.">
        <form method="POST" action="{{ route('register.store') }}" class="grid gap-5" novalidate>
            @csrf
            <x-form.errors />
            <x-form.field name="name" label="Name" autocomplete="name" required />
            <x-form.field name="email" label="Email" type="email" autocomplete="email" required hint="We will send a link to confirm this address." />
            <x-form.field name="password" label="Password" type="password" autocomplete="new-password" required hint="At least 8 characters." />
            <x-form.field name="password_confirmation" label="Confirm password" type="password" autocomplete="new-password" required />
            <x-button>Create account</x-button>
        </form>
        <x-slot:footer>
            <p>Already have an account? <a href="{{ route('login') }}" class="text-[#077784] underline underline-offset-2 hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]">Sign in</a></p>
        </x-slot:footer>
    </x-auth-card>
</x-layouts.site>
