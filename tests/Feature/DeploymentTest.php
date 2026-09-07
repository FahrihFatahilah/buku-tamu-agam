<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Deployment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeploymentTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = Client::create(['name' => 'T', 'email' => 't@t.com', 'status' => 'active']);
    }

    public function test_deployment_starts_as_pending(): void
    {
        $d = Deployment::create([
            'client_id' => $this->client->id,
            'type' => 'dedicated',
            'status' => 'pending',
            'config' => [],
        ]);

        $this->assertEquals('pending', $d->status);
    }

    public function test_valid_transitions(): void
    {
        $d = Deployment::create([
            'client_id' => $this->client->id,
            'type' => 'dedicated', 'status' => 'pending', 'config' => [],
        ]);

        $this->assertTrue($d->canTransitionTo('provisioning'));
        $d->update(['status' => 'provisioning']);

        $this->assertTrue($d->canTransitionTo('deploying'));
        $d->update(['status' => 'deploying']);

        $this->assertTrue($d->canTransitionTo('healthy'));
        $d->update(['status' => 'healthy']);

        $this->assertTrue($d->canTransitionTo('suspended'));
    }

    public function test_invalid_transition_is_rejected(): void
    {
        $d = Deployment::create([
            'client_id' => $this->client->id,
            'type' => 'dedicated', 'status' => 'pending', 'config' => [],
        ]);

        $this->assertFalse($d->canTransitionTo('healthy'));
        $this->assertFalse($d->canTransitionTo('terminated'));
    }

    public function test_failed_can_retry_to_provisioning(): void
    {
        $d = Deployment::create([
            'client_id' => $this->client->id,
            'type' => 'dedicated', 'status' => 'failed', 'config' => [],
        ]);

        $this->assertTrue($d->canTransitionTo('provisioning'));
    }

    public function test_terminated_cannot_transition(): void
    {
        $d = Deployment::create([
            'client_id' => $this->client->id,
            'type' => 'dedicated', 'status' => 'terminated', 'config' => [],
        ]);

        foreach (['pending', 'provisioning', 'deploying', 'healthy', 'suspended'] as $state) {
            $this->assertFalse($d->canTransitionTo($state));
        }
    }
}
