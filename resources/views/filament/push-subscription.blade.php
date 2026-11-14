{{-- Turns browser push on or off for the current browser. Used on Notification settings. --}}
<div
    x-data="{
        status: 'checking',
        busy: false,
        error: '',
        publicKey: @js($publicKey),
        libraryInstalled: @js($libraryInstalled),
        storeUrl: @js(route('admin.push-subscriptions.store')),
        destroyUrl: @js(route('admin.push-subscriptions.destroy')),
        csrf: @js(csrf_token()),
        async init() {
            if (! ('serviceWorker' in navigator) || ! ('PushManager' in window) || ! window.isSecureContext) {
                this.status = 'unsupported';
                return;
            }
            if (Notification.permission === 'denied') {
                this.status = 'blocked';
                return;
            }
            const registration = await navigator.serviceWorker.register(@js(asset('push-sw.js')));
            const subscription = await registration.pushManager.getSubscription();
            this.status = subscription ? 'on' : 'off';
        },
        keyBytes() {
            const padded = (this.publicKey + '='.repeat((4 - this.publicKey.length % 4) % 4)).replace(/-/g, '+').replace(/_/g, '/');
            return Uint8Array.from(atob(padded), (c) => c.charCodeAt(0));
        },
        async send(url, method, body) {
            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrf },
                body: JSON.stringify(body),
            });
            if (! response.ok) throw new Error('The server did not accept the request (' + response.status + ').');
        },
        async enable() {
            this.busy = true;
            this.error = '';
            try {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') {
                    this.status = permission === 'denied' ? 'blocked' : 'off';
                    return;
                }
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: this.keyBytes() });
                await this.send(this.storeUrl, 'POST', subscription.toJSON());
                this.status = 'on';
            } catch (e) {
                this.error = e.message || 'Push notifications could not be turned on.';
            } finally {
                this.busy = false;
            }
        },
        async disable() {
            this.busy = true;
            this.error = '';
            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                if (subscription) {
                    await this.send(this.destroyUrl, 'DELETE', { endpoint: subscription.endpoint });
                    await subscription.unsubscribe();
                }
                this.status = 'off';
            } catch (e) {
                this.error = e.message || 'Push notifications could not be turned off.';
            } finally {
                this.busy = false;
            }
        },
    }"
    class="space-y-3 text-sm"
>
    @unless ($libraryInstalled)
        <p class="rounded-lg bg-warning-50 p-3 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400">
            The server cannot send push messages yet: the <code>minishlink/web-push</code> package is not installed.
            Run <code>composer require minishlink/web-push</code> on the server. You can still turn push on here, and messages will start arriving once it is installed.
        </p>
    @endunless

    @if (blank($publicKey))
        <p class="text-danger-600 dark:text-danger-400">Push keys could not be created on this server. Check that the PHP OpenSSL extension is working.</p>
    @else
        <p x-show="status === 'checking'">Checking this browser...</p>
        <p x-show="status === 'unsupported'" x-cloak>This browser does not support push notifications, or the site is not served over HTTPS.</p>
        <p x-show="status === 'blocked'" x-cloak>Notifications are blocked for this site. Allow them in the browser's site settings, then reload this page.</p>

        <div x-show="status === 'off'" x-cloak class="flex flex-wrap items-center gap-3">
            <span>Push notifications are off in this browser.</span>
            <x-filament::button size="sm" x-on:click="enable()" x-bind:disabled="busy" icon="heroicon-o-bell">Turn on in this browser</x-filament::button>
        </div>

        <div x-show="status === 'on'" x-cloak class="flex flex-wrap items-center gap-3">
            <span>Push notifications are on in this browser.</span>
            <x-filament::button size="sm" color="gray" x-on:click="disable()" x-bind:disabled="busy" icon="heroicon-o-bell-slash">Turn off in this browser</x-filament::button>
        </div>

        <p x-show="error" x-text="error" x-cloak class="text-danger-600 dark:text-danger-400"></p>
    @endif
</div>
