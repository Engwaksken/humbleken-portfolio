<x-layouts.site :title="'Delete your account | '.$brandName" description="How to permanently delete your {{ $brandName }} account and the data linked to it.">
    <x-page-hero eyebrow="Your account" title="Delete your account." intro="You can permanently delete your {{ $brandName }} account at any time, from the website or the mobile app." />

    <section class="container-site section">
        <div class="article-body mx-auto max-w-3xl">
            <h2>In the mobile app</h2>
            <ol>
                <li>Open the {{ $brandName }} app and sign in.</li>
                <li>Go to the <strong>Account</strong> tab.</li>
                <li>Scroll down and tap <strong>Delete my account</strong>.</li>
                <li>Enter your password and confirm.</li>
            </ol>

            <h2>On the website</h2>
            <ol>
                <li><a href="{{ route('login') }}">Sign in</a> to your account.</li>
                <li>Open your <a href="{{ route('account.dashboard') }}">account page</a>.</li>
                <li>Scroll to <strong>Delete account</strong>, enter your password, tick the confirmation box and select <strong>Delete my account</strong>.</li>
            </ol>

            <h2>What is deleted</h2>
            <p>Deleting your account immediately and permanently removes:</p>
            <ul>
                <li>your account details (name, email address and password),</li>
                <li>your support tickets, replies and attached files,</li>
                <li>appointment requests and enquiries linked to your account,</li>
                <li>your notification settings and any devices signed in to your account.</li>
            </ul>
            <p>Nothing is kept after deletion, except where the law requires me to keep a record (for example, payment records).</p>

            <h2>Can't sign in?</h2>
            <p>
                If you cannot sign in, or you want enquiries you sent without an account removed,
                @if ($contactEmail)
                    email <a href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a> from the address on your account
                @else
                    <a href="{{ route('contact') }}">contact me</a> using the email address on your account
                @endif
                and I will delete your data within 30 days.
            </p>
            <p>See the <a href="{{ route('privacy') }}">privacy policy</a> for more about how your information is handled.</p>
        </div>
    </section>
</x-layouts.site>
