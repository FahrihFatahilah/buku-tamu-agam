<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Wedding;

class InvitationService
{
    public function __construct(
        private InvitationResolver $resolver,
        private GuestTokenService $tokenService,
        private GuestVisibilityService $visibility,
    ) {}

    /**
     * Resolve a public invitation (no guest context).
     */
    public function resolvePublic(string $host, string $publicId, string $slug): array
    {
        return $this->resolver->resolve($host, $publicId, $slug);
    }

    public function resolvePublicShort(string $host, string $shortId, string $slug): array
    {
        return $this->resolver->resolveByShortId($host, $shortId, $slug);
    }

    public function resolvePersonalized(string $host, string $publicId, string $slug, string $token): array
    {
        $result = $this->resolver->resolve($host, $publicId, $slug);
        return $this->applyGuestContext($result, $token);
    }

    public function resolvePersonalizedShort(string $host, string $shortId, string $slug, string $token): array
    {
        $result = $this->resolver->resolveByShortId($host, $shortId, $slug);
        return $this->applyGuestContext($result, $token);
    }

    private function applyGuestContext(array $result, string $token): array
    {
        if ($result['error'] || $result['redirect']) {
            return $result;
        }

        $wedding = $result['wedding'];
        $guest   = $this->tokenService->resolveGuest($token, $wedding);

        if (!$guest) {
            return array_merge($result, ['error' => 'invalid_token', 'guest' => null]);
        }

        if ($guest->status === 'sent') {
            $guest->update(['status' => 'opened']);
        }

        $visibleEvents = $this->visibility->getVisibleEntities('event', $wedding, $guest);
        $visibleGifts  = $this->visibility->getVisibleEntities('gift_method', $wedding, $guest);

        return array_merge($result, [
            'guest'          => $guest,
            'visible_gifts'  => $visibleGifts->isNotEmpty() ? $visibleGifts : null,
            'visible_events' => $visibleEvents->isNotEmpty() ? $visibleEvents : null,
        ]);
    }
}
