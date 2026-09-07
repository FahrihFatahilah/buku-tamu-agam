<?php

namespace App\Services;

use App\Models\WeddingDomain;
use App\Models\Wedding;
use Illuminate\Support\Str;

class DomainService
{
    public function __construct(
        private AuditLogService $audit,
        private CloudflareService $cloudflare,
    ) {}

    public function addSubdomain(Wedding $wedding, string $subdomain): WeddingDomain
    {
        $platformDomain = config('ngundang.platform_domain', 'ngundang.com');
        $domain = "{$subdomain}.{$platformDomain}";

        $record = WeddingDomain::create([
            'wedding_id' => $wedding->id,
            'domain' => $domain,
            'type' => 'subdomain',
            'is_primary' => !$wedding->domains()->exists(),
            'is_active' => true,
            // Subdomains on wildcard DNS are auto-verified
            'verification_status' => 'verified',
            'verified_at' => now(),
            'ssl_status' => 'active',
        ]);

        $this->audit->log('domain.added', 'wedding_domain', $record->id, ['domain' => $domain], $wedding->id);

        return $record;
    }

    public function addCustomDomain(Wedding $wedding, string $domain): WeddingDomain
    {
        $token = Str::random(32);

        $record = WeddingDomain::create([
            'wedding_id' => $wedding->id,
            'domain' => $domain,
            'type' => 'custom',
            'is_primary' => false,
            'is_active' => false,
            'verification_status' => 'pending',
            'verification_token' => $token,
            'ssl_status' => 'pending',
        ]);

        $this->audit->log('domain.custom_added', 'wedding_domain', $record->id, ['domain' => $domain], $wedding->id);

        return $record;
    }

    public function verifyDomain(WeddingDomain $domain): bool
    {
        return $this->verifyCustomDomain($domain);
    }

    public function verifyCustomDomain(WeddingDomain $domain): bool
    {
        // Check DNS TXT record for verification token
        $records = @dns_get_record("_ngundang-verify.{$domain->domain}", DNS_TXT);

        if (!$records) {
            return false;
        }

        foreach ($records as $record) {
            if (isset($record['txt']) && $record['txt'] === $domain->verification_token) {
                $domain->update([
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                    'is_active' => true,
                ]);

                $this->audit->log('domain.verified', 'wedding_domain', $domain->id, [], $domain->wedding_id);

                return true;
            }
        }

        $domain->update(['verification_status' => 'failed']);
        return false;
    }
}
