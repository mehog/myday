<?php

namespace App\Livewire\Dashboard;

use App\Jobs\SendGuestPushNotificationsJob;
use App\Livewire\Dashboard\Concerns\RendersDashboard;
use App\Models\Guest;
use App\Models\PushNotificationLog;
use App\Models\WeddingEvent;
use App\PlanFeature;
use App\PushNotificationRecipientType;
use App\PushNotificationStatus;
use App\Services\WeddingScheduledNotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreatePush extends Component
{
    use RendersDashboard;

    public string $title = '';

    public string $body = '';

    public ?string $scheduled_at = null;

    public string $recipient_type = '';

    /** @var list<int|string> */
    public array $selected_guest_ids = [];

    public ?string $flashMessage = null;

    public ?string $flashError = null;

    public function mount(): void
    {
        abort_unless($this->wedding() instanceof WeddingEvent, 404);

        $this->recipient_type = PushNotificationRecipientType::All->value;
    }

    public function render()
    {
        return $this->dashboardView('livewire.dashboard.create-push', [
            'subscriberCount' => $this->subscriberCount(),
            'subscriberOptions' => $this->subscriberOptions(),
        ], __('dashboard.pushes_create'), [
            ['label' => __('dashboard.nav.pushes'), 'url' => route('dashboard.pushes')],
            ['label' => __('dashboard.pushes_create'), 'url' => null],
        ], backUrl: route('dashboard.pushes'));
    }

    protected function wedding(): ?WeddingEvent
    {
        return auth()->user()?->weddingEvent;
    }

    public function subscriberCount(): int
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return 0;
        }

        return $wedding->guests()
            ->whereHas('pushSubscriptions')
            ->count();
    }

    /**
     * @return array<int, string>
     */
    public function subscriberOptions(): array
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            return [];
        }

        return $wedding->guests()
            ->whereHas('pushSubscriptions')
            ->orderBy('name')
            ->get()
            ->mapWithKeys(fn (Guest $guest) => [
                $guest->id => $guest->name.' ('.($guest->rsvp_status?->label() ?? __('app.push_notifications_rsvp_pending')).')',
            ])
            ->all();
    }

    public function send(): void
    {
        $wedding = $this->wedding();

        if (! $wedding instanceof WeddingEvent) {
            $this->flashError = __('app.push_notifications_no_wedding');

            return;
        }

        if (! $wedding->hasFeature(PlanFeature::PushSend)) {
            $this->dispatch('open-upgrade-modal');

            return;
        }

        if ($this->scheduled_at === '') {
            $this->scheduled_at = null;
        }

        $rules = [
            'title' => ['required', 'string', 'max:50'],
            'body' => ['required', 'string', 'max:120'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'recipient_type' => ['required', Rule::enum(PushNotificationRecipientType::class)],
            'selected_guest_ids' => ['array'],
        ];

        if ($this->recipient_type === PushNotificationRecipientType::Selected->value) {
            $rules['selected_guest_ids'] = ['required', 'array', 'min:1'];
            $rules['selected_guest_ids.*'] = ['integer'];
        }

        $data = $this->validate($rules);

        $guests = $this->resolveRecipients($wedding->id, $data);

        if ($guests->isEmpty()) {
            $this->flashError = __('app.push_notifications_no_subscribers');

            return;
        }

        $scheduledAt = filled($data['scheduled_at'] ?? null)
            ? Carbon::parse($data['scheduled_at'])
            : null;

        $log = PushNotificationLog::query()->create([
            'wedding_event_id' => $wedding->id,
            'title' => $data['title'],
            'body' => $data['body'],
            'recipient_type' => $data['recipient_type'],
            'sent_to_count' => $guests->count(),
            'guest_ids' => $guests->pluck('id')->all(),
            'status' => PushNotificationStatus::Queued,
            'scheduled_at' => $scheduledAt,
        ]);

        $guestIds = $guests->pluck('id')->all();
        $user = auth()->user();

        if ($scheduledAt !== null && $scheduledAt->isFuture() && $user !== null) {
            app(WeddingScheduledNotificationService::class)->scheduleGuestPush(
                log: $log,
                user: $user,
                sendAt: $scheduledAt,
                guestIds: $guestIds,
            );

            session()->flash('success', __('app.push_notifications_scheduled_success'));
            $this->redirect(route('dashboard.pushes'), navigate: true);

            return;
        }

        SendGuestPushNotificationsJob::dispatch(
            logId: $log->id,
            guestIds: $guestIds,
            title: $data['title'],
            body: $data['body'],
        );

        session()->flash('success', __('app.push_notifications_queued_success'));
        $this->redirect(route('dashboard.pushes'), navigate: true);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return Collection<int, Guest>
     */
    protected function resolveRecipients(int $weddingEventId, array $data): Collection
    {
        $query = Guest::query()
            ->where('wedding_event_id', $weddingEventId)
            ->whereHas('pushSubscriptions');

        if ($data['recipient_type'] === PushNotificationRecipientType::Unanswered->value) {
            $query->whereNull('rsvp_status');
        }

        if ($data['recipient_type'] === PushNotificationRecipientType::Selected->value) {
            $query->whereIn('id', $data['selected_guest_ids'] ?? []);
        }

        return $query->get();
    }
}
