<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\Wedding;
use App\Policies\ClientPolicy;
use App\Policies\WeddingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Wedding::class => WeddingPolicy::class,
        Client::class => ClientPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
