<?php

namespace App\Services;

use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlaylistService
{
    private const ALLOWED_AUDIO_MIMES = ['audio/mpeg', 'audio/mp3', 'audio/ogg', 'audio/wav', 'audio/x-wav'];
    private const MAX_AUDIO_SIZE = 20 * 1024 * 1024;

    public function __construct(private AuditLogService $audit) {}

    public function getOrCreate(Wedding $wedding): Playlist
    {
        return $wedding->playlists()->firstOrCreate(
            ['wedding_id' => $wedding->id],
            ['name' => 'Default', 'is_active' => true, 'autoplay' => true, 'loop' => true]
        );
    }

    public function updateSettings(Playlist $playlist, array $data): Playlist
    {
        $playlist->update($data);
        return $playlist;
    }

    public function addTrack(Playlist $playlist, UploadedFile $file, array $meta = []): PlaylistItem
    {
        $mime = $file->getMimeType();
        if (!in_array($mime, self::ALLOWED_AUDIO_MIMES)) {
            throw new \InvalidArgumentException("File type {$mime} is not allowed.");
        }
        if ($file->getSize() > self::MAX_AUDIO_SIZE) {
            throw new \InvalidArgumentException('File size exceeds 20MB limit.');
        }

        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs("weddings/{$playlist->wedding_id}/audio", $filename, 'public');

        $item = $playlist->items()->create([
            'title'      => $meta['title'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'artist'     => $meta['artist'] ?? null,
            'file_path'  => $path,
            'file_size'  => $file->getSize(),
            'sort_order' => $playlist->items()->max('sort_order') + 1,
        ]);

        $this->audit->log('playlist.track_added', 'playlist_item', $item->id, [
            'title' => $item->title,
        ], $playlist->wedding_id);

        return $item;
    }

    public function deleteTrack(PlaylistItem $item): void
    {
        Storage::delete($item->file_path);
        $this->audit->log('playlist.track_deleted', 'playlist_item', $item->id, [
            'title' => $item->title,
        ], $item->playlist->wedding_id);
        $item->delete();
    }

    public function reorder(Playlist $playlist, array $orderedIds): void
    {
        foreach ($orderedIds as $sort => $id) {
            $playlist->items()->where('id', $id)->update(['sort_order' => $sort]);
        }
    }
}
