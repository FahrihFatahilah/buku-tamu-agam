<?php

namespace App\Jobs;

use App\Models\Deployment;
use App\Services\DeploymentService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProvisionDeployment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 600;
    public int $backoff = 60;

    public function __construct(public Deployment $deployment) {}

    public function handle(DeploymentService $service): void
    {
        try {
            $service->transition($this->deployment, 'deploying');

            // Actual provisioning logic goes here.
            // For shared deployment: no-op (already running on shared infra).
            // For dedicated: would call infrastructure API, Docker API, etc.
            // This is intentionally a stub — real implementation depends on infra.
            $this->provisionByType();

            $service->transition($this->deployment, 'healthy');
        } catch (\Throwable $e) {
            Log::error('Deployment failed', [
                'deployment_id' => $this->deployment->id,
                'error'         => $e->getMessage(),
            ]);

            $service->transition($this->deployment, 'failed', $e->getMessage());
        }
    }

    private function provisionByType(): void
    {
        match ($this->deployment->type) {
            'shared'    => null, // No provisioning needed for shared
            'dedicated' => $this->provisionDedicated(),
            default     => null,
        };
    }

    private function provisionDedicated(): void
    {
        // Stub: real implementation would call Docker/K8s/cloud API
        // Never expose Docker socket directly to web process
        // Config comes from $this->deployment->config (not user input)
        sleep(1); // Simulate async work
    }

    public function failed(\Throwable $e): void
    {
        $this->deployment->update([
            'status'     => 'failed',
            'last_error' => $e->getMessage(),
        ]);
    }
}
