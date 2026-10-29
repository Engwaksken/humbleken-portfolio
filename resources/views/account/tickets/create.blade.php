<x-layouts.site :title="'Open a support ticket — '.$brandName">
    <div class="mx-auto max-w-2xl px-6 py-16 lg:py-20">
        <a href="{{ route('account.tickets.index') }}" class="text-sm font-semibold text-[#077784] hover:text-[#075d68] focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-[#077784]">
            <span aria-hidden="true">←</span> Support tickets
        </a>
        <h1 class="mt-6 text-3xl font-semibold tracking-tight">Open a support ticket</h1>
        <p class="mt-3 leading-7 text-[#506176]">Describe the problem, what you have already tried, and how it affects your work. Please do not include passwords.</p>

        <form method="POST" action="{{ route('account.tickets.store') }}" class="mt-8 grid gap-5 bg-white p-6 sm:p-8" novalidate>
            @csrf
            <x-form.errors />
            <x-form.field name="subject" label="Subject" required />
            <x-form.field name="body" label="Details" :rows="8" required />
            <x-button>Open ticket</x-button>
        </form>
    </div>
</x-layouts.site>
