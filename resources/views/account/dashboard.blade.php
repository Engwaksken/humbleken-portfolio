@php
    $linkClass = 'text-[#077784] underline underline-offset-2 hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#077784]';
@endphp
<x-layouts.site :title="'Your account — '.$brandName">
    <div class="mx-auto max-w-4xl px-6 py-16 lg:py-20">
        <h1 class="text-3xl font-semibold tracking-tight">Hello, {{ auth()->user()->name }}</h1>

        @if (auth()->user()->is_admin)
            <p class="mt-4 border-l-2 border-[#077784] bg-white px-4 py-3 text-sm">
                You are an administrator. <a href="{{ filament()->getPanel('admin')->getUrl() }}" class="{{ $linkClass }} font-semibold">Go to the admin dashboard</a>
            </p>
        @endif

        @if (session('status') === 'password-updated')
            <p role="status" class="mt-6 border-l-2 border-[#077784] bg-white px-4 py-3 text-sm font-medium">Your password has been changed.</p>
        @endif

        <section aria-labelledby="tickets-title" class="mt-12">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="tickets-title" class="text-2xl font-semibold">Support tickets</h2>
                    @if ($awaitingYou > 0)
                        <p class="mt-1 text-sm text-[#506176]">{{ trans_choice('{1} 1 ticket is|[2,*] :count tickets are', $awaitingYou) }} waiting for your reply.</p>
                    @endif
                </div>
                <a href="{{ route('account.tickets.create') }}" class="w-fit rounded bg-[#077784] px-4 py-2 text-sm font-semibold text-white hover:bg-[#075d68] focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#077784]">Open a ticket</a>
            </div>

            <div class="mt-6">
                @if ($recentTickets->isEmpty())
                    <p class="border-l-2 border-[#077784] py-2 pl-4 text-[#506176]">You have no support tickets yet.</p>
                @else
                    @include('account.partials.ticket-list', ['tickets' => $recentTickets])
                    <p class="mt-4 text-sm"><a href="{{ route('account.tickets.index') }}" class="{{ $linkClass }}">All tickets</a></p>
                @endif
            </div>
        </section>

        <section aria-labelledby="password-title" class="mt-16 max-w-md">
            <h2 id="password-title" class="text-2xl font-semibold">Change password</h2>
            <form method="POST" action="{{ route('user-password.update') }}" class="mt-6 grid gap-5 bg-white p-6" novalidate>
                @csrf
                @method('PUT')
                <x-form.errors bag="updatePassword" />
                <x-form.field name="current_password" label="Current password" type="password" autocomplete="current-password" required bag="updatePassword" />
                <x-form.field name="password" label="New password" type="password" autocomplete="new-password" required bag="updatePassword" hint="At least 8 characters." />
                <x-form.field name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" required bag="updatePassword" />
                <x-button>Change password</x-button>
            </form>
        </section>
    </div>
</x-layouts.site>
