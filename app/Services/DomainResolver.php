<?php

namespace App\Services;

use App\Models\Wedding;
use App\Models\WeddingDomain;
use Illuminate\Support\Facades\Cache;

class DomainResolver
{
    private const CACHE_TTL = 300; // 5 minutes

    public function resolveFromHost(string $host): ?Wedding
    {
        $cacheKey = "domain_mapping:{$host}";

        $weddingId = Cache::remember($cacheKey, self::CACHE_TTL, function () use ($host) {
            $domain = WeddingDomain::where('domain', $host)
                ->where('is_active', true)
                ->where('verification_status', 'verified')
                ->first();

            return $domain?->wedding_id;
        });

        if (!$weddingId) {
            return null;
        }

        return Wedding::find($weddingId);
    }

    public function validateInvitationOnDomain(Wedding $domainWedding, string $publicId): bool
    {
        return $domainWedding->public_id === $publicId;
    }

    public function isPlatformDomain(string $host): bool
    {
        $platformDomain = config('ngundang.platform_domain', 'ngundang.com');

        if (in_array($host, ['localhost', '127.0.0.1'])) {
            return true;
        }

        return $host === $platformDomain || str_ends_with($host, '.' . $platformDomain);
    }

    public function forgetCache(string $domain): void
    {
        Cache::forget("domain_mapping:{$domain}");
    }
}
