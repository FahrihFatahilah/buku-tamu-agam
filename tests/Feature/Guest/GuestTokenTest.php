<?php

namespace Tests\Feature\Guest;

use App\Models\Client;
use App\Models\Guest;
use App\Models\Template;
use App\Models\Wedding;
use App\Models\WeddingSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestTokenTest extends TestCase
{
    use RefreshDatabase;

    private Wedding $wedding;
    private Guest $guest;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'status' => 'active']);
        $template = Template::create(['key' => 'minang-elegance', 'name' => 'Minang', 'is_active' => true, 'sort_order' => 1]);

        $this->wedding = Wedding::create([
            'client_id' => $client->id,
            'template_id' => $template->id,
            'public_id' => 'INV-TOKN01',
            'slug' => 'test-wedding',
            'title' => 'Test',
            'groom_name' => 'Bagas',
            'bride_name' => 'Rani',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // WeddingService::bootstrapSections() always provisions sections on create.
        // The personalized greeting lives in the "opening" section, so seed it here
        // to mirror a real wedding and let the identity assertions render.
        WeddingSection::create([
            'wedding_id' => $this->wedding->id,
            'section_key' => 'opening',
            'is_enabled' => true,
            'sort_order' => 0,
        ]);

        $this->guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Budi Santoso',
            'max_pax' => 2,
        ]);
    }

    public function test_valid_token_shows_personalized_invitation(): void
    {
        $response = $this->get("/INV-TOKN01/test-wedding/u/{$this->guest->invitation_token}");
        $response->assertStatus(200);
    }

    public function test_invalid_token_returns_404(): void
    {
        $response = $this->get('/INV-TOKN01/test-wedding/u/invalidtoken123');
        $response->assertStatus(404);
    }

    /**
     * CRITICAL: Query parameter name manipulation must NOT change guest identity.
     * TOKEN_BUDI?name=Andi must still show Budi.
     */
    public function test_query_param_cannot_override_guest_identity(): void
    {
        $response = $this->get("/INV-TOKN01/test-wedding/u/{$this->guest->invitation_token}?name=Andi&guest_id=999");
        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        // Query param name should not appear as the guest name in the greeting
        $response->assertSee('Kepada Yth.');
        $response->assertDontSee('Kepada Yth.</p>\n            <p class="font-serif text-[#F5F0E8] text-lg">Andi');
    }

    public function test_token_from_wrong_wedding_returns_404(): void
    {
        $client = Client::first();
        $template = Template::first();

        $weddingB = Wedding::create([
            'client_id' => $client->id,
            'template_id' => $template->id,
            'public_id' => 'INV-TOKN02',
            'slug' => 'other-wedding',
            'title' => 'Other',
            'groom_name' => 'Doni',
            'bride_name' => 'Putri',
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Use Budi's token on Wedding B's URL
        $response = $this->get("/INV-TOKN02/other-wedding/u/{$this->guest->invitation_token}");
        $response->assertStatus(404);
    }

    public function test_regenerated_token_invalidates_old_token(): void
    {
        $oldToken = $this->guest->invitation_token;

        app(\App\Services\GuestTokenService::class)->regenerateToken($this->guest);

        $response = $this->get("/INV-TOKN01/test-wedding/u/{$oldToken}");
        $response->assertStatus(404);
    }

    public function test_rsvp_uses_token_identity_not_request_body(): void
    {
        $response = $this->post(
            "/INV-TOKN01/test-wedding/u/{$this->guest->invitation_token}/rsvp",
            [
                'attendance_status' => 'attending',
                'pax' => 1,
                'guest_id' => 9999, // Should be ignored
            ]
        );

        $response->assertRedirect();

        $rsvp = $this->guest->fresh()->rsvp;
        $this->assertNotNull($rsvp);
        $this->assertEquals($this->guest->id, $rsvp->guest_id);
    }
}
