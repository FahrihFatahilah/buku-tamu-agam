<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Deployment extends Model
{
    protected $fillable = [
        'client_id', 'type', 'status', 'host', 'config',
        'last_error', 'provisioned_at', 'deployed_at',
    ];

    protected $casts = [
        'config' => 'array',
        'provisioned_at' => 'datetime',
        'deployed_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function isHealthy(): bool
    {
        return $this->status === 'healthy';
    }

    public function canTransitionTo(string $state): bool
    {
        $transitions = [
            'pending'      => ['provisioning'],
            'provisioning' => ['deploying', 'failed'],
            'deploying'    => ['healthy', 'failed'],
            'healthy'      => ['suspended', 'terminated'],
            'failed'       => ['provisioning', 'terminated'],
            'suspended'    => ['deploying', 'terminated'],
            'terminated'   => [],
        ];

        return in_array($state, $transitions[$this->status] ?? []);
    }
}
