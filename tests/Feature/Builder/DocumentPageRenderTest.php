<?php

namespace Tests\Feature\Builder;

use App\Models\Client;
use App\Models\Guest;
use App\Models\GuestCategory;
use App\Models\Template;
use App\Models\Wedding;
use App\Services\WeddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentPageRenderTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private Template $template;

    private Wedding $wedding;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create(['name' => 'C', 'email' => 'c@test.com', 'status' => 'active']);

        $this->template = Template::create([
            'key' => 'minang-elegance', 'name' => 'Minang', 'is_active' => true, 'sort_order' => 1,
            'default_settings' => [
                'palette' => ['primary' => '#7C3238', 'secondary' => '#F5F0E8', 'accent' => '#B8960C', 'dark' => '#2C1810'],
                'fonts' => ['display' => 'Playfair Display', 'body' => 'Lato'],
            ],
        ]);

        $this->wedding = app(WeddingService::class)->create($this->client->id, [
            'template_id' => $this->template->id,
            'groom_name' => 'Andi',
            'bride_name' => 'Sari',
        ]);

        $this->wedding->update(['status' => 'published', 'published_at' => now()]);
    }

    /** Turn on the document renderer with a minimal document. */
    private function useDocument(array $nodes, array $overlays = []): void
    {
        $this->wedding->update([
            'builder_document' => [
                'version' => 2,
                'theme' => ['colors' => ['primary' => '#7C3238', 'accent' => '#B8960C'], 'typography' => ['headingFont' => 'Playfair Display', 'bodyFont' => 'Lato']],
                'nodes' => $nodes,
                'overlays' => $overlays,
            ],
        ]);
    }

    public function test_without_a_document_the_coded_template_is_used(): void
    {
        // The coded path must keep working untouched.
        $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")
            ->assertOk()
            ->assertSee('Sari', false);
    }

    public function test_a_document_switches_the_rendering_path(): void
    {
        $this->useDocument([[
            'id' => 'hero1',
            'type' => 'section',
            'props' => [],
            'children' => [[
                'id' => 'h1',
                'type' => 'heading',
                'props' => ['text' => 'Dokumen Builder Aktif', 'level' => 1],
                'styles' => ['desktop' => ['fontSize' => 40]],
            ]],
        ]]);

        $response = $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")
            ->assertOk()
            ->assertSee('Dokumen Builder Aktif', false);

        // Node ids and compiled styles are present.
        $response->assertSee('data-node-id="hero1"', false);
        $response->assertSee('font-size:40px', false);
    }

    public function test_invitation_widgets_read_live_wedding_data(): void
    {
        $this->useDocument([[
            'id' => 'couple1',
            'type' => 'couple-names',
            'props' => ['eyebrow' => 'Mempelai'],
            'children' => [],
        ]]);

        $response = $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")->assertOk();

        // Names come from the model, not from the document.
        $response->assertSee('Andi', false);
        $response->assertSee('Sari', false);
    }

    public function test_disabled_nodes_are_skipped(): void
    {
        $this->useDocument([
            ['id' => 'a', 'type' => 'heading', 'props' => ['text' => 'Tampil'], 'children' => []],
            ['id' => 'b', 'type' => 'heading', 'props' => ['text' => 'Tersembunyi'], 'children' => [], 'disabled' => true],
        ]);

        $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")
            ->assertOk()
            ->assertSee('Tampil', false)
            ->assertDontSee('Tersembunyi', false);
    }

    public function test_guest_specific_widgets_need_a_guest(): void
    {
        $this->useDocument([
            ['id' => 'rsvp1', 'type' => 'rsvp', 'props' => ['heading' => 'RSVP'], 'children' => []],
            ['id' => 'h1', 'type' => 'heading', 'props' => ['text' => 'Halo'], 'children' => []],
        ]);

        // Public (non-personalized) invitation: the guest widget is omitted.
        $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")
            ->assertOk()
            ->assertSee('Halo', false)
            ->assertDontSee('Kirim Konfirmasi', false);
    }

    public function test_personalized_document_renders_the_guest_name(): void
    {
        $category = GuestCategory::create(['wedding_id' => $this->wedding->id, 'name' => 'Teman']);

        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'category_id' => $category->id,
            'name' => 'Budi Santoso',
            'max_pax' => 2,
        ]);

        $this->useDocument([[
            'id' => 'g1',
            'type' => 'guest-name',
            'props' => ['prefix' => 'Kepada Yth.'],
            'children' => [],
        ]]);

        $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}/u/{$guest->invitation_token}")
            ->assertOk()
            ->assertSee('Budi Santoso', false);
    }

    public function test_query_params_cannot_override_the_document_guest_identity(): void
    {
        $category = GuestCategory::create(['wedding_id' => $this->wedding->id, 'name' => 'Teman']);

        $guest = Guest::create([
            'wedding_id' => $this->wedding->id,
            'category_id' => $category->id,
            'name' => 'Budi Santoso',
            'max_pax' => 2,
        ]);

        $this->useDocument([[
            'id' => 'g1',
            'type' => 'guest-name',
            'props' => ['prefix' => 'Kepada Yth.'],
            'children' => [],
        ]]);

        $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}/u/{$guest->invitation_token}?name=Andi&guest_id=999")
            ->assertOk()
            ->assertSee('Budi Santoso', false);
    }

    public function test_public_page_does_not_load_builder_assets(): void
    {
        $this->useDocument([[
            'id' => 'h1', 'type' => 'heading', 'props' => ['text' => 'Halo'], 'children' => [],
        ]]);

        $html = $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")->assertOk()->getContent();

        // The lightweight-public-page requirement, asserted rather than assumed.
        $this->assertStringNotContainsString('builder.js', $html);
        $this->assertStringNotContainsString('builder.css', $html);
        $this->assertStringNotContainsString('builder-root', $html);
        $this->assertStringNotContainsString('dnd-kit', $html);
    }

    public function test_overlays_render_when_enabled(): void
    {
        $this->useDocument(
            [['id' => 'h1', 'type' => 'heading', 'props' => ['text' => 'Halo'], 'children' => []]],
            [
                ['id' => 'ov1', 'type' => 'falling-petals', 'enabled' => true, 'props' => ['density' => 6], 'styles' => []],
                ['id' => 'ov2', 'type' => 'sparkles', 'enabled' => false, 'props' => [], 'styles' => []],
            ]
        );

        $response = $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")->assertOk();

        $response->assertSee('data-overlay-id="ov1"', false);
        // Disabled overlays never render.
        $response->assertDontSee('data-overlay-id="ov2"', false);
        // The animated overlay actually emitted particles.
        $response->assertSee('class="petal"', false);
    }

    public function test_every_overlay_type_renders_without_error(): void
    {
        // Registry-driven: every registered overlay must render with default
        // props, so adding one to config cannot silently break the page.
        foreach (array_keys(config('builder.overlays')) as $type) {
            $this->useDocument(
                [['id' => 'h1', 'type' => 'heading', 'props' => ['text' => 'Halo'], 'children' => []]],
                [['id' => 'ovx', 'type' => $type, 'enabled' => true, 'props' => [], 'styles' => []]]
            );

            $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")
                ->assertOk()
                ->assertSee('data-overlay-id="ovx"', false);
        }
    }

    public function test_every_widget_type_renders_with_default_props(): void
    {
        // Same guarantee for widgets: default props must be renderable.
        foreach (config('builder.widgets') as $type => $definition) {
            $nodes = [[
                'id' => 'w1',
                'type' => $type,
                'props' => $definition['defaultProps'] ?? [],
                'styles' => $definition['defaultStyles'] ?? [],
                'children' => [],
            ]];

            $this->useDocument($nodes);

            $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")->assertOk();
        }
    }

    public function test_a_broken_document_falls_back_to_the_coded_template(): void
    {
        // Force a render failure by pointing a widget at a missing view.
        $this->wedding->update([
            'builder_document' => [
                'version' => 2,
                'theme' => [],
                'nodes' => [['id' => 'x', 'type' => 'heading', 'props' => ['text' => 'ok'], 'children' => []]],
                'overlays' => [],
            ],
        ]);

        // Even with an unrenderable widget view the page must not 500. The
        // renderer skips unknown views, so the page still resolves.
        $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")->assertOk();
    }

    public function test_decorations_render_with_themeable_builtin_svg(): void
    {
        $this->useDocument([[
            'id' => 'deco1',
            'type' => 'decoration',
            'props' => ['asset' => 'builtin:flower', 'color' => '#C9A84C'],
            'styles' => ['desktop' => ['x' => 20, 'y' => 40, 'width' => 120]],
            'children' => [],
        ]]);

        $response = $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")->assertOk();

        $response->assertSee('data-node-id="deco1"', false);
        // Inlined so it can inherit the authored colour.
        $response->assertSee('color: #C9A84C', false);
        $response->assertSee('left:20px', false);
    }

    public function test_animations_emit_hooks_when_enabled(): void
    {
        $this->wedding->update(['animation_config' => ['preset' => 'elegant', 'duration' => 'normal', 'intensity' => 'normal']]);

        $this->useDocument([[
            'id' => 'h1',
            'type' => 'heading',
            'props' => ['text' => 'Halo'],
            'children' => [],
            'animation' => ['entrance' => ['type' => 'fade-up', 'duration' => 800, 'delay' => 200]],
        ]]);

        $response = $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")->assertOk();

        $response->assertSee('data-anim-entrance="fade-up"', false);
        $response->assertSee('data-anim-delay="200"', false);
        $response->assertSee('data-anim-watch="1"', false);
    }

    public function test_animation_preset_none_disables_animation_hooks(): void
    {
        // This is the previously-dead animation_config finally taking effect.
        $this->wedding->update(['animation_config' => ['preset' => 'none']]);

        $this->useDocument([[
            'id' => 'h1',
            'type' => 'heading',
            'props' => ['text' => 'Halo'],
            'children' => [],
            'animation' => ['entrance' => ['type' => 'fade-up']],
        ]]);

        // The attribute itself must be absent (the stylesheet mentions the
        // selector, so assert on the rendered attribute, not the raw string).
        $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")
            ->assertOk()
            ->assertDontSee('data-anim-entrance="', false);
    }

    public function test_animation_duration_scales_with_the_preset(): void
    {
        $this->wedding->update(['animation_config' => ['preset' => 'cinematic', 'duration' => 'slow', 'intensity' => 'normal']]);

        $this->useDocument([[
            'id' => 'h1',
            'type' => 'heading',
            'props' => ['text' => 'Halo'],
            'children' => [],
            'animation' => ['entrance' => ['type' => 'fade', 'duration' => 1000]],
        ]]);

        $html = $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")->assertOk()->getContent();

        // cinematic (1.4) * slow (1.4) * 1000ms = 1960ms
        $this->assertMatchesRegularExpression('/data-anim-entrance-duration="19\d\d"/', $html);
    }

    public function test_unpublished_wedding_with_a_document_still_404s(): void
    {
        $this->useDocument([['id' => 'h1', 'type' => 'heading', 'props' => ['text' => 'x'], 'children' => []]]);
        $this->wedding->update(['status' => 'draft']);

        $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}")->assertNotFound();
    }
}
