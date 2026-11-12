<div class="container-fluid py-3">
    <div class="mb-3">
        <h3 class="font-weight-bold mb-1">Notification Preferences</h3>
        <p class="text-muted mb-0">Choose how ZREMS notifies you. In-app notifications appear in the bell icon; email notifications go to your registered address.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('email_test_sent'))
        <div class="alert alert-info">{{ session('email_test_sent') }}</div>
    @endif

    <form wire:submit.prevent="save">
        <div class="card mb-3">
            <div class="card-header"><strong>Delivery Channels</strong></div>
            <div class="card-body">
                <div class="custom-control custom-switch mb-2">
                    <input type="checkbox" class="custom-control-input" id="notify_in_app" wire:model="notify_in_app">
                    <label class="custom-control-label" for="notify_in_app">
                        <strong>In-app (bell icon)</strong> — recommended
                    </label>
                </div>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="notify_email" wire:model="notify_email">
                    <label class="custom-control-label" for="notify_email">
                        <strong>Email</strong> — sent to {{ auth()->user()->email ?? '—' }}
                    </label>
                </div>
                <p class="text-muted small mt-2 mb-0">
                    Turning both off will silence all ZREMS notifications for your account.
                </p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Categories</strong></div>
            <div class="card-body">
                <p class="text-muted small">Mute categories you don't want to hear about. Applies to both channels above.</p>
                @foreach($categories as $key => $enabled)
                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input"
                               id="cat_{{ $key }}"
                               wire:model="categories.{{ $key }}">
                        <label class="custom-control-label" for="cat_{{ $key }}">
                            {{ $categoryLabels[$key] ?? ucfirst($key) }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><strong>Email delivery test</strong></div>
            <div class="card-body">
                <p class="text-muted small">Send a test message only to your registered address ({{ auth()->user()->email ?? '—' }}). Configured mailer: <strong>{{ config('mail.default') }}</strong>.</p>
                @error('email_test')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
                <button type="button" class="btn btn-outline-primary" wire:click="sendTestEmail" wire:loading.attr="disabled">
                    <i class="fas fa-paper-plane mr-1"></i>Send test email
                </button>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Preferences
        </button>
        <a href="{{ route('notifications.index') }}" class="btn btn-outline-secondary">
            <i class="far fa-bell"></i> View Notifications
        </a>
    </form>
</div>
