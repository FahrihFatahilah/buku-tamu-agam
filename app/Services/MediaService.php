<?php

namespace App\Services;

use App\Models\WeddingMedia;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
    private const ALLOWED_IMAGE_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    private const ALLOWED_VIDEO_MIMES = ['video/mp4', 'video/webm'];
    private const ALLOWED_AUDIO_MIMES = ['audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav'];
    private const MAX_IMAGE_SIZE = 10 * 1024 * 1024; // 10MB
    private const MAX_AUDIO_SIZE = 20 * 1024 * 1024; // 20MB

    public function __construct(private AuditLogService $audit) {}

    public function uploadImage(Wedding $wedding, UploadedFile $file, string $collection): WeddingMedia
    {
        $this->validateFile($file, self::ALLOWED_IMAGE_MIMES, self::MAX_IMAGE_SIZE);

        $path = $this->store($file, "weddings/{$wedding->id}/{$collection}");

        [$width, $height] = $this->getImageDimensions($file);

        $media = WeddingMedia::create([
            'wedding_id'    => $wedding->id,
            'collection'    => $collection,
            'file_path'     => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type'     => $file->getMimeType(),
            'file_size'     => $file->getSize(),
            'width'         => $width,
            'height'        => $height,
            'sort_order'    => WeddingMedia::where('wedding_id', $wedding->id)->where('collection', $collection)->max('sort_order') + 1,
        ]);

        $this->audit->log('media.uploaded', 'wedding_media', $media->id, [
            'collection' => $collection,
        ], $wedding->id);

        return $media;
    }

    /**
     * Store a file and return its path (for non-gallery uploads like QRIS images).
     */
    public function storeFile(UploadedFile $file, string $directory): string
    {
        $this->validateFile($file, self::ALLOWED_IMAGE_MIMES, self::MAX_IMAGE_SIZE);
        return $this->store($file, $directory);
    }

    public function delete(WeddingMedia $media): void
    {
        Storage::delete($media->file_path);
        $this->audit->log('media.deleted', 'wedding_media', $media->id, [], $media->wedding_id);
        $media->delete();
    }

    private function validateFile(UploadedFile $file, array $allowedMimes, int $maxSize): void
    {
        $mime = $file->getMimeType();

        if (!in_array($mime, $allowedMimes)) {
            throw new \InvalidArgumentException("File type {$mime} is not allowed.");
        }

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File size exceeds limit.');
        }
    }

    private function store(UploadedFile $file, string $directory): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($directory, $filename, 'public');
    }

    private function getImageDimensions(UploadedFile $file): array
    {
        try {
            [$width, $height] = getimagesize($file->getRealPath());
            return [$width, $height];
        } catch (\Exception) {
            return [null, null];
        }
    }
}
