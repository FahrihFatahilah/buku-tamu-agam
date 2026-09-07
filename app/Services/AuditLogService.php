<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public function log(
        string $action,
        ?string $entityType = null,
        ?int $entityId = null,
        array $metadata = [],
        ?int $weddingId = null
    ): void {
        $request = app(Request::class);

        AuditLog::create([
            'user_id' => Auth::id(),
            'wedding_id' => $weddingId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'metadata' => empty($metadata) ? null : $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => now(),
        ]);
    }
}
