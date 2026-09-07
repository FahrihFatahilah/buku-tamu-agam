<?php

namespace App\Providers;

use App\Services\AuditLogService;
use App\Services\CheckInService;
use App\Services\CloudflareService;
use App\Services\DomainResolver;
use App\Services\DomainService;
use App\Services\GuestService;
use App\Services\GuestTokenService;
use App\Services\GuestVisibilityService;
use App\Services\InvitationResolver;
use App\Services\MediaService;
use App\Services\TemplateService;
use App\Services\WeddingService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogService::class);
        $this->app->singleton(TemplateService::class);
        $this->app->singleton(CloudflareService::class);
        $this->app->singleton(DomainResolver::class);
        $this->app->singleton(InvitationResolver::class);
        $this->app->singleton(GuestTokenService::class);
        $this->app->singleton(GuestVisibilityService::class);
        $this->app->singleton(CheckInService::class);
        $this->app->singleton(MediaService::class);
        $this->app->singleton(GuestService::class);
        $this->app->singleton(WeddingService::class);
        $this->app->singleton(DomainService::class);
    }

    public function boot(): void
    {
        // Share templateService to all views
        \Illuminate\Support\Facades\View::share(
            'templateService',
            app(\App\Services\TemplateService::class)
        );
    }
}
