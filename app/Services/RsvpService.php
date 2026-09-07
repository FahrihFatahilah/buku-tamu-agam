<?php

namespace App\Services;

use App\Models\Guest;
use App\Models\Rsvp;
use App\Models\Wedding;

class RsvpService
{
    public function __construct(private AuditLogService $audit) {}

    public function upsert(Guest $guest, Wedding $wedding, array $data): Rsvp
    {
        $rsvp = $guest->rsvp()->updateOrCreate(
            ['guest_id' => $guest->id],
            array_merge($data, ['wedding_id' => $wedding->id])
        );

        $this->audit->log('rsvp.submitted', 'guest', $guest->id, [
            'status' => $data['attendance_status'],
            'pax'    => $data['pax'],
        ], $wedding->id);

        return $rsvp;
    }
}
