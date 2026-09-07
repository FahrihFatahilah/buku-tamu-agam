<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CloudflareService
{
    private string $apiToken;
    private string $zoneId;

    public function __construct()
    {
        $this->apiToken = config('services.cloudflare.api_token', '');
        $this->zoneId = config('services.cloudflare.zone_id', '');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiToken) && !empty($this->zoneId);
    }

    /**
     * Purge cache for specific URLs.
     * Only called server-side, credentials never exposed to browser.
     */
    public function purgeCache(array $urls): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->post("https://api.cloudflare.com/client/v4/zones/{$this->zoneId}/purge_cache", [
                    'files' => $urls,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Cloudflare cache purge failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Verify domain ownership via Cloudflare API.
     * Used for custom domain SSL provisioning.
     */
    public function addCustomHostname(string $hostname): ?string
    {
        if (!$this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withToken($this->apiToken)
                ->post("https://api.cloudflare.com/client/v4/zones/{$this->zoneId}/custom_hostnames", [
                    'hostname' => $hostname,
                    'ssl' => ['method' => 'http', 'type' => 'dv'],
                ]);

            if ($response->successful()) {
                return $response->json('result.id');
            }
        } catch (\Exception $e) {
            Log::error('Cloudflare custom hostname failed', ['error' => $e->getMessage()]);
        }

        return null;
    }
}
