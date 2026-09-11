<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use App\Services\WeddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TemplateBuilderTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private User $superAdmin;

    private User $clientAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create(['name' => 'Test', 'email' => 'test@test.com', 'status' => 'active']);

        $this->superAdmin = User::create([
            'name' => 'Super', 'email' => 'super@test.com', 'password' => bcrypt('pass'),
            'role' => 'super_admin', 'is_active' => true,
        ]);

        $this->clientAdmin = User::create([
            'name' => 'Client', 'email' => 'client@test.com', 'password' => bcrypt('pass'),
            'client_id' => $this->client->id, 'role' => 'client_admin', 'is_active' => true,
        ]);
    }

    private function templatePayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Custom Template',
            'category' => 'general',
            'palette' => 'minang',
            'font_display' => 'Playfair Display',
            'font_body' => 'Lato',
            'is_active' => '1',
            'sort_order' => 1,
            'order' => json_encode(['couple', 'hero', 'opening']),
            'enabled' => ['couple' => '1', 'hero' => '1'],
            'title' => ['couple' => 'Mempelai Kami'],
        ], $overrides);
    }

    // ── Live preview ────────────────────────────────────────────────────────

    public function test_preview_renders_sample_content(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/admin/templates/preview?palette=minang&font_display=Playfair+Display&font_body=Lato')
            ->assertOk()
            ->assertSee('Andi Pratama', false)
            ->assertSee('Sari Dewi', false)
            ->assertSee('Amplop Digital', false)
            ->assertSee('Gedung Serbaguna Minang Permai', false);
    }

    public function test_preview_is_forbidden_for_client_admin(): void
    {
        $this->actingAs($this->clientAdmin)
            ->get('/admin/templates/preview')
            ->assertForbidden();
    }

    public function test_preview_respects_disabled_sections(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/admin/templates/preview?off=gift')
            ->assertOk()
            ->assertDontSee('Amplop Digital', false);
    }

    public function test_preview_respects_section_order(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get('/admin/templates/preview?order=gift,hero,couple')
            ->assertOk();

        $html = $response->getContent();

        // Gift dragged to the front must render before the hero.
        $this->assertLessThan(
            strpos($html, 'The Wedding Of'),
            strpos($html, 'Amplop Digital'),
            'Gift should render before the hero when ordered first.'
        );
    }

    public function test_preview_falls_back_on_unknown_palette_and_fonts(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/admin/templates/preview?palette=__nope__&font_display=<script>&font_body=__nope__')
            ->assertOk()
            ->assertDontSee('<script>alert', false);
    }

    public function test_preview_ignores_unknown_section_keys(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/admin/templates/preview?order=evil,hero&off=evil')
            ->assertOk()
            ->assertSee('The Wedding Of', false);
    }

    public function test_every_template_timeline_compiles(): void
    {
        // Blade silently drops a directive preceded by a word character, so
        // "... WIB@endif" leaked "@endif" into the compiled PHP and 500'd.
        $blade = app('blade.compiler');

        foreach (glob(resource_path('views/templates/*/sections/*.blade.php')) as $file) {
            $compiled = $blade->compileString(file_get_contents($file));

            $this->assertStringNotContainsString(
                '@endif',
                $compiled,
                "Uncompiled @endif in {$file} — a directive is preceded by a word character."
            );
            $this->assertStringNotContainsString('@endforeach', $compiled, "Uncompiled @endforeach in {$file}.");
        }
    }

    public function test_alternate_templates_render_timeline(): void
    {
        foreach (['modern-luxury', 'minimalist', 'floral-romantic'] as $key) {
            $template = Template::create([
                'key' => $key, 'name' => $key, 'is_active' => true, 'sort_order' => 1,
            ]);

            $wedding = app(WeddingService::class)->create($this->client->id, [
                'template_id' => $template->id, 'groom_name' => 'Andi', 'bride_name' => 'Sari',
            ]);
            $wedding->update(['status' => 'published', 'published_at' => now()]);
            $wedding->events()->create([
                'name' => 'Akad', 'starts_at' => now()->addMonth()->setTime(8, 0),
                'ends_at' => now()->addMonth()->setTime(10, 0), 'is_public' => true, 'sort_order' => 0,
            ]);

            $this->get("/{$wedding->public_id}/{$wedding->slug}")
                ->assertOk();
        }
    }

    // ── Builder pages render ────────────────────────────────────────────────

    public function test_create_page_renders(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/admin/templates/create')
            ->assertOk()
            ->assertSee('Struktur', false);
    }

    public function test_edit_page_renders_with_stored_layout(): void
    {
        $this->actingAs($this->superAdmin)->post('/admin/templates', $this->templatePayload());
        $template = Template::firstOrFail();

        $this->actingAs($this->superAdmin)
            ->get("/admin/templates/{$template->id}/edit")
            ->assertOk()
            ->assertSee('Mempelai Kami', false)
            ->assertSee('custom-template', false);
    }

    // ── Access control (spec §4) ────────────────────────────────────────────

    public function test_super_admin_can_view_templates_index(): void
    {
        $this->actingAs($this->superAdmin)
            ->get('/admin/templates')
            ->assertOk();
    }

    public function test_client_admin_cannot_view_templates_index(): void
    {
        $this->actingAs($this->clientAdmin)
            ->get('/admin/templates')
            ->assertForbidden();
    }

    public function test_client_admin_cannot_create_template(): void
    {
        $this->actingAs($this->clientAdmin)
            ->post('/admin/templates', $this->templatePayload())
            ->assertForbidden();

        $this->assertDatabaseCount('templates', 0);
    }

    // ── Creation ────────────────────────────────────────────────────────────

    public function test_super_admin_can_create_template(): void
    {
        $this->actingAs($this->superAdmin)
            ->post('/admin/templates', $this->templatePayload())
            ->assertRedirect();

        $template = Template::firstOrFail();
        $this->assertSame('custom-template', $template->key);
        $this->assertTrue($template->is_active);
        $this->assertSame(['primary' => '#7C3238', 'secondary' => '#F5F0E8', 'accent' => '#B8960C', 'dark' => '#2C1810'],
            $template->default_settings['palette']);
        $this->assertSame('Playfair Display', $template->default_settings['fonts']['display']);
    }

    public function test_section_order_is_persisted_from_the_builder(): void
    {
        $this->actingAs($this->superAdmin)->post('/admin/templates', $this->templatePayload());

        $keys = array_column(Template::firstOrFail()->default_sections, 'key');

        $this->assertSame('couple', $keys[0]);
        $this->assertSame('hero', $keys[1]);
        $this->assertSame('opening', $keys[2]);
    }

    public function test_disabled_and_titled_sections_are_persisted(): void
    {
        $this->actingAs($this->superAdmin)->post('/admin/templates', $this->templatePayload());

        $sections = collect(Template::firstOrFail()->default_sections)->keyBy('key');

        $this->assertTrue($sections['couple']['enabled']);
        $this->assertSame('Mempelai Kami', $sections['couple']['title']);
        $this->assertTrue($sections['hero']['enabled']);
        // Absent from `enabled` => off
        $this->assertFalse($sections['opening']['enabled']);
    }

    public function test_sections_missing_from_payload_are_appended(): void
    {
        $this->actingAs($this->superAdmin)->post('/admin/templates', $this->templatePayload());

        $keys = array_column(Template::firstOrFail()->default_sections, 'key');

        // The builder only sent three keys, but all canonical sections must exist.
        $this->assertCount(count(config('ngundang.sections')), $keys);
        foreach (array_keys(config('ngundang.sections')) as $canonical) {
            $this->assertContains($canonical, $keys);
        }
    }

    public function test_unknown_section_keys_are_discarded(): void
    {
        $this->actingAs($this->superAdmin)->post('/admin/templates', $this->templatePayload([
            'order' => json_encode(['couple', 'evil_key', 'hero']),
        ]));

        $keys = array_column(Template::firstOrFail()->default_sections, 'key');

        $this->assertNotContains('evil_key', $keys);
    }

    // ── Bootstrap from template layout ──────────────────────────────────────

    public function test_wedding_bootstraps_sections_from_template_layout(): void
    {
        $template = Template::create([
            'key' => 'order-test', 'name' => 'Order Test', 'is_active' => true, 'sort_order' => 1,
            'default_sections' => [
                ['key' => 'couple', 'enabled' => true, 'title' => 'Mempelai Kami'],
                ['key' => 'hero', 'enabled' => false, 'title' => null],
            ],
        ]);

        $wedding = app(WeddingService::class)->create($this->client->id, [
            'template_id' => $template->id,
            'groom_name' => 'Andi',
            'bride_name' => 'Sari',
        ]);

        $sections = $wedding->sections()->orderBy('sort_order')->get()->keyBy('section_key');

        $this->assertCount(count(config('ngundang.sections')), $sections);
        $this->assertSame('Mempelai Kami', $sections['couple']->title);
        $this->assertTrue($sections['couple']->is_enabled);
        $this->assertFalse($sections['hero']->is_enabled);

        // Saved order wins: couple then hero.
        $this->assertSame('couple', $wedding->sections()->orderBy('sort_order')->first()->section_key);
    }

    public function test_wedding_on_template_without_blade_files_still_renders(): void
    {
        $template = Template::create([
            'key' => 'no-blade-dir', 'name' => 'No Blade', 'is_active' => true, 'sort_order' => 1,
            'default_settings' => ['palette' => config('ngundang.palettes.minimal')],
        ]);

        $wedding = app(WeddingService::class)->create($this->client->id, [
            'template_id' => $template->id,
            'groom_name' => 'Andi',
            'bride_name' => 'Sari',
        ]);
        $wedding->update(['status' => 'published', 'published_at' => now()]);

        // Falls back to templates/default instead of throwing.
        $this->get("/{$wedding->public_id}/{$wedding->slug}")
            ->assertOk()
            ->assertSee('Sari', false);
    }

    // ── Wedding section reordering ──────────────────────────────────────────

    public function test_wedding_sections_can_be_reordered(): void
    {
        $template = Template::create([
            'key' => 'reorder', 'name' => 'Reorder', 'is_active' => true, 'sort_order' => 1,
        ]);

        $wedding = app(WeddingService::class)->create($this->client->id, [
            'template_id' => $template->id,
            'groom_name' => 'Andi',
            'bride_name' => 'Sari',
        ]);

        $sections = $wedding->sections()->orderBy('sort_order')->get();
        $first = $sections[0];
        $second = $sections[1];

        $this->actingAs($this->clientAdmin)
            ->postJson("/admin/weddings/{$wedding->id}/sections/reorder", [
                'order' => [$second->id, $first->id],
            ])
            ->assertOk();

        $this->assertSame(0, $second->fresh()->sort_order);
        $this->assertSame(1, $first->fresh()->sort_order);
    }

    public function test_reorder_cannot_touch_another_weddings_section(): void
    {
        $template = Template::create([
            'key' => 'iso', 'name' => 'Iso', 'is_active' => true, 'sort_order' => 1,
        ]);

        $weddingA = app(WeddingService::class)->create($this->client->id, [
            'template_id' => $template->id, 'groom_name' => 'A', 'bride_name' => 'B',
        ]);
        $weddingB = app(WeddingService::class)->create($this->client->id, [
            'template_id' => $template->id, 'groom_name' => 'C', 'bride_name' => 'D',
        ]);

        $foreign = $weddingB->sections()->orderBy('sort_order')->first();
        $original = $foreign->sort_order;

        $this->actingAs($this->clientAdmin)
            ->postJson("/admin/weddings/{$weddingA->id}/sections/reorder", [
                'order' => [$foreign->id],
            ])
            ->assertOk();

        // Scoped by wedding_id, so the foreign section is untouched.
        $this->assertSame($original, $foreign->fresh()->sort_order);
    }
}
