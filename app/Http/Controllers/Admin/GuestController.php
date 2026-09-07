<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Wedding;
use App\Services\AuditLogService;
use App\Services\GuestService;
use App\Services\GuestTokenService;
use Illuminate\Http\Request;
use League\Csv\Reader;

class GuestController extends Controller
{
    public function __construct(
        private GuestService $guestService,
        private GuestTokenService $tokenService,
    ) {}

    public function index(Wedding $wedding, Request $request)
    {
        $this->authorize('manageGuests', $wedding);

        $guests = Guest::where('wedding_id', $wedding->id)
            ->with(['category', 'rsvp', 'checkin'])
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->latest()
            ->paginate(50);

        $categories = $wedding->guestCategories;

        return view('admin.guests.index', compact('wedding', 'guests', 'categories'));
    }

    public function store(Request $request, Wedding $wedding)
    {
        $this->authorize('manageGuests', $wedding);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'category_id' => 'nullable|exists:guest_categories,id',
            'max_pax' => 'required|integer|min:1|max:20',
            'notes' => 'nullable|string|max:500',
            'guest_type' => 'nullable|in:regular,vip',
        ]);

        // Ensure category belongs to this wedding
        if (!empty($validated['category_id'])) {
            $wedding->guestCategories()->findOrFail($validated['category_id']);
        }

        $guest = $this->guestService->create($wedding, $validated);

        return back()->with('success', "Tamu {$guest->name} berhasil ditambahkan.");
    }

    public function update(Request $request, Wedding $wedding, Guest $guest)
    {
        $this->authorize('manageGuests', $wedding);
        abort_if($guest->wedding_id !== $wedding->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'category_id' => 'nullable|exists:guest_categories,id',
            'max_pax' => 'required|integer|min:1|max:20',
            'notes' => 'nullable|string|max:500',
            'guest_type' => 'nullable|in:regular,vip',
        ]);

        $this->guestService->update($guest, $validated);

        return back()->with('success', 'Data tamu berhasil diperbarui.');
    }

    public function destroy(Wedding $wedding, Guest $guest)
    {
        $this->authorize('manageGuests', $wedding);
        abort_if($guest->wedding_id !== $wedding->id, 403);

        $this->guestService->delete($guest);

        return back()->with('success', 'Tamu berhasil dihapus.');
    }

    public function regenerateToken(Wedding $wedding, Guest $guest)
    {
        $this->authorize('manageGuests', $wedding);
        abort_if($guest->wedding_id !== $wedding->id, 403);

        $this->guestService->regenerateToken($guest, $this->tokenService);

        return back()->with('success', 'Token undangan berhasil diperbarui.');
    }

    public function importCsv(Request $request, Wedding $wedding)
    {
        $this->authorize('manageGuests', $wedding);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('file');
        $rows = array_map('str_getcsv', file($file->getRealPath()));
        $headers = array_map(fn($h) => strtolower(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h))), array_shift($rows));
        $data = array_filter(
            array_map(fn($row) => array_combine($headers, array_pad(array_map('trim', $row), count($headers), null)), $rows),
            fn($row) => !empty($row['name'])
        );

        // Large files go to queue; small files process sync
        if (count($data) > 500) {
            \App\Jobs\ImportGuestsCsv::dispatch($wedding, array_values($data), $request->user()->id);
            return back()->with('success', 'Import sedang diproses di background. Refresh halaman beberapa saat lagi.');
        }

        $result = $this->guestService->importCsv($wedding, array_values($data));

        return back()->with('import_result', $result);
    }
}
