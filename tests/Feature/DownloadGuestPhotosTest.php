<?php

namespace Tests\Feature;

use App\GuestMessageType;
use App\Models\Guest;
use App\Models\GuestMessage;
use App\Models\User;
use App\Models\WeddingEvent;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\RefreshInMemoryDatabase;
use Tests\TestCase;
use ZipArchive;

class DownloadGuestPhotosTest extends TestCase
{
    use RefreshInMemoryDatabase;

    public function test_user_can_download_all_photos_for_a_message_as_zip(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        [$user, $message] = $this->createPhotoMessageWithFiles(2);

        $response = $this->actingAs($user)
            ->get(route('guest-messages.photos.download', ['message' => $message->id]));

        $response->assertOk();
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.zip', (string) $response->headers->get('content-disposition'));
    }

    public function test_user_can_download_a_single_selected_photo(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        [$user, $message, $paths] = $this->createPhotoMessageWithFiles(2);

        $response = $this->actingAs($user)
            ->get(route('guest-messages.photos.download', [
                'message' => $message->id,
                'indexes' => [1],
            ]));

        $response->assertOk();
        $this->assertStringContainsString(basename($paths[1]), (string) $response->headers->get('content-disposition'));
        $this->assertStringNotContainsString('.zip', (string) $response->headers->get('content-disposition'));
    }

    public function test_user_can_download_multiple_selected_photos_as_zip(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        [$user, $message] = $this->createPhotoMessageWithFiles(3);

        $response = $this->actingAs($user)
            ->get(route('guest-messages.photos.download', [
                'message' => $message->id,
                'indexes' => [0, 2],
            ]));

        $response->assertOk();
        $this->assertStringContainsString('.zip', (string) $response->headers->get('content-disposition'));

        $file = $response->baseResponse->getFile();
        $this->assertNotNull($file);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($file->getPathname()));
        $this->assertSame(2, $zip->numFiles);
        $zip->close();
    }

    public function test_user_cannot_download_photos_from_another_wedding(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        [, $message] = $this->createPhotoMessageWithFiles(1);
        /** @var User $otherUser */
        $otherUser = User::factory()->create();
        WeddingEvent::factory()->for($otherUser)->create();

        $this->actingAs($otherUser)
            ->get(route('guest-messages.photos.download', [
                'message' => $message->id,
                'indexes' => [0],
            ]))
            ->assertForbidden();
    }

    public function test_invalid_photo_index_returns_not_found(): void
    {
        Storage::fake('media');
        config()->set('filesystems.media_disk', 'media');

        [$user, $message] = $this->createPhotoMessageWithFiles(1);

        $this->actingAs($user)
            ->get(route('guest-messages.photos.download', [
                'message' => $message->id,
                'indexes' => [5],
            ]))
            ->assertNotFound();
    }

    /**
     * @return array{0: User, 1: GuestMessage, 2: list<string>}
     */
    protected function createPhotoMessageWithFiles(int $count): array
    {
        $user = User::factory()->create();
        $event = WeddingEvent::factory()->for($user)->create();
        $guest = Guest::factory()->for($event)->create();

        $paths = [];

        for ($i = 0; $i < $count; $i++) {
            $file = UploadedFile::fake()->image("photo-{$i}.jpg");
            $paths[] = $file->store('guest-messages/photos', 'media');
        }

        $message = GuestMessage::query()->create([
            'wedding_event_id' => $event->id,
            'guest_id' => $guest->id,
            'sender_name' => $guest->name,
            'type' => GuestMessageType::Photo,
            'file_paths' => $paths,
        ]);

        return [$user, $message, $paths];
    }
}
