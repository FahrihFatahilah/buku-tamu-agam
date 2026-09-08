<?php

namespace App\Http\Controllers\CheckIn;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Services\AuditLogService;
use App\Services\CheckInService;
use App\Services\GuestTokenService;
use Illuminate\Http\Request;

class CheckInController extends Controller
{
    public function __construct(
        private CheckInService $checkInService,
        private GuestTokenService $tokenService,
        private AuditLogService $audit,
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $weddings = $user->isSuperAdmin()
            ? Wedding::published()->with('client')->latest()->get()
            : Wedding::published()->forClient($user->client_id)->latest()->get();

        return view('checkin.index', compact('weddings'));
    }

    public function scanner(Request $request, Wedding $wedding)
    {
        $this->authorize('checkIn', $wedding);

        return view('checkin.scanner', compact('wedding'));
    }

    public function resolveToken(Request $request, Wedding $wedding, string $token)
    {
        $this->authorize('checkIn', $wedding);

        $guest = $this->tokenService->resolveGuest($token, $wedding);

        if (!$guest) {
            return response()->json(['error' => 'Token tidak valid.'], 404);
        }

        return response()->json([
            'guest' => [
                'id'            => $guest->id,
                'name'          => $guest->name,
                'category'      => $guest->category?->name,
                'max_pax'       => $guest->max_pax,
                'token'         => $token,
                'is_vip'        => strtolower($guest->category?->name ?? '') === 'vip' || $guest->guest_type === 'vip',
                'is_checked_in' => $guest->isCheckedIn(),
                'checkin'       => $guest->checkin ? [
                    'pax'           => $guest->checkin->pax,
                    'checked_in_at' => $guest->checkin->checked_in_at->format('H:i'),
                ] : null,
            ],
        ]);
    }

    public function confirm(Request $request, Wedding $wedding)
    {
        $this->authorize('checkIn', $wedding);

        $validated = $request->validate([
            'token' => 'required|string',
            'pax' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:255',
        ]);

        $guest = $this->tokenService->resolveGuest($validated['token'], $wedding);

        if (!$guest) {
            return response()->json(['error' => 'Token tidak valid.'], 404);
        }

        $checkin = $this->checkInService->checkIn(
            $guest,
            $validated['pax'],
            $request->user()->id,
            $validated['notes'] ?? null
        );

        $this->audit->log('checkin.confirmed', 'guest', $guest->id, [
            'pax' => $validated['pax'],
        ], $wedding->id);

        return response()->json([
            'success' => true,
            'guest_name' => $guest->name,
            'pax' => $checkin->pax,
            'checked_in_at' => $checkin->checked_in_at->format('H:i'),
        ]);
    }

    public function search(Request $request, Wedding $wedding)
    {
        $this->authorize('checkIn', $wedding);

        $query = $request->validate(['q' => 'required|string|min:2'])['q'];

        $guests = $this->checkInService->searchGuests($wedding, $query);

        return response()->json(['guests' => $guests->map(fn($g) => [
            'id'            => $g->id,
            'name'          => $g->name,
            'phone'         => $g->phone,
            'category'      => $g->category?->name,
            'max_pax'       => $g->max_pax,
            'is_checked_in' => $g->isCheckedIn(),
            'token'         => $g->short_token ?? $g->invitation_token,
        ])]);
    }
}
