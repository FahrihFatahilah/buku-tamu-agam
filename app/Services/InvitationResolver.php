<?php

namespace App\Services;

use App\Models\Wedding;
use App\Models\WeddingSlugHistory;
use Illuminate\Http\RedirectResponse;

class InvitationResolver
{
    public function __construct(private DomainResolver $domainResolver) {}

    /**
     * Resolve by public_id (INV-XXXXXX) — legacy long URL.
     */
    public function resolve(string $host, string $publicId, string $slug): array
    {
        if (!$this->domainResolver->isPlatformDomain($host)) {
            $domainWedding = $this->domainResolver->resolveFromHost($host);
            if (!$domainWedding) {
                return ['wedding' => null, 'redirect' => null, 'error' => 'domain_not_found'];
            }
            if (!$this->domainResolver->validateInvitationOnDomain($domainWedding, $publicId)) {
                return ['wedding' => null, 'redirect' => null, 'error' => 'domain_mismatch'];
            }
        }

        $wedding = Wedding::where('public_id', $publicId)->with(['template', 'domains'])->first();
        if (!$wedding) {
            return ['wedding' => null, 'redirect' => null, 'error' => 'not_found'];
        }

        return $this->validateSlugAndStatus($wedding, $slug, $publicId);
    }

    /**
     * Resolve by short_id (6 char) — short URL.
     * Slug MUST match exactly — asal tulis slug → 404.
     * Old slugs redirect to current slug.
     */
    public function resolveByShortId(string $host, string $shortId, string $slug): array
    {
        $wedding = Wedding::where('short_id', $shortId)->with(['template', 'domains'])->first();

        if (!$wedding) {
            return ['wedding' => null, 'redirect' => null, 'error' => 'not_found'];
        }

        // Domain validation same as long URL
        if (!$this->domainResolver->isPlatformDomain($host)) {
            $domainWedding = $this->domainResolver->resolveFromHost($host);

            if (!$domainWedding) {
                return ['wedding' => null, 'redirect' => null, 'error' => 'domain_not_found'];
            }

            if ($domainWedding->id !== $wedding->id) {
                return ['wedding' => null, 'redirect' => null, 'error' => 'domain_mismatch'];
            }
        }

        return $this->validateSlugAndStatus($wedding, $slug, $shortId);
    }

    /**
     * Shared slug validation + published check.
     * Slug wrong → 404. Old slug → 301 redirect. Unpublished → 404 (unless preview).
     */
    private function validateSlugAndStatus(Wedding $wedding, string $slug, string $id): array
    {
        if ($wedding->slug !== $slug) {
            // Check slug history for canonical redirect
            $history = WeddingSlugHistory::where('wedding_id', $wedding->id)
                ->where('slug', $slug)->exists();

            if ($history) {
                return ['wedding' => $wedding, 'redirect' => "/{$id}/{$wedding->slug}", 'error' => null];
            }

            // Slug doesn't match and not in history → 404
            return ['wedding' => null, 'redirect' => null, 'error' => 'slug_mismatch'];
        }

        if (!$wedding->isPublished()) {
            $previewKey = "preview_wedding_{$wedding->id}";
            if (session($previewKey) && auth()->check() && auth()->user()->canManageWedding($wedding)) {
                return ['wedding' => $wedding, 'redirect' => null, 'error' => null];
            }
            return ['wedding' => null, 'redirect' => null, 'error' => 'not_published'];
        }

        return ['wedding' => $wedding, 'redirect' => null, 'error' => null];
    }
}
