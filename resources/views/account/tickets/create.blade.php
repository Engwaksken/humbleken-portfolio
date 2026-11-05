<x-layouts.site :title="'Open a support ticket — '.$brandName">
    <div class="mx-auto w-full max-w-2xl px-4 py-12 sm:px-6 sm:py-16 lg:py-20">
        <a href="{{ route('account.tickets.index') }}" class="text-sm font-semibold text-brand hover:text-brand-dark focus-visible:rounded focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand">
            <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i> Support tickets
        </a>
        <h1 class="mt-6 text-3xl font-semibold tracking-tight">Open a support ticket</h1>
        <p class="mt-3 leading-7 text-muted">Describe the problem, what you have already tried, and how it affects your work. Please do not include passwords.</p>

        <form method="POST" action="{{ route('account.tickets.store') }}" enctype="multipart/form-data" class="mt-8 grid gap-5 card p-6 sm:p-8" novalidate>
            @csrf
            <x-form.errors />
            <x-form.field name="subject" label="Subject" placeholder="Briefly describe the problem" required />
            <x-form.field name="body" label="Details" placeholder="Describe the problem, what you tried, and how it affects your work" :rows="8" required />
            <div class="grid gap-2"><label for="attachments" class="text-sm font-semibold">Attachments (optional, up to 5 files; 10 MB each)</label><input id="attachments" name="attachments[]" type="file" multiple accept=".pdf,.png,.jpg,.jpeg,.gif,.txt,.csv,.doc,.docx,.xls,.xlsx,.zip" class="rounded border border-line px-3 py-2"><p class="text-sm text-muted">Do not upload passwords or other secrets.</p>@error('attachments.*')<p class="text-sm text-[#b42318]">{{ $message }}</p>@enderror</div>
            <x-button>Open ticket</x-button>
        </form>
    </div>
</x-layouts.site>
