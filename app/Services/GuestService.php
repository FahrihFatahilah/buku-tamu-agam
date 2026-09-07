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
        $categoriesBefore = $wedding->guestCategories()->count();

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
                    'wedding_id'  => $wedding->id,
                    'name'        => trim($row['name']),
                    'phone'       => $row['phone'] ?? null,
                    'email'       => $row['email'] ?? null,
                    'max_pax'     => max(1, (int) ($row['max_pax'] ?? 1)),
                    'notes'       => $row['notes'] ?? null,
                    'category_id' => $this->resolveCategoryId($wedding, $row['category_id'] ?? null),
                    'guest_type'  => in_array($row['guest_type'] ?? '', ['vip', 'regular']) ? $row['guest_type'] : 'regular',
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

        $categoriesCreated = $wedding->guestCategories()->count() - $categoriesBefore;

        return compact('imported', 'failed', 'duplicate', 'categoriesCreated');
    }

    private function resolveCategoryId(Wedding $wedding, mixed $value): ?int
    {
        if (empty($value)) return null;

        // Numeric ID — harus sudah ada
        if (is_numeric($value)) {
            return $wedding->guestCategories()->where('id', (int) $value)->value('id');
        }

        // Category name — buat otomatis jika belum ada
        return $wedding->guestCategories()->firstOrCreate(
            ['name' => trim($value)],
            ['sort_order' => $wedding->guestCategories()->max('sort_order') + 1]
        )->id;
    }
}
