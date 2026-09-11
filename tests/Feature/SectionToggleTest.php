<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\GiftMethod;
use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use App\Models\WeddingSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SectionToggleTest extends TestCase
{
    use RefreshDatabase;

    private Wedding $wedding;
    private User $admin;
    private WeddingSection $gift;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::create(['name' => 'T', 'email' => 't@t.com', 'status' => 'active']);
        $template = Template::create(['key' => 'minang-elegance', 'name' => 'Minang', 'is_active' => true, 'sort_order' => 1]);

        $this->wedding = Wedding::create([
            'client_id' => $client->id, 'template_id' => $template->id,
            'public_id' => 'INV-SECT01', 'slug' => 'section-test',
            'title' => 'Test', 'groom_name' => 'A', 'bride_name' => 'B',
            'status' => 'published', 'published_at' => now(),
        ]);

        WeddingSection::create(['wedding_id' => $this->wedding->id, 'section_key' => 'hero', 'is_enabled' => true, 'sort_order' => 0]);

        $this->gift = WeddingSection::create([
            'wedding_id' => $this->wedding->id,
            'section_key' => 'gift',
            'is_enabled' => true,
            'sort_order' => 1,
            'settings' => ['bg_color' => '#F5F0E8'],
        ]);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('pass'),
            'client_id' => $client->id, 'role' => 'client_admin', 'is_active' => true,
        ]);
    }

    private function togglePayload(array $overrides = []): array
    {
        return array_merge([
            'sort_order' => $this->gift->sort_order,
            'title'      => '',
            'is_enabled' => '0',
        ], $overrides);
    }

    public function test_unchecking_toggle_disables_section(): void
    {
        $this->actingAs($this->admin)
            ->put("/admin/weddings/{$this->wedding->id}/sections/{$this->gift->id}", $this->togglePayload())
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertFalse($this->gift->fresh()->is_enabled);
    }

    public function test_checking_toggle_enables_section(): void
    {
        $this->gift->update(['is_enabled' => false]);

        $this->actingAs($this->admin)
            ->put("/admin/weddings/{$this->wedding->id}/sections/{$this->gift->id}", $this->togglePayload(['is_enabled' => '1']))
            ->assertRedirect();

        $this->assertTrue($this->gift->fresh()->is_enabled);
    }

    public function test_toggle_off_preserves_section_settings(): void
    {
        $this->actingAs($this->admin)
            ->put("/admin/weddings/{$this->wedding->id}/sections/{$this->gift->id}", $this->togglePayload());

        $this->assertSame(['bg_color' => '#F5F0E8'], $this->gift->fresh()->settings);
    }

    public function test_expanded_form_keeps_disabled_state(): void
    {
        $this->gift->update(['is_enabled' => false]);

        $this->actingAs($this->admin)
            ->put("/admin/weddings/{$this->wedding->id}/sections/{$this->gift->id}", [
                'is_enabled'      => '0',
                'sort_order'      => $this->gift->sort_order,
                'title'           => 'Hadiah',
                'settings'        => ['bg_color' => '#ffffff', 'bg_opacity' => '80'],
            ])
            ->assertRedirect();

        $section = $this->gift->fresh();
        $this->assertFalse($section->is_enabled);
        $this->assertSame('Hadiah', $section->title);
        $this->assertSame('#ffffff', $section->settings['bg_color']);
    }

    public function test_disabled_gift_section_is_not_rendered(): void
    {
        GiftMethod::create([
            'wedding_id' => $this->wedding->id, 'type' => 'bank_transfer',
            'label' => 'BCA', 'account_number' => '123', 'is_active' => true, 'sort_order' => 1,
        ]);

        $this->gift->update(['is_enabled' => false]);

        $this->get('/INV-SECT01/section-test')
            ->assertStatus(200)
            ->assertDontSee('Amplop Digital', false);
    }

    public function test_enabled_gift_section_is_rendered(): void
    {
        GiftMethod::create([
            'wedding_id' => $this->wedding->id, 'type' => 'bank_transfer',
            'label' => 'BCA', 'account_number' => '123', 'is_active' => true, 'sort_order' => 1,
        ]);

        $this->get('/INV-SECT01/section-test')
            ->assertStatus(200)
            ->assertSee('Amplop Digital', false);
    }
}
