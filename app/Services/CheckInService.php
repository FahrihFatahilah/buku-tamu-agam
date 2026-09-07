<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\GuestCheckin;
use App\Models\Wedding;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    public function checkIn(Guest $guest, int $pax, ?int $operatorId = null, ?string $notes = null): GuestCheckin
    {
        return DB::transaction(function () use ($guest, $pax, $operatorId, $notes) {
            // Use updateOrCreate with a lock to prevent race conditions
            $checkin = GuestCheckin::lockForUpdate()
                ->where('guest_id', $guest->id)
                ->first();

            if ($checkin) {
                $checkin->update([
                    'pax' => $pax,
                    'checked_in_by' => $operatorId,
                    'notes' => $notes,
                    'checked_in_at' => now(),
                ]);
                return $checkin;
            }

            return GuestCheckin::create([
                'wedding_id' => $guest->wedding_id,
                'guest_id' => $guest->id,
                'checked_in_at' => now(),
                'pax' => $pax,
                'checked_in_by' => $operatorId,
                'notes' => $notes,
            ]);
        });
    }

    public function searchGuests(Wedding $wedding, string $query): \Illuminate\Database\Eloquent\Collection
    {
        return Guest::where('wedding_id', $wedding->id)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('phone', 'like', "%{$query}%");
            })
            ->with(['category', 'checkin', 'rsvp'])
            ->limit(20)
            ->get();
    }
}
