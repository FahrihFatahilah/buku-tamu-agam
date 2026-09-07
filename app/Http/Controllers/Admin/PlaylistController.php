<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\Wedding;
use App\Services\PlaylistService;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function __construct(private PlaylistService $playlistService) {}

    public function index(Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $playlist = $this->playlistService->getOrCreate($wedding);
        $playlist->load('items');

        return view('admin.playlist.index', compact('wedding', 'playlist'));
    }

    public function updateSettings(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $validated = $request->validate([
            'autoplay' => 'boolean',
            'loop'     => 'boolean',
            'shuffle'  => 'boolean',
            'volume'   => 'integer|min:0|max:100',
        ]);

        $playlist = $this->playlistService->getOrCreate($wedding);
        $this->playlistService->updateSettings($playlist, $validated);

        return back()->with('success', 'Pengaturan playlist disimpan.');
    }

    public function addTrack(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $request->validate([
            'file'   => 'required|file|max:20480',
            'title'  => 'nullable|string|max:100',
            'artist' => 'nullable|string|max:100',
        ]);

        $playlist = $this->playlistService->getOrCreate($wedding);

        try {
            $this->playlistService->addTrack($playlist, $request->file('file'), [
                'title'  => $request->title,
                'artist' => $request->artist,
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return back()->with('success', 'Lagu berhasil ditambahkan.');
    }

    public function deleteTrack(Wedding $wedding, PlaylistItem $item)
    {
        $this->authorize('update', $wedding);
        abort_if($item->playlist->wedding_id !== $wedding->id, 403);

        $this->playlistService->deleteTrack($item);

        return back()->with('success', 'Lagu berhasil dihapus.');
    }

    public function reorder(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);

        $playlist = $this->playlistService->getOrCreate($wedding);
        $this->playlistService->reorder($playlist, $request->order);

        return response()->json(['ok' => true]);
    }
}
