<?php

namespace Tests\Feature\CheckIn;

use App\Models\Client;
use App\Models\Guest;
use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckInTest extends TestCase
{
    use RefreshDatabase;

    private Wedding $wedding;
    private Guest $guest;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'status' => 'active']);
        $template = Template::create(['key' => 'minang-elegance', 'name' => 'Minang', 'is_active' => true, 'sort_order' => 1]);

        $this->wedding = Wedding::create([
            'client_id' => $client->id,
            'template_id' => $template->id,
            'public_id' => 'INV-CHKN01',
            'slug' => 'checkin-test',
            'title' => 'Test',
            'groom_name' => 'A',
            'bride_name' => 'B',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'name' => 'Budi',
            'max_pax' => 3,
        ]);

        $this->operator = User::create([
            'name' => 'Operator',
            'email' => 'op@test.com',
            'password' => bcrypt('password'),
            'client_id' => $client->id,
            'role' => 'checkin_operator',
            'is_active' => true,
        ]);
    }

    public function test_valid_token_resolves_guest(): void
    {
        $response = $this->actingAs($this->operator)
            ->getJson("/check-in/{$this->wedding->id}/token/{$this->guest->invitation_token}");

        $response->assertOk()
            ->assertJsonPath('guest.name', 'Budi')
            ->assertJsonPath('guest.is_checked_in', false);
    }

    public function test_invalid_token_returns_404(): void
    {
        $response = $this->actingAs($this->operator)
            ->getJson("/check-in/{$this->wedding->id}/token/invalidtoken");

        $response->assertNotFound();
    }

    public function test_checkin_creates_record(): void
    {
        $response = $this->actingAs($this->operator)
            ->postJson("/check-in/{$this->wedding->id}/confirm", [
                'token' => $this->guest->invitation_token,
                'pax' => 2,
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('guest_checkins', [
            'guest_id' => $this->guest->id,
            'pax' => 2,
        ]);
    }

    public function test_double_checkin_updates_existing_record(): void
    {
        $service = app(\App\Services\CheckInService::class);
        $service->checkIn($this->guest, 1, $this->operator->id);

        // Second check-in
        $response = $this->actingAs($this->operator)
            ->postJson("/check-in/{$this->wedding->id}/confirm", [
                'token' => $this->guest->invitation_token,
                'pax' => 3,
            ]);

        $response->assertOk();

        // Should update, not create duplicate
        $this->assertDatabaseCount('guest_checkins', 1);
        $this->assertDatabaseHas('guest_checkins', ['guest_id' => $this->guest->id, 'pax' => 3]);
    }

    public function test_checkin_operator_cannot_access_other_wedding(): void
    {
        $otherClient = Client::create(['name' => 'Other', 'email' => 'other@test.com', 'status' => 'active']);
        $otherWedding = Wedding::create([
            'client_id' => $otherClient->id,
            'template_id' => Template::first()->id,
            'public_id' => 'INV-CHKN02',
            'slug' => 'other',
            'title' => 'Other',
            'groom_name' => 'X',
            'bride_name' => 'Y',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->actingAs($this->operator)
            ->getJson("/check-in/{$otherWedding->id}/token/{$this->guest->invitation_token}");

        $response->assertForbidden();
    }
}
