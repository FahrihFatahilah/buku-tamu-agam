<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Guest;
use App\Models\GiftMethod;
use App\Models\Template;
use App\Models\User;
use App\Models\VisibilityRule;
use App\Models\Wedding;
use App\Services\GuestVisibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisibilityEngineTest extends TestCase
{
    use RefreshDatabase;

    private Wedding $wedding;
    private GiftMethod $gift;
    private \App\Models\GuestCategory $categoryA;
    private \App\Models\GuestCategory $categoryB;
    private Guest $guestA;
    private Guest $guestB;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'status' => 'active']);
        $template = Template::create(['key' => 'minang-elegance', 'name' => 'Minang', 'is_active' => true, 'sort_order' => 1]);

        $this->wedding = Wedding::create([
            'client_id' => $client->id,
            'template_id' => $template->id,
            'public_id' => 'INV-VIS001',
            'slug' => 'vis-test',
            'title' => 'Test',
            'groom_name' => 'A',
            'bride_name' => 'B',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $this->categoryA = \App\Models\GuestCategory::create(['wedding_id' => $this->wedding->id, 'name' => 'Keluarga']);
        $this->categoryB = \App\Models\GuestCategory::create(['wedding_id' => $this->wedding->id, 'name' => 'Rekan Kerja']);

        $this->guestA = Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Budi', 'category_id' => $this->categoryA->id, 'max_pax' => 1]);
        $this->guestB = Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Andi', 'category_id' => $this->categoryB->id, 'max_pax' => 1]);

        $this->gift = GiftMethod::create([
            'wedding_id' => $this->wedding->id,
            'type' => 'bank_transfer',
            'label' => 'BCA',
            'bank_name' => 'BCA',
            'account_number' => '1234567890',
            'account_holder' => 'Test',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        \App\Models\WeddingSection::create([
            'wedding_id' => $this->wedding->id,
            'section_key' => 'gift',
            'is_enabled' => true,
            'sort_order' => 0,
        ]);
    }

    public function test_no_rules_means_visible_to_all(): void
    {
        $service = app(GuestVisibilityService::class);

        $this->assertTrue($service->isEntityVisible('gift_method', $this->gift->id, $this->wedding, $this->guestA));
        $this->assertTrue($service->isEntityVisible('gift_method', $this->gift->id, $this->wedding, $this->guestB));
        $this->assertTrue($service->isEntityVisible('gift_method', $this->gift->id, $this->wedding, null));
    }

    public function test_category_rule_hides_from_category(): void
    {
        // Hide gift from Rekan Kerja
        VisibilityRule::create([
            'wedding_id' => $this->wedding->id,
            'entity_type' => 'gift_method',
            'entity_id' => $this->gift->id,
            'scope' => 'category',
            'scope_id' => $this->categoryB->id,
            'is_visible' => false,
        ]);

        $service = app(GuestVisibilityService::class);

        $this->assertTrue($service->isEntityVisible('gift_method', $this->gift->id, $this->wedding, $this->guestA));
        $this->assertFalse($service->isEntityVisible('gift_method', $this->gift->id, $this->wedding, $this->guestB));
    }

    public function test_individual_override_has_highest_priority(): void
    {
        // Category rule: hide from Keluarga
        VisibilityRule::create([
            'wedding_id' => $this->wedding->id,
            'entity_type' => 'gift_method',
            'entity_id' => $this->gift->id,
            'scope' => 'category',
            'scope_id' => $this->categoryA->id,
            'is_visible' => false,
        ]);

        // Individual override: show to Budi (overrides category rule)
        VisibilityRule::create([
            'wedding_id' => $this->wedding->id,
            'entity_type' => 'gift_method',
            'entity_id' => $this->gift->id,
            'scope' => 'guest',
            'scope_id' => $this->guestA->id,
            'is_visible' => true,
        ]);

        $service = app(GuestVisibilityService::class);

        // Budi should see it (individual override wins)
        $this->assertTrue($service->isEntityVisible('gift_method', $this->gift->id, $this->wedding, $this->guestA));
    }

    public function test_private_gift_data_not_sent_to_browser_for_hidden_guest(): void
    {
        // Hide gift from Rekan Kerja
        VisibilityRule::create([
            'wedding_id' => $this->wedding->id,
            'entity_type' => 'gift_method',
            'entity_id' => $this->gift->id,
            'scope' => 'category',
            'scope_id' => $this->categoryB->id,
            'is_visible' => false,
        ]);

        // Access personalized invitation as guestB (Rekan Kerja)
        $response = $this->get("/INV-VIS001/vis-test/u/{$this->guestB->invitation_token}");

        $response->assertStatus(200);
        // Account number should NOT be in the response
        $response->assertDontSee('1234567890');
    }
}
