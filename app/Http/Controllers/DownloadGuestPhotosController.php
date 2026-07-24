<?php

namespace App\Http\Controllers;

use App\GuestMessageType;
use App\Models\GuestMessage;
use App\Support\MediaDisk;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class DownloadGuestPhotosController extends Controller
{
    public function __invoke(Request $request, ?GuestMessage $message = null): BinaryFileResponse|StreamedResponse
    {
        $wedding = auth()->user()?->weddingEvent;
        abort_unless($wedding, 403);

        $validated = $request->validate([
            'indexes' => ['sometimes', 'array', 'min:1'],
            'indexes.*' => ['integer', 'min:0'],
        ]);

        $indexes = isset($validated['indexes'])
            ? array_values(array_unique(array_map('intval', $validated['indexes'])))
            : null;

        if ($indexes !== null) {
            abort_unless($message !== null, 422);
            abort_unless($message->wedding_event_id === $wedding->id, 403);
            abort_unless($message->type === GuestMessageType::Photo, 404);

            $paths = collect($message->file_paths ?? []);
            $selectedPaths = collect($indexes)
                ->map(fn (int $index) => $paths->get($index))
                ->filter(fn ($path): bool => is_string($path) && $path !== '')
                ->values();

            abort_if($selectedPaths->count() !== count($indexes), 404);

            if ($selectedPaths->count() === 1) {
                return $this->downloadSingle($selectedPaths->first());
            }

            return $this->downloadZip(
                collect([(object) ['file_paths' => $selectedPaths->all()]]),
                'guest-photos-'.$message->id.'-selected.zip',
            );
        }

        $query = GuestMessage::query()
            ->where('wedding_event_id', $wedding->id)
            ->where('type', GuestMessageType::Photo);

        if ($message) {
            abort_unless($message->wedding_event_id === $wedding->id, 403);
            $query->where('id', $message->id);
        }

        $photos = $query->orderBy('created_at')->get();

        abort_if($photos->isEmpty(), 404);

        $filename = $message
            ? 'guest-photos-'.$message->id.'.zip'
            : 'guest-photos.zip';

        return $this->downloadZip($photos, $filename);
    }

    protected function downloadSingle(string $path): StreamedResponse|BinaryFileResponse
    {
        abort_unless(MediaDisk::disk()->exists($path), 404);

        return MediaDisk::disk()->download($path, basename($path));
    }

    /**
     * @param  Collection<int, object>  $photoMessages
     */
    protected function downloadZip(Collection $photoMessages, string $filename): BinaryFileResponse
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'guest-photos-').'.zip';
        $zip = new ZipArchive;
        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $added = 0;

        foreach ($photoMessages as $messageIndex => $photoMessage) {
            foreach ($photoMessage->file_paths ?? [] as $fileIndex => $filePath) {
                if (! is_string($filePath) || $filePath === '' || ! MediaDisk::disk()->exists($filePath)) {
                    continue;
                }

                $stream = MediaDisk::disk()->readStream($filePath);

                if ($stream === false) {
                    continue;
                }

                $zip->addFromString(
                    ($messageIndex + 1).'_'.($fileIndex + 1).'_'.basename($filePath),
                    stream_get_contents($stream) ?: ''
                );

                fclose($stream);
                $added++;
            }
        }

        $zip->close();

        abort_if($added === 0, 404);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }
}
