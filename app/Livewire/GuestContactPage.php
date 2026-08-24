<?php

namespace App\Livewire;

use App\GuestMessageType;
use App\Models\Guest;
use App\Models\GuestMessage;
use App\Models\WeddingEvent;
use App\PlanFeature;
use App\Support\MediaDisk;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.invitation')]
class GuestContactPage extends Component
{
    use WithFileUploads;

    public WeddingEvent $event;

    public Guest $guest;

    public string $senderName = '';

    public string $textContent = '';

    public $audioFile = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $photoFiles = [];

    /** @var array<int, TemporaryUploadedFile> */
    public array $videoFiles = [];

    public bool $messageSent = false;

    public bool $isDemo = false;

    public bool $fromPlaceCardQr = false;

    public ?string $lastSentType = null;

    public function mount(string $slug, string $token): void
    {
        $this->event = WeddingEvent::query()
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $this->event->canBeViewedBy(auth()->user())) {
            abort(404);
        }

        if (! $this->event->is_active) {
            abort(403);
        }

        $this->guest = $this->event->guests()
            ->where('token', $token)
            ->firstOrFail();

        $this->isDemo = $this->event->is_demo;
        $this->senderName = $this->guest->name;
        $this->fromPlaceCardQr = request()->query('qr-code') === 'true';
    }

    public function canSendPhotos(): bool
    {
        return $this->event->acceptsGuestPhotos();
    }

    public function canSendVideos(): bool
    {
        return $this->event->acceptsGuestPhotos();
    }

    public function isWithinGuestMediaWindow(): bool
    {
        return $this->event->isWithinGuestPhotoWindow();
    }

    public function hasGuestMediaPlan(): bool
    {
        return $this->event->hasFeature(PlanFeature::QrPhotoAlbum);
    }

    public function submitText(): void
    {
        if ($this->isDemo) {
            $this->dispatch('demo-message-sent');

            return;
        }

        $this->ensureCanSendMessage();

        $validated = $this->validate([
            'textContent' => ['required', 'string', 'min:1', 'max:5000'],
        ], [
            'textContent.required' => __('invitation.message_required'),
        ]);

        GuestMessage::query()->create([
            'wedding_event_id' => $this->event->id,
            'guest_id' => $this->guest->id,
            'sender_name' => $this->senderName,
            'type' => GuestMessageType::Text,
            'content' => trim($validated['textContent']),
            ...$this->fingerprint(),
        ]);

        $this->textContent = '';
        $this->markMessageSent('text');
    }

    public function submitAudio(): void
    {
        if ($this->isDemo) {
            $this->dispatch('demo-message-sent');

            return;
        }

        $this->ensureCanSendMessage();

        $this->validate([
            'audioFile' => ['required', 'file', 'mimetypes:audio/webm,audio/ogg,audio/mp4,audio/mpeg,audio/wav,video/webm,audio/3gpp,audio/aac', 'max:10240'],
        ], [
            'audioFile.required' => __('invitation.audio_required'),
            'audioFile.mimetypes' => __('invitation.audio_format_error'),
        ]);

        $extension = $this->audioFile->getClientOriginalExtension() ?: 'webm';
        $path = $this->audioFile->storeAs(
            'guest-messages/audio',
            Str::uuid().'.'.$extension,
            MediaDisk::name()
        );

        GuestMessage::query()->create([
            'wedding_event_id' => $this->event->id,
            'guest_id' => $this->guest->id,
            'sender_name' => $this->senderName,
            'type' => GuestMessageType::Audio,
            'file_path' => $path,
            ...$this->fingerprint(),
        ]);

        $this->reset('audioFile');
        $this->markMessageSent('audio');
    }

    public function submitPhotos(): void
    {
        if (! $this->canSendPhotos()) {
            throw ValidationException::withMessages([
                'photoFiles' => __('invitation.photos_not_available'),
            ]);
        }

        if ($this->isDemo) {
            $this->dispatch('demo-message-sent');
            $this->dispatch('photos-submitted');

            return;
        }

        $this->ensureCanSendMessage();

        $this->validate([
            'photoFiles' => ['required', 'array', 'min:1', 'max:10'],
            'photoFiles.*' => ['required', 'image', 'max:5120'],
        ], [
            'photoFiles.required' => __('invitation.photos_required'),
            'photoFiles.max' => __('invitation.photos_max'),
        ]);

        $paths = [];

        foreach ($this->photoFiles as $photo) {
            $paths[] = $photo->store(
                'guest-messages/photos',
                MediaDisk::name()
            );
        }

        GuestMessage::query()->create([
            'wedding_event_id' => $this->event->id,
            'guest_id' => $this->guest->id,
            'sender_name' => $this->senderName,
            'type' => GuestMessageType::Photo,
            'file_paths' => $paths,
            ...$this->fingerprint(),
        ]);

        $this->reset('photoFiles');
        $this->markMessageSent('photo');
        $this->dispatch('photos-submitted');
    }

    public function submitVideos(): void
    {
        if (! $this->canSendVideos()) {
            throw ValidationException::withMessages([
                'videoFiles' => __('invitation.videos_not_available'),
            ]);
        }

        if ($this->isDemo) {
            $this->dispatch('demo-message-sent');
            $this->dispatch('videos-submitted');

            return;
        }

        $this->ensureCanSendMessage();

        $this->validate([
            'videoFiles' => ['required', 'array', 'min:1', 'max:3'],
            'videoFiles.*' => ['required', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm,video/3gpp', 'max:51200'],
        ], [
            'videoFiles.required' => __('invitation.videos_required'),
            'videoFiles.max' => __('invitation.videos_max'),
            'videoFiles.*.mimetypes' => __('invitation.videos_format_error'),
        ]);

        $paths = [];

        foreach ($this->videoFiles as $video) {
            $paths[] = $video->store(
                'guest-messages/videos',
                MediaDisk::name()
            );
        }

        GuestMessage::query()->create([
            'wedding_event_id' => $this->event->id,
            'guest_id' => $this->guest->id,
            'sender_name' => $this->senderName,
            'type' => GuestMessageType::Video,
            'file_paths' => $paths,
            ...$this->fingerprint(),
        ]);

        $this->reset('videoFiles');
        $this->markMessageSent('video');
        $this->dispatch('videos-submitted');
    }

    protected function ensureCanSendMessage(): void
    {
        $count = GuestMessage::query()
            ->where('guest_id', $this->guest->id)
            ->where('created_at', '>=', now()->startOfDay())
            ->count();

        if ($count >= 10) {
            throw ValidationException::withMessages([
                'textContent' => __('invitation.message_rate_limit'),
            ]);
        }
    }

    /**
     * @return array{ip_hash: ?string, user_agent: ?string, device_type: string, browser: ?string, os: ?string}
     */
    private function fingerprint(): array
    {
        $request = request();
        $agent = new Agent;

        if ($request->userAgent()) {
            $agent->setUserAgent($request->userAgent());
        }

        return [
            'ip_hash' => $request->ip() ? hash('sha256', $request->ip()) : null,
            'user_agent' => $request->userAgent(),
            'device_type' => match (true) {
                $agent->isTablet() => 'tablet',
                $agent->isMobile() => 'mobile',
                default => 'desktop',
            },
            'browser' => $agent->browser() ?: null,
            'os' => $agent->platform() ?: null,
        ];
    }

    protected function markMessageSent(string $type): void
    {
        $this->messageSent = true;
        $this->lastSentType = $type;
    }

    public function dismissSuccess(): void
    {
        $this->messageSent = false;
        $this->lastSentType = null;
    }

    public function render()
    {
        return view('livewire.guest-contact-page')
            ->title(
                ($this->fromPlaceCardQr
                    ? __('invitation.contact_page_qr_title')
                    : __('invitation.contact_page_title'))
                .' | '.$this->event->couple_names
            )
            ->layoutData([
                'event' => $this->event,
                'guest' => $this->guest,
                'isPersonalLink' => true,
            ]);
    }
}
