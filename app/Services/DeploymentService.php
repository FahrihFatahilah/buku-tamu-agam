<?php

namespace App\Services;

use App\Jobs\ProvisionDeployment;
use App\Models\Client;
use App\Models\Deployment;

class DeploymentService
{
    public function __construct(private AuditLogService $audit) {}

    public function create(Client $client, string $type, array $config = []): Deployment
    {
        $deployment = Deployment::create([
            'client_id' => $client->id,
            'type'      => $type,
            'status'    => 'pending',
            'config'    => $config,
        ]);

        $this->audit->log('deployment.created', 'deployment', $deployment->id, [
            'type' => $type,
        ]);

        return $deployment;
    }

    public function provision(Deployment $deployment): void
    {
        if (!$deployment->canTransitionTo('provisioning')) {
            throw new \RuntimeException("Cannot transition from {$deployment->status} to provisioning.");
        }

        $deployment->update(['status' => 'provisioning']);

        $this->audit->log('deployment.provisioning', 'deployment', $deployment->id, []);

        // Dispatch to queue — never run arbitrary commands from browser
        ProvisionDeployment::dispatch($deployment);
    }

    public function transition(Deployment $deployment, string $newStatus, ?string $error = null): void
    {
        if (!$deployment->canTransitionTo($newStatus)) {
            throw new \RuntimeException("Cannot transition from {$deployment->status} to {$newStatus}.");
        }

        $update = ['status' => $newStatus];
        if ($error) {
            $update['last_error'] = $error;
        }
        if ($newStatus === 'healthy') {
            $update['deployed_at'] = now();
        }

        $deployment->update($update);

        $this->audit->log("deployment.{$newStatus}", 'deployment', $deployment->id, [
            'error' => $error,
        ]);
    }
}
