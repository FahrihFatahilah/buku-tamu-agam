<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Services\GuestBookService;
use App\Services\GuestTokenService;
use Illuminate\Http\Request;

class GuestBookController extends Controller
{
    public function __construct(
        private GuestBookService $guestBookService,
        private GuestTokenService $tokenService,
    ) {}

    /**
     * Handles both /INV-XXXXXX/slug/guestbook and /xxxxxx/slug/guestbook
     */
    public function store(Request $request, string $id, string $slug)
    {
        // Resolve wedding by public_id (INV-...) or short_id (6 lowercase)
        $wedding = preg_match('/^INV-[A-Z0-9]{6}$/', $id)
            ? Wedding::where('public_id', $id)->where('status', 'published')->firstOrFail()
            : Wedding::where('short_id', $id)->where('status', 'published')->firstOrFail();

        $validated = $request->validate([
            'name'              => 'required|string|max:100',
            'message'           => 'required|string|max:1000',
            'attendance_status' => 'nullable|in:attending,not_attending,maybe',
            'pax'               => 'nullable|integer|min:1|max:20',
        ]);

        $guest = null;
        $token = $request->query('t');
        if ($token) {
            $guest = $this->tokenService->resolveGuest($token, $wedding);
        }

        $this->guestBookService->submit($wedding, $validated, $guest, $request->ip());

        // AJAX request — return JSON so page doesn't reload
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Pesan berhasil dikirim.']);
        }

        return back()->with('success', 'Pesan berhasil dikirim.');
    }
}
