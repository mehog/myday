<?php

namespace Tests\Feature;

use App\Filament\App\Resources\GuestMessagesResource\Pages\ViewAllGuestPhotos;
use App\GuestMessageType;
use App\Models\Guest;
use App\Models\GuestMessage;
use App\Models\User;
use App\Models\WeddingEvent;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;

class ViewAllGuestPhotosTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_user_can_view_all_photos_from_their_wedding(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        [$user, $messages] = $this->createPhotoMessages(2, photosPerMessage: 2);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(ViewAllGuestPhotos::class)
            ->assertSuccessful()
            ->assertSet('totalPhotoCount', 4)
            ->assertSet('photos', fn (array $photos): bool => count($photos) === 4)
            ->assertSee($messages[0]->sender_name);
    }

    public function test_photos_load_incrementally_in_batches(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        [$user] = $this->createPhotoMessages(ViewAllGuestPhotos::PER_PAGE + 2, photosPerMessage: 1);

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(ViewAllGuestPhotos::class)
            ->assertSet('photos', fn (array $photos): bool => count($photos) === ViewAllGuestPhotos::PER_PAGE)
            ->assertSet('hasMore', true)
            ->call('loadMore')
            ->assertSet('photos', fn (array $photos): bool => count($photos) === ViewAllGuestPhotos::PER_PAGE + 2)
            ->assertSet('hasMore', false);
    }

    public function test_user_cannot_see_photos_from_another_wedding(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        $this->createPhotoMessages(1, photosPerMessage: 2);

        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        WeddingEvent::factory()->for($otherUser)->create();

        $this->actingAs($otherUser);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(ViewAllGuestPhotos::class)
            ->assertSuccessful()
            ->assertSet('totalPhotoCount', 0)
            ->assertSet('photos', []);
    }

    public function test_user_without_wedding_cannot_open_gallery(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);
        Filament::setCurrentPanel(Filament::getPanel('app'));

        Livewire::test(ViewAllGuestPhotos::class)
            ->assertForbidden();
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
}
