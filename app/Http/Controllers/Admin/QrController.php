<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Wedding;
use App\Services\QrCodeService;

class QrController extends Controller
{
    public function __construct(private QrCodeService $qrService) {}

    public function show(Wedding $wedding, Guest $guest)
    {
        $this->authorize('viewGuests', $wedding);
        abort_if($guest->wedding_id !== $wedding->id, 403);

        $svg = $this->qrService->generate($guest);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'inline',
        ]);
    }

    public function download(Wedding $wedding, Guest $guest)
    {
        $this->authorize('viewGuests', $wedding);
        abort_if($guest->wedding_id !== $wedding->id, 403);

        $png = $this->qrService->generatePng($guest);
        $filename = 'qr-'.str($guest->name)->slug().'.png';

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
