@php
    $linkClass = 'font-semibold text-brand hover:text-brand-dark focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand';
@endphp
<x-layouts.site :title="'Your account | '.$brandName">
    <div class="mx-auto w-full max-w-4xl section px-4 sm:px-6">
        <h1 class="text-3xl font-semibold tracking-tight">Hello, {{ auth()->user()->name }}</h1>

        @if (auth()->user()->is_admin)
            <p class="mt-4 border-l-2 border-brand bg-white px-4 py-3 text-sm">
                You are an administrator. <a href="{{ filament()->getPanel('admin')->getUrl() }}" class="{{ $linkClass }} font-semibold">Go to the admin dashboard</a>
            </p>
        @endif

        @if (session('status') === 'password-updated')
            <p role="status" class="mt-6 border-l-2 border-brand bg-white px-4 py-3 text-sm font-medium">Your password has been changed.</p>
        @endif

        <section aria-labelledby="tickets-title" class="mt-8 sm:mt-10">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 id="tickets-title" class="text-2xl font-semibold">Support tickets</h2>
                    @if ($awaitingYou > 0)
                        <p class="mt-1 text-sm text-muted">{{ trans_choice('{1} 1 ticket is|[2,*] :count tickets are', $awaitingYou) }} waiting for your reply.</p>
                    @endif
                </div>
                <a href="{{ route('account.tickets.create') }}" class="btn-primary w-full sm:w-fit">Open a ticket</a>
            </div>

            <div class="mt-6">
                @if ($recentTickets->isEmpty())
                    <p class="border-l-2 border-brand py-2 pl-4 text-muted">You have no support tickets yet.</p>
                @else
                    @include('account.partials.ticket-list', ['tickets' => $recentTickets])
                    <p class="mt-4 text-sm"><a href="{{ route('account.tickets.index') }}" class="{{ $linkClass }}">All tickets</a></p>
                @endif
            </div>
        </section>

        <section aria-labelledby="bookings-title" class="mt-10 sm:mt-12">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <h2 id="bookings-title" class="text-2xl font-semibold">Bookings and services</h2>
                <a href="{{ route('booking') }}" class="btn-ghost w-full sm:w-fit">Request an appointment</a>
            </div>

            <div class="mt-6">
                @if ($recentBookings->isEmpty())
                    <p class="border-l-2 border-brand py-2 pl-4 text-muted">You have no appointment requests yet.</p>
                @else
                    @include('account.partials.booking-list', ['bookings' => $recentBookings])
                    <p class="mt-4 text-sm"><a href="{{ route('account.bookings.index') }}" class="{{ $linkClass }}">All bookings</a></p>
                @endif
            </div>
        </section>

        <section aria-labelledby="notifications-title" class="mt-10 max-w-md sm:mt-12">
            <h2 id="notifications-title" class="text-2xl font-semibold">Email notifications</h2>
            <form method="POST" action="{{ route('account.notifications.update') }}" class="mt-6 grid gap-4 card p-5 sm:p-6 lg:p-8">
                @csrf
                @method('PUT')
                @if (session('notifications_saved'))
                    <p class="text-sm font-semibold text-brand-dark" role="status">Your notification settings were saved.</p>
                @endif
                @foreach (\App\Support\NotificationPreferences::CUSTOMER_EVENTS as $event => $label)
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="preferences[{{ $event }}][mail]" value="1" class="mt-1 size-4 accent-brand"
                            @checked(\App\Support\NotificationPreferences::wants(auth()->user(), $event, 'mail'))>
                        <span>Email me about {{ lcfirst($label) }}</span>
                    </label>
                @endforeach
                <p class="text-sm text-muted">Important account emails, such as password resets, are always sent.</p>
                <x-button>Save notification settings</x-button>
            </form>
        </section>

        <section aria-labelledby="password-title" class="mt-10 max-w-md sm:mt-12">
            <h2 id="password-title" class="text-2xl font-semibold">Change password</h2>
            <form method="POST" action="{{ route('user-password.update') }}" class="mt-6 grid gap-4 card p-5 sm:p-6 lg:p-8" novalidate>
                @csrf
                @method('PUT')
                <x-form.errors bag="updatePassword" />
                <x-form.field name="current_password" label="Current password" type="password" autocomplete="current-password" required bag="updatePassword" />
                <x-form.field name="password" label="New password" type="password" autocomplete="new-password" required bag="updatePassword" hint="At least 8 characters." />
                <x-form.field name="password_confirmation" label="Confirm new password" type="password" autocomplete="new-password" required bag="updatePassword" />
                <x-button>Change password</x-button>
            </form>
        </section>

        <section aria-labelledby="delete-account-title" class="mt-10 max-w-md sm:mt-12">
            <h2 id="delete-account-title" class="text-2xl font-semibold">Delete account</h2>
            <form method="POST" action="{{ route('account.destroy') }}" class="mt-6 grid gap-4 card border-[#b42318]/30 p-5 sm:p-6 lg:p-8" novalidate>
                @csrf
                @method('DELETE')
                <p class="text-sm text-muted">This permanently deletes your account, your support tickets and attachments, and the bookings and enquiries linked to your account. It cannot be undone.</p>
                <x-form.errors bag="deleteAccount" />
                <x-form.field name="password" id="field-delete-password" label="Your password" type="password" autocomplete="current-password" required bag="deleteAccount" />
                <label class="flex items-start gap-3 text-sm">
                    <input type="checkbox" name="confirm" value="1" class="mt-0.5 size-4 accent-[#b42318]" required>
                    <span>I understand my account and its data will be permanently deleted.</span>
                </label>
                @error('confirm', 'deleteAccount')<p class="text-sm text-[#b42318]">{{ $message }}</p>@enderror
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#b42318] px-5 py-3 font-semibold text-white hover:bg-[#912018] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#b42318]">Delete my account</button>
            </form>
        </section>
    </div>
</x-layouts.site>
