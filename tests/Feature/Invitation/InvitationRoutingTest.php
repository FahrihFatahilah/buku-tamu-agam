<?php

namespace Tests\Feature\Invitation;

use App\Models\Client;
use App\Models\Guest;
use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingDomain;
use App\Models\WeddingSlugHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvitationRoutingTest extends TestCase
{
    use RefreshDatabase;

    private Wedding $wedding;
    private Template $template;
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = Template::create([
            'key' => 'minang-elegance',
            'name' => 'Minang Elegance',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->client = Client::create([
            'name' => 'Test Client',
            'email' => 'test@client.com',
            'status' => 'active',
        ]);

        $this->wedding = Wedding::create([
            'client_id' => $this->client->id,
            'template_id' => $this->template->id,
            'public_id' => 'INV-TEST01',
            'slug' => 'andi-sari',
            'title' => 'Test Wedding',
            'groom_name' => 'Andi',
            'bride_name' => 'Sari',
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function test_valid_invitation_url_returns_200(): void
    {
        $response = $this->get('/INV-TEST01/andi-sari');
        $response->assertStatus(200);
    }

    public function test_invalid_public_id_returns_404(): void
    {
        $response = $this->get('/INV-XXXXX/andi-sari');
        $response->assertStatus(404);
    }

    public function test_wrong_slug_returns_404(): void
    {
        $response = $this->get('/INV-TEST01/wrong-slug');
        $response->assertStatus(404);
    }

    public function test_old_slug_redirects_to_current_slug(): void
    {
        // Record old slug in history
        WeddingSlugHistory::create([
            'wedding_id' => $this->wedding->id,
            'slug' => 'andi-sari-old',
            'retired_at' => now(),
        ]);

        $response = $this->get('/INV-TEST01/andi-sari-old');
        $response->assertRedirect('/INV-TEST01/andi-sari');
        $response->assertStatus(301);
    }

    public function test_unpublished_invitation_returns_404(): void
    {
        $this->wedding->update(['status' => 'draft']);

        $response = $this->get('/INV-TEST01/andi-sari');
        $response->assertStatus(404);
    }

    public function test_archived_invitation_returns_404(): void
    {
        $this->wedding->update(['status' => 'archived']);

        $response = $this->get('/INV-TEST01/andi-sari');
        $response->assertStatus(404);
    }

    public function test_domain_mismatch_returns_404(): void
    {
        // Wedding B
        $weddingB = Wedding::create([
            'client_id' => $this->client->id,
            'template_id' => $this->template->id,
            'public_id' => 'INV-TEST02',
            'slug' => 'budi-rina',
            'title' => 'Wedding B',
            'groom_name' => 'Budi',
            'bride_name' => 'Rina',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Domain mapped to Wedding A
        WeddingDomain::create([
            'wedding_id' => $this->wedding->id,
            'domain' => 'andi-sari.ngundang.test',
            'type' => 'subdomain',
            'is_primary' => true,
            'is_active' => true,
            'verification_status' => 'verified',
            'verified_at' => now(),
            'ssl_status' => 'active',
        ]);

        // Test domain mismatch via service directly (host resolution in tests is unreliable)
        $resolver = app(\App\Services\InvitationResolver::class);
        $result = $resolver->resolve('andi-sari.ngundang.test', 'INV-TEST02', 'budi-rina');

        $this->assertEquals('domain_mismatch', $result['error']);
        $this->assertNull($result['wedding']);
    }
}
