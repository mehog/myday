<?php

namespace App\Livewire;

use App\GuestMessageType;
use App\Models\Guest;
use App\Models\GuestMessage;
use App\Models\WeddingEvent;
use App\Support\MediaDisk;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    public bool $messageSent = false;

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

        $this->senderName = $this->guest->name;
    }

    public function canSendPhotos(): bool
    {
        $start = $this->event->wedding_date->copy()->startOfDay();
        $end = $this->event->wedding_date->copy()->addDays(30)->endOfDay();

        return now()->gte($start) && now()->lte($end);
    }

    public function submitText(): void
    {
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
        ]);

        $this->textContent = '';
        $this->markMessageSent('text');
    }

    public function submitAudio(): void
    {
        $this->ensureCanSendMessage();

        $this->validate([
            'audioFile' => ['required', 'file', 'mimetypes:audio/webm,audio/ogg,audio/mp4,audio/mpeg,audio/wav,video/webm', 'max:10240'],
        ], [
            'audioFile.required' => __('invitation.audio_required'),
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
        ]);

        $this->reset('photoFiles');
        $this->markMessageSent('photo');
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
            ->title(__('invitation.contact_page_title').' | '.$this->event->couple_names)
            ->layoutData([
                'event' => $this->event,
                'guest' => $this->guest,
                'isPersonalLink' => true,
            ]);
    }
}
