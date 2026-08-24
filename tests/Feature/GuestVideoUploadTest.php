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

class GuestVideoUploadTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_guest_can_submit_multiple_videos_uploaded_individually(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        $event = WeddingEvent::factory()->paid()->create([
            'wedding_date' => now(),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create();

        Livewire::test(GuestContactPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('videoFiles', [
                UploadedFile::fake()->create('first.mp4', 1024, 'video/mp4'),
                UploadedFile::fake()->create('second.mp4', 1024, 'video/mp4'),
            ])
            ->call('submitVideos')
            ->assertHasNoErrors()
            ->assertDispatched('videos-submitted')
            ->assertSet('videoFiles', [])
            ->assertSet('messageSent', true)
            ->assertSet('lastSentType', 'video');

        $message = GuestMessage::query()->sole();

        $this->assertSame(GuestMessageType::Video, $message->type);
        $this->assertCount(2, $message->file_paths);
        $this->assertCount(2, Storage::disk('media')->allFiles('guest-messages/videos'));
    }

    public function test_guest_cannot_submit_videos_when_album_is_closed(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        $event = WeddingEvent::factory()->paid()->create([
            'wedding_date' => now()->addWeek(),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create();

        Livewire::test(GuestContactPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('videoFiles', [
                UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4'),
            ])
            ->call('submitVideos')
            ->assertHasErrors(['videoFiles']);

        $this->assertSame(0, GuestMessage::query()->count());
    }

    public function test_demo_event_does_not_persist_video_submissions(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        $event = WeddingEvent::factory()->paid()->create([
            'wedding_date' => now(),
            'is_active' => true,
            'is_demo' => true,
        ]);
        $guest = Guest::factory()->for($event)->create();

        Livewire::test(GuestContactPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('videoFiles', [
                UploadedFile::fake()->create('clip.mp4', 1024, 'video/mp4'),
            ])
            ->call('submitVideos')
            ->assertHasNoErrors()
            ->assertDispatched('videos-submitted');

        $this->assertSame(0, GuestMessage::query()->count());
        $this->assertCount(0, Storage::disk('media')->allFiles('guest-messages/videos'));
    }

    public function test_oversized_video_fails_validation(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        $event = WeddingEvent::factory()->paid()->create([
            'wedding_date' => now(),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create();

        Livewire::test(GuestContactPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('videoFiles', [
                UploadedFile::fake()->create('large.mp4', 100000, 'video/mp4'),
            ])
            ->call('submitVideos')
            ->assertHasErrors(['videoFiles']);

        $this->assertSame(0, GuestMessage::query()->count());
    }

    public function test_invalid_video_mime_fails_validation(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        $event = WeddingEvent::factory()->paid()->create([
            'wedding_date' => now(),
            'is_active' => true,
        ]);
        $guest = Guest::factory()->for($event)->create();

        Livewire::test(GuestContactPage::class, [
            'slug' => $event->slug,
            'token' => $guest->token,
        ])
            ->set('videoFiles', [
                UploadedFile::fake()->create('clip.txt', 100, 'text/plain'),
            ])
            ->call('submitVideos')
            ->assertHasErrors(['videoFiles.0']);

        $this->assertSame(0, GuestMessage::query()->count());
    }
}
