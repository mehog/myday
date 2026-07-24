<?php

namespace Tests\Feature;

use App\GuestMessageType;
use App\Livewire\GuestContactPage;
use App\Models\Guest;
use App\Models\GuestMessage;
use App\Models\WeddingEvent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class GuestPhotoUploadTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_guest_can_submit_multiple_photos_uploaded_individually(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        $event = WeddingEvent::factory()->create([
            'wedding_date' => now(),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create();

        Livewire::test(GuestContactPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('photoFiles', [
                UploadedFile::fake()->image('first.jpg'),
                UploadedFile::fake()->image('second.jpg'),
            ])
            ->call('submitPhotos')
            ->assertHasNoErrors()
            ->assertDispatched('photos-submitted')
            ->assertSet('photoFiles', [])
            ->assertSet('messageSent', true)
            ->assertSet('lastSentType', 'photo');

        $message = GuestMessage::query()->sole();

        $this->assertSame(GuestMessageType::Photo, $message->type);
        $this->assertCount(2, $message->file_paths);
        $this->assertCount(2, Storage::disk('media')->allFiles('guest-messages/photos'));
    }

    public function test_guest_can_remove_an_uploaded_photo_before_submit(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        $event = WeddingEvent::factory()->create([
            'wedding_date' => now(),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create();

        $component = Livewire::test(GuestContactPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('photoFiles', [
                UploadedFile::fake()->image('first.jpg'),
                UploadedFile::fake()->image('second.jpg'),
            ]);

        $filename = $component->get('photoFiles')[0]->getFilename();

        $component
            ->call('_removeUpload', 'photoFiles', $filename)
            ->assertCount('photoFiles', 1)
            ->call('submitPhotos')
            ->assertHasNoErrors()
            ->assertDispatched('photos-submitted');

        $message = GuestMessage::query()->sole();

        $this->assertCount(1, $message->file_paths);
        $this->assertCount(1, Storage::disk('media')->allFiles('guest-messages/photos'));
    }
}
