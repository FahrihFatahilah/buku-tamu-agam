<?php

namespace App\Jobs;

use App\Models\WeddingMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessMediaUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(public WeddingMedia $media) {}

    public function handle(): void
    {
        // Skip if file no longer exists
        if (!Storage::disk('public')->exists($this->media->file_path)) {
            return;
        }

        // Update dimensions if not set
        if (!$this->media->width || !$this->media->height) {
            $fullPath = Storage::disk('public')->path($this->media->file_path);
            [$width, $height] = @getimagesize($fullPath) ?: [null, null];

            if ($width && $height) {
                $this->media->update(['width' => $width, 'height' => $height]);
            }
        }
    }
}
