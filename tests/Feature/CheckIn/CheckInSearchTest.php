<?php

namespace Tests\Feature\CheckIn;

use App\Models\Client;
use App\Models\Guest;
use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckInSearchTest extends TestCase
{
    use RefreshDatabase;

    private Wedding $wedding;
    private User $operator;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::create(['name' => 'T', 'email' => 't@t.com', 'status' => 'active']);
        $template = Template::create(['key' => 'test', 'name' => 'Test', 'is_active' => true, 'sort_order' => 1]);

        $this->wedding = Wedding::create([
            'client_id' => $client->id, 'template_id' => $template->id,
            'public_id' => 'INV-SRCH01', 'slug' => 'search-test',
            'title' => 'Test', 'groom_name' => 'A', 'bride_name' => 'B',
            'status' => 'published', 'published_at' => now(),
        ]);

        $this->operator = User::create([
            'name' => 'Op', 'email' => 'op@test.com', 'password' => bcrypt('pass'),
            'client_id' => $client->id, 'role' => 'checkin_operator', 'is_active' => true,
        ]);
    }

    public function test_search_returns_multiple_matches(): void
    {
        Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Ahmad Budi', 'max_pax' => 1]);
        Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Ahmad Rudi', 'max_pax' => 2]);
        Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Siti Rahayu', 'max_pax' => 1]);

        $response = $this->actingAs($this->operator)
            ->getJson("/check-in/{$this->wedding->id}/search?q=Ahmad");

        $response->assertOk();
        $this->assertCount(2, $response->json('guests'));
    }

    public function test_search_does_not_auto_select_on_multiple_matches(): void
    {
        Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Budi Santoso', 'max_pax' => 1]);
        Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Budi Prasetyo', 'max_pax' => 2]);

        $response = $this->actingAs($this->operator)
            ->getJson("/check-in/{$this->wedding->id}/search?q=Budi");

        // Returns list, not a single resolved guest — operator must choose
        $response->assertOk()
            ->assertJsonStructure(['guests' => [['id', 'name', 'max_pax', 'is_checked_in', 'token']]]);

        $this->assertCount(2, $response->json('guests'));
    }

    public function test_search_by_phone(): void
    {
        Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Citra', 'phone' => '08123456789', 'max_pax' => 1]);

        $response = $this->actingAs($this->operator)
            ->getJson("/check-in/{$this->wedding->id}/search?q=08123456789");

        $response->assertOk();
        $this->assertCount(1, $response->json('guests'));
        $this->assertEquals('Citra', $response->json('guests.0.name'));
    }

    public function test_search_isolated_to_wedding(): void
    {
        $otherClient = Client::create(['name' => 'Other', 'email' => 'o@o.com', 'status' => 'active']);
        $otherWedding = Wedding::create([
            'client_id' => $otherClient->id, 'template_id' => Template::first()->id,
            'public_id' => 'INV-SRCH02', 'slug' => 'other-search',
            'title' => 'Other', 'groom_name' => 'X', 'bride_name' => 'Y',
            'status' => 'published', 'published_at' => now(),
        ]);

        // Guest in other wedding with same name
        Guest::create(['wedding_id' => $otherWedding->id, 'name' => 'Shared Name', 'max_pax' => 1]);
        Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Shared Name', 'max_pax' => 1]);

        $response = $this->actingAs($this->operator)
            ->getJson("/check-in/{$this->wedding->id}/search?q=Shared");

        $response->assertOk();
        // Only returns guest from this wedding
        $this->assertCount(1, $response->json('guests'));
    }
}
