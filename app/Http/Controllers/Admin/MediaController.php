<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\WeddingMedia;
use App\Services\MediaService;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function __construct(private MediaService $mediaService) {}

    public function index(Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $media = $wedding->media()->orderBy('collection')->orderBy('sort_order')->get()
            ->groupBy('collection');

        return view('admin.media.index', compact('wedding', 'media'));
    }

    public function store(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $request->validate([
            'file'       => 'required|file|max:10240',
            'collection' => 'required|in:hero,couple,gallery,family,prewedding',
            'alt_text'   => 'nullable|string|max:200',
        ]);

        try {
            $media = $this->mediaService->uploadImage($wedding, $request->file('file'), $request->collection);

            if ($request->alt_text) {
                $media->update(['alt_text' => $request->alt_text]);
            }
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return back()->with('success', 'Media berhasil diupload.');
    }

    public function update(Request $request, Wedding $wedding, WeddingMedia $media)
    {
        $this->authorize('update', $wedding);
        abort_if($media->wedding_id !== $wedding->id, 403);

        $request->validate(['alt_text' => 'nullable|string|max:200']);
        $media->update(['alt_text' => $request->alt_text]);

        return back()->with('success', 'Media berhasil diperbarui.');
    }

    public function destroy(Wedding $wedding, WeddingMedia $media)
    {
        $this->authorize('update', $wedding);
        abort_if($media->wedding_id !== $wedding->id, 403);

        $this->mediaService->delete($media);

        return back()->with('success', 'Media berhasil dihapus.');
    }

    public function reorder(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $request->validate([
            'order'   => 'required|array',
            'order.*' => 'integer',
        ]);

        foreach ($request->order as $sort => $id) {
            WeddingMedia::where('id', $id)->where('wedding_id', $wedding->id)
                ->update(['sort_order' => $sort]);
        }

        return response()->json(['ok' => true]);
    }
}
