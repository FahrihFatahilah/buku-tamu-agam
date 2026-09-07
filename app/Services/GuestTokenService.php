<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Wedding;

class GuestTokenService
{
    /**
     * Resolve guest from token (short or full), validating against the correct wedding.
     * Query parameters CANNOT override the resolved guest identity.
     */
    public function resolveGuest(string $token, Wedding $wedding): ?Guest
    {
        return Guest::where('wedding_id', $wedding->id)
            ->where(function ($q) use ($token) {
                $q->where('short_token', $token)
                  ->orWhere('invitation_token', $token);
            })
            ->with(['category', 'rsvp', 'checkin'])
            ->first();
    }

    /**
     * Regenerate both tokens, invalidating the old ones.
     */
    public function regenerateToken(Guest $guest): Guest
    {
        do {
            $short = Guest::generateShortToken();
        } while (Guest::where('short_token', $short)->where('id', '!=', $guest->id)->exists());

        $guest->update([
            'invitation_token'  => Guest::generateToken(),
            'short_token'       => $short,
            'token_generated_at' => now(),
        ]);

        return $guest->fresh();
    }
}
