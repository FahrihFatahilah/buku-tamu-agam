<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\GiftMethod;
use App\Models\Guest;
use App\Models\GuestCategory;
use App\Models\Template;
use App\Models\User;
use App\Models\VisibilityRule;
use App\Models\Wedding;
use App\Models\WeddingSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GiftDisplayTest extends TestCase
{
    use RefreshDatabase;

    private Wedding $wedding;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::create(['name' => 'T', 'email' => 't@t.com', 'status' => 'active']);
        $template = Template::create(['key' => 'minang-elegance', 'name' => 'Minang', 'is_active' => true, 'sort_order' => 1]);

        $this->wedding = Wedding::create([
            'client_id' => $client->id, 'template_id' => $template->id,
            'public_id' => 'INV-GIFT01', 'slug' => 'gift-test',
            'title' => 'Test', 'groom_name' => 'A', 'bride_name' => 'B',
            'status' => 'published', 'published_at' => now(),
        ]);

        WeddingSection::create([
            'wedding_id' => $this->wedding->id, 'section_key' => 'hero',
            'is_enabled' => true, 'sort_order' => 0,
        ]);
        WeddingSection::create([
            'wedding_id' => $this->wedding->id, 'section_key' => 'gift',
            'is_enabled' => true, 'sort_order' => 1,
        ]);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('pass'),
            'client_id' => $client->id, 'role' => 'client_admin', 'is_active' => true,
        ]);
    }

    private function createGiftViaAdmin(array $overrides = []): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/weddings/{$this->wedding->id}/gift", array_merge([
                'type'           => 'bank_transfer',
                'label'          => 'BCA',
                'bank_name'      => 'BCA',
                'account_number' => '1234567890',
                'account_holder' => 'Andi',
                'is_active'      => '1',
            ], $overrides))
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    public function test_admin_gift_form_persists_gift_method(): void
    {
        $this->createGiftViaAdmin();

        $this->assertDatabaseHas('gift_methods', [
            'wedding_id' => $this->wedding->id,
            'label'      => 'BCA',
            'is_active'  => true,
        ]);
    }

    public function test_gift_created_via_admin_form_shows_on_public_invitation(): void
    {
        $this->createGiftViaAdmin();

        $this->get('/INV-GIFT01/gift-test')
            ->assertStatus(200)
            ->assertSee('Amplop Digital', false)
            ->assertSee('BCA', false);
    }

    public function test_gift_created_via_admin_form_shows_on_personalized_invitation(): void
    {
        $this->createGiftViaAdmin();

        $category = GuestCategory::create(['wedding_id' => $this->wedding->id, 'name' => 'Teman', 'sort_order' => 0]);
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id, 'category_id' => $category->id,
            'name' => 'Budi', 'max_pax' => 2,
        ]);

        $this->get("/INV-GIFT01/gift-test/u/{$guest->invitation_token}")
            ->assertStatus(200)
            ->assertSee('BCA', false);
    }

    public function test_unchecking_active_switch_deactivates_gift(): void
    {
        $this->actingAs($this->admin)
            ->post("/admin/weddings/{$this->wedding->id}/gift", [
                'type'      => 'bank_transfer',
                'label'     => 'Mandiri',
                'is_active' => '1',
            ])
            ->assertRedirect();

        $gift = GiftMethod::where('label', 'Mandiri')->firstOrFail();

        $this->actingAs($this->admin)
            ->put("/admin/weddings/{$this->wedding->id}/gift/{$gift->id}", [
                'type'      => 'bank_transfer',
                'label'     => 'Mandiri',
            ])
            ->assertRedirect();

        $this->assertFalse($gift->fresh()->is_active, 'Unchecking "Aktif" must deactivate the gift.');
    }

    public function test_unruled_gift_still_shows_when_another_gift_has_a_rule(): void
    {
        $this->createGiftViaAdmin(['label' => 'BCA']);
        $this->createGiftViaAdmin(['label' => 'Mandiri']);

        $category = GuestCategory::create(['wedding_id' => $this->wedding->id, 'name' => 'Teman', 'sort_order' => 0]);
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id, 'category_id' => $category->id,
            'name' => 'Budi', 'max_pax' => 2,
        ]);

        $bca = GiftMethod::where('label', 'BCA')->firstOrFail();
        VisibilityRule::create([
            'wedding_id'  => $this->wedding->id,
            'entity_type' => 'gift_method',
            'entity_id'   => $bca->id,
            'scope'       => 'category',
            'scope_id'    => $category->id,
            'is_visible'  => true,
        ]);

        $this->get("/INV-GIFT01/gift-test/u/{$guest->invitation_token}")
            ->assertStatus(200)
            ->assertSee('BCA', false)
            ->assertSee('Mandiri', false);
    }

    public function test_wedding_default_hidden_rule_hides_gift_from_guest(): void
    {
        $this->createGiftViaAdmin(['label' => 'BCA']);

        $category = GuestCategory::create(['wedding_id' => $this->wedding->id, 'name' => 'Teman', 'sort_order' => 0]);
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id, 'category_id' => $category->id,
            'name' => 'Budi', 'max_pax' => 2,
        ]);

        $bca = GiftMethod::where('label', 'BCA')->firstOrFail();
        VisibilityRule::create([
            'wedding_id'  => $this->wedding->id,
            'entity_type' => 'gift_method',
            'entity_id'   => $bca->id,
            'scope'       => 'wedding_default',
            'scope_id'    => null,
            'is_visible'  => false,
        ]);

        $this->get("/INV-GIFT01/gift-test/u/{$guest->invitation_token}")
            ->assertStatus(200)
            ->assertDontSee('1234567890', false);
    }
}
