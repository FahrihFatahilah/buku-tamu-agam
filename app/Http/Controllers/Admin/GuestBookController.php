<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuestBookEntry;
use App\Models\Wedding;
use Illuminate\Http\Request;

class GuestBookController extends Controller
{
    public function index(Wedding $wedding, Request $request)
    {
        $this->authorize('update', $wedding);

        $entries = GuestBookEntry::where('wedding_id', $wedding->id)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->guest_type, fn($q) => $q->whereHas('guest', fn($g) => $g->where('guest_type', $request->guest_type)))
            ->with('guest')
            ->latest()
            ->paginate(30);

        return view('admin.guestbook.index', compact('wedding', 'entries'));
    }

    public function moderate(Request $request, Wedding $wedding, GuestBookEntry $entry)
    {
        $this->authorize('update', $wedding);
        abort_if($entry->wedding_id !== $wedding->id, 403);

        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,hidden',
        ]);

        $entry->update($validated);

        return back()->with('success', 'Status pesan berhasil diperbarui.');
    }

    public function bulkModerate(Request $request, Wedding $wedding)
    {
        $this->authorize('update', $wedding);

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
            'status' => 'required|in:approved,rejected,hidden',
        ]);

        GuestBookEntry::where('wedding_id', $wedding->id)
            ->whereIn('id', $validated['ids'])
            ->update(['status' => $validated['status']]);

        return back()->with('success', 'Pesan berhasil dimoderasi.');
    }

    public function destroy(Wedding $wedding, GuestBookEntry $entry)
    {
        $this->authorize('update', $wedding);
        abort_if($entry->wedding_id !== $wedding->id, 403);

        $entry->delete();

        return back()->with('success', 'Pesan berhasil dihapus.');
    }
}
