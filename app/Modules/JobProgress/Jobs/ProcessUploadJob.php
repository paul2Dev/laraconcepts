<?php

namespace App\Modules\JobProgress\Jobs;

use App\Modules\JobProgress\Events\UploadProgressUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Fixed step count so the percentage sequence is deterministic regardless of file size. */
    private const CHUNKS = 5;

    /** Simulates per-chunk work (parsing, resizing, etc.) so the demo's progress bar is actually visible. */
    private const CHUNK_DELAY_MICROSECONDS = 400_000;

    public function __construct(
        public readonly string $uploadId,
        public readonly string $path,
        public readonly string $disk = 'local',
    ) {}

    public function handle(): void
    {
        $storage = Storage::disk($this->disk);
        $stream = $storage->readStream($this->path);
        $totalBytes = fstat($stream)['size'];
        $linesProcessed = 0;

        if ($totalBytes === 0) {
            event(new UploadProgressUpdated($this->uploadId, percentage: 100, linesProcessed: 0));
        } else {
            $chunkBytes = (int) ceil($totalBytes / self::CHUNKS);

            while (! feof($stream)) {
                $chunk = fread($stream, $chunkBytes);

                if ($chunk === false || $chunk === '') {
                    break;
                }

                $linesProcessed += substr_count($chunk, "\n");
                usleep(self::CHUNK_DELAY_MICROSECONDS);

                event(new UploadProgressUpdated(
                    uploadId: $this->uploadId,
                    percentage: (int) min(100, round(ftell($stream) / $totalBytes * 100)),
                    linesProcessed: $linesProcessed,
                ));
            }
        }

        fclose($stream);
        $storage->delete($this->path);
    }
}
