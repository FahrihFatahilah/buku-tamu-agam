<?php

namespace App\Services;

use App\Models\GuestBookEntry;
use App\Models\Guest;
use App\Models\Wedding;

class GuestBookService
{
    public function __construct(private AuditLogService $audit) {}

    public function submit(Wedding $wedding, array $data, ?Guest $guest, string $ip): GuestBookEntry
    {
        $moderation = $wedding->settings['guestbook_moderation'] ?? true;

        $entry = GuestBookEntry::create([
            'wedding_id'        => $wedding->id,
            'guest_id'          => $guest?->id,
            'name'              => $data['name'],
            'message'           => $data['message'],
            'attendance_status' => $data['attendance_status'] ?? null,
            'pax'               => $data['pax'] ?? null,
            'status'            => $moderation ? 'pending' : 'approved',
            'ip_address'        => $ip,
        ]);

        $this->audit->log('guestbook.submitted', 'guest_book_entry', $entry->id, [
            'name' => $data['name'],
        ], $wedding->id);

        return $entry;
    }
}
