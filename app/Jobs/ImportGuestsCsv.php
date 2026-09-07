<?php

namespace App\Jobs;

use App\Models\Wedding;
use App\Services\GuestService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ImportGuestsCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    public function __construct(
        public Wedding $wedding,
        public array $rows,
        public int $userId
    ) {}

    public function handle(GuestService $guestService): void
    {
        $guestService->importCsv($this->wedding, $this->rows);
    }
}
