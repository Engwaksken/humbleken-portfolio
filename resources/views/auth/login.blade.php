<x-layouts.site :title="'Sign in — '.$brandName">
    <x-auth-card heading="Sign in" intro="Sign in to view and reply to your support tickets.">
        <form method="POST" action="{{ route('login.store') }}" class="grid gap-5" novalidate>
            @csrf
            <x-form.errors />
            <x-form.field name="email" label="Email" type="email" autocomplete="email" required />
            <x-form.field name="password" label="Password" type="password" autocomplete="current-password" required />
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="size-4 accent-[#077784]"> Keep me signed in
            </label>
            <x-button>Sign in</x-button>
        </form>
        <x-slot:footer>
            <p><a href="{{ route('password.request') }}" class="text-[#077784] underline underline-offset-2 hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]">Forgot your password?</a></p>
            <p class="mt-2">New here? <a href="{{ route('register') }}" class="text-[#077784] underline underline-offset-2 hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]">Create an account</a></p>
        </x-slot:footer>
    </x-auth-card>
</x-layouts.site>
