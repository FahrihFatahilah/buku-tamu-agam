<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Wedding;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GuestService
{
    public function __construct(private AuditLogService $audit) {}

    public function create(Wedding $wedding, array $data): Guest
    {
        $guest = Guest::create(array_merge($data, ['wedding_id' => $wedding->id]));

        $this->audit->log('guest.created', 'guest', $guest->id, ['name' => $guest->name], $wedding->id);

        return $guest;
    }

    public function update(Guest $guest, array $data): Guest
    {
        $guest->update($data);
        $this->audit->log('guest.updated', 'guest', $guest->id, [], $guest->wedding_id);
        return $guest;
    }

    public function delete(Guest $guest): void
    {
        $this->audit->log('guest.deleted', 'guest', $guest->id, ['name' => $guest->name], $guest->wedding_id);
        $guest->delete();
    }

    public function regenerateToken(Guest $guest, GuestTokenService $tokenService): Guest
    {
        $guest = $tokenService->regenerateToken($guest);
        $this->audit->log('guest.token_regenerated', 'guest', $guest->id, [], $guest->wedding_id);
        return $guest;
    }

    /**
     * Import guests from CSV. Returns ['imported', 'failed', 'duplicate'] counts.
     */
    public function importCsv(Wedding $wedding, array $rows): array
    {
        $imported = 0;
        $failed = 0;
        $duplicate = 0;

        foreach ($rows as $row) {
            if (empty($row['name'])) {
                $failed++;
                continue;
            }

            // Check duplicate by name+phone within wedding
            $exists = Guest::where('wedding_id', $wedding->id)
                ->where('name', $row['name'])
                ->when(!empty($row['phone']), fn($q) => $q->where('phone', $row['phone']))
                ->exists();

            if ($exists) {
                $duplicate++;
                continue;
            }

            try {
                Guest::create([
                    'wedding_id' => $wedding->id,
                    'name' => $row['name'],
                    'phone' => $row['phone'] ?? null,
                    'email' => $row['email'] ?? null,
                    'max_pax' => (int) ($row['max_pax'] ?? 1),
                ]);
                $imported++;
            } catch (\Exception) {
                $failed++;
            }
        }

        $this->audit->log('guest.csv_imported', 'wedding', $wedding->id, [
            'imported' => $imported,
            'failed' => $failed,
            'duplicate' => $duplicate,
        ], $wedding->id);

        return compact('imported', 'failed', 'duplicate');
    }
}
