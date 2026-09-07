<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Models\WeddingEvent;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Wedding $wedding)
    {
        $this->authorize('update', $wedding);
        $events = $wedding->events()->get();
        return view('admin.events.index', compact('wedding', 'events'));
    }

    public function store(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'type'       => 'required|in:akad,reception,pengajian,siraman,custom',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
            'venue'      => 'nullable|string|max:200',
            'address'    => 'nullable|string|max:500',
            'maps_url'   => 'nullable|url|max:500',
            'dress_code' => 'nullable|string|max:100',
            'notes'      => 'nullable|string|max:500',
            'sort_order' => 'integer',
        ]);
        $validated['is_public'] = $request->boolean('is_public');

        $wedding->events()->create($validated);

        return back()->with('success', 'Acara berhasil ditambahkan.');
    }

    public function update(Request $request, Wedding $wedding, WeddingEvent $event)
    {
        $this->authorize('update', $wedding);
        abort_if($event->wedding_id !== $wedding->id, 403);

        $validated = $request->validate([
            'name'       => 'required|string|max:100',
            'type'       => 'required|in:akad,reception,pengajian,siraman,custom',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
            'venue'      => 'nullable|string|max:200',
            'address'    => 'nullable|string|max:500',
            'maps_url'   => 'nullable|url|max:500',
            'dress_code' => 'nullable|string|max:100',
            'notes'      => 'nullable|string|max:500',
            'sort_order' => 'integer',
        ]);
        $validated['is_public'] = $request->boolean('is_public');

        $event->update($validated);

        return back()->with('success', 'Acara berhasil diperbarui.');
    }

    public function destroy(Wedding $wedding, WeddingEvent $event)
    {
        $this->authorize('update', $wedding);
        abort_if($event->wedding_id !== $wedding->id, 403);

        $event->delete();

        return back()->with('success', 'Acara berhasil dihapus.');
    }
}
