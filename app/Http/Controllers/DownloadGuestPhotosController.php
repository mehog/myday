<?php

namespace App\Http\Controllers;

use App\GuestMessageType;
use App\Models\GuestMessage;
use App\Support\MediaDisk;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class DownloadGuestPhotosController extends Controller
{
    public function __invoke(?GuestMessage $message = null): BinaryFileResponse
    {
        $wedding = auth()->user()?->weddingEvent;
        abort_unless($wedding, 403);

        $query = GuestMessage::query()
            ->where('wedding_event_id', $wedding->id)
            ->where('type', GuestMessageType::Photo);

        if ($message) {
            abort_unless($message->wedding_event_id === $wedding->id, 403);
            $query->where('id', $message->id);
        }

        $photos = $query->orderBy('created_at')->get();

        abort_if($photos->isEmpty(), 404);

        $tmpPath = tempnam(sys_get_temp_dir(), 'guest-photos-').'.zip';
        $zip = new ZipArchive;
        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($photos as $messageIndex => $photoMessage) {
            foreach ($photoMessage->file_paths ?? [] as $fileIndex => $filePath) {
                if (! MediaDisk::disk()->exists($filePath)) {
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
            }
        }

        $zip->close();

        $filename = $message
            ? 'guest-photos-'.$message->id.'.zip'
            : 'guest-photos.zip';

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }
}
