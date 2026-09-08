<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Wedding;
use App\Services\GuestTokenService;
use App\Services\QrCodeService;
use App\Services\RsvpService;
use Illuminate\Http\Request;

class RsvpController extends Controller
{
    public function __construct(
        private GuestTokenService $tokenService,
        private RsvpService $rsvpService,
        private QrCodeService $qrCodeService,
    ) {}

    public function store(Request $request, string $publicId, string $slug, string $token)
    {
        $wedding = Wedding::where('public_id', $publicId)->where('status', 'published')->firstOrFail();
        return $this->process($request, $wedding, $token);
    }

    public function storeShort(Request $request, string $shortId, string $slug, string $token)
    {
        $wedding = Wedding::where('short_id', $shortId)->where('status', 'published')->firstOrFail();
        return $this->process($request, $wedding, $token);
    }

    public function qr(Request $request, string $anyId, string $slug, string $token)
    {
        $wedding = Wedding::where('short_id', $anyId)
            ->orWhere('public_id', $anyId)
            ->where('status', 'published')
            ->firstOrFail();

        $guest = $this->tokenService->resolveGuest($token, $wedding);
        if (!$guest) abort(404);

        $svg = preg_replace('/^<\?xml[^?]*\?>\s*/i', '', $this->qrCodeService->generate($guest, 220));

        return response($svg, 200)->header('Content-Type', 'image/svg+xml');
    }

    private function process(Request $request, Wedding $wedding, string $token)
    {
        $guest = $this->tokenService->resolveGuest($token, $wedding);
        if (!$guest) abort(404);

        $validated = $request->validate([
            'attendance_status' => 'required|in:attending,not_attending,maybe',
            'pax'               => 'required|integer|min:1|max:' . $guest->max_pax,
            'note'              => 'nullable|string|max:500',
        ]);

        $this->rsvpService->upsert($guest, $wedding, array_merge($validated, [
            'ip_address' => $request->ip(),
        ]));

        if ($request->expectsJson()) {
            $qrUrl = url(request()->path() . '/../qr');
            return response()->json([
                'success'   => true,
                'attending' => $validated['attendance_status'] === 'attending',
                'qr_url'    => route('rsvp.qr', [
                    'anyId' => $wedding->short_id ?? $wedding->public_id,
                    'slug'  => $wedding->slug,
                    'token' => $token,
                ]),
            ]);
        }

        return back()->with('success', 'RSVP berhasil disimpan.');
    }
}
