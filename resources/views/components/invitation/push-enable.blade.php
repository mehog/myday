@if (! empty($isPersonalLink) && $guest && config('webpush.vapid.public_key'))
    <div class="mt-4 flex flex-col items-center gap-2" x-show="! subscribed" x-cloak>
        <button
            type="button"
            class="text-sm text-[var(--color-primary)] hover:underline transition disabled:opacity-60"
            :disabled="subscribing"
            @click="
                subscribing = true;
                pushError = null;
                subscribeToPush(subscribeUrl).then((result) => {
                    subscribing = false;
                    if (result.ok) {
                        subscribed = true;
                        showPushPrompt = false;
                    } else if (result.error) {
                        pushError = result.error;
                    }
                });
            "
        >
            <span x-show="! subscribing">{{ __('app.push_enable_notifications') }}</span>
            <span x-show="subscribing">{{ __('invitation.saving') }}</span>
        </button>

        @include('components.invitation.push-error-feedback')
    </div>
@endif
