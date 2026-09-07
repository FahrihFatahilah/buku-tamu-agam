<?php

namespace App\Services;

use App\Models\Guest;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    /**
     * Generate SVG QR code for a guest's personal URL.
     */
    public function generate(Guest $guest, int $size = 200): string
    {
        return QrCode::format('svg')
            ->size($size)
            ->errorCorrection('M')
            ->generate($guest->personalUrl());
    }

    /**
     * Generate PNG QR code as binary string.
     */
    public function generatePng(Guest $guest, int $size = 300): string
    {
        return QrCode::format('png')
            ->size($size)
            ->errorCorrection('M')
            ->generate($guest->personalUrl());
    }
}
