<?php

namespace Tests\Feature\Dashboard;

use App\GuestMessageType;
use App\Livewire\Dashboard\GuestPhotos;
use App\Livewire\Dashboard\GuestVideos;
use App\Livewire\Dashboard\Messages;
use App\Models\Guest;
use App\Models\GuestMessage;
use App\Models\User;
use App\Models\WeddingEvent;
use App\Services\GuestMessageMediaGallery;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class GuestMediaGalleryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_user_can_view_all_photos_in_dashboard(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        [$user, $messages] = $this->createPhotoMessages(2, photosPerMessage: 2);

        Livewire::actingAs($user)
            ->test(GuestPhotos::class)
            ->assertSuccessful()
            ->assertSet('totalPhotoCount', 4)
            ->assertSet('photos', fn (array $photos): bool => count($photos) === 4)
            ->assertSee($messages[0]->sender_name);
    }

    public function test_dashboard_photos_load_incrementally(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        [$user] = $this->createPhotoMessages(GuestMessageMediaGallery::PER_PAGE + 2, photosPerMessage: 1);

        Livewire::actingAs($user)
            ->test(GuestPhotos::class)
            ->assertSet('photos', fn (array $photos): bool => count($photos) === GuestMessageMediaGallery::PER_PAGE)
            ->assertSet('hasMore', true)
            ->call('loadMore')
            ->assertSet('photos', fn (array $photos): bool => count($photos) === GuestMessageMediaGallery::PER_PAGE + 2)
            ->assertSet('hasMore', false);
    }

    public function test_user_cannot_see_dashboard_photos_from_another_wedding(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        $this->createPhotoMessages(1, photosPerMessage: 2);

        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        WeddingEvent::factory()->for($otherUser)->create();

        Livewire::actingAs($otherUser)
            ->test(GuestPhotos::class)
            ->assertSuccessful()
            ->assertSet('totalPhotoCount', 0)
            ->assertSet('photos', []);
    }

    public function test_user_without_wedding_cannot_open_dashboard_photo_gallery(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(GuestPhotos::class)
            ->assertForbidden();
    }

    public function test_user_can_view_all_videos_in_dashboard(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        [$user, $message] = $this->createVideoMessage();

        Livewire::actingAs($user)
            ->test(GuestVideos::class)
            ->assertSuccessful()
            ->assertSet('totalVideoCount', 1)
            ->assertSee($message->sender_name);
    }

    public function test_messages_page_renders_photo_lightbox_markup(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        [$user] = $this->createPhotoMessages(1, photosPerMessage: 2);

        Livewire::actingAs($user)
            ->test(Messages::class)
            ->assertSuccessful()
            ->assertSeeHtml('x-data')
            ->assertSeeHtml('carouselIndex')
            ->assertSee(__('app.guest_messages_view_all_photos'));
    }

    /**
     * @return array{0: User, 1: list<GuestMessage>}
     */
    protected function createPhotoMessages(int $messageCount, int $photosPerMessage = 1): array
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create();

        $messages = [];

        for ($i = 0; $i < $messageCount; $i++) {
            $guest = Guest::factory()->for($event)->create([
                'name' => "Guest {$i}",
            ]);

            $paths = [];

            for ($j = 0; $j < $photosPerMessage; $j++) {
                $file = UploadedFile::fake()->image("guest-{$i}-photo-{$j}.jpg");
                $paths[] = $file->store('guest-messages/photos', 'media');
            }

            $messages[] = GuestMessage::query()->create([
                'wedding_event_id' => $event->id,
                'guest_id' => $guest->id,
                'sender_name' => $guest->name,
                'type' => GuestMessageType::Photo,
                'file_paths' => $paths,
            ]);
        }

        return [$user, $messages];
    }

    /**
     * @return array{0: User, 1: GuestMessage}
     */
    protected function createVideoMessage(): array
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create();
        $guest = Guest::factory()->for($event)->create(['name' => 'Video Guest']);

        $path = UploadedFile::fake()->create('clip.mp4', 100, 'video/mp4')
            ->store('guest-messages/videos', 'media');

        $message = GuestMessage::query()->create([
            'wedding_event_id' => $event->id,
            'guest_id' => $guest->id,
            'sender_name' => $guest->name,
            'type' => GuestMessageType::Video,
            'file_paths' => [$path],
        ]);

        return [$user, $message];
    }
}
