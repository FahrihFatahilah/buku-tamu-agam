<?php

namespace Tests\Feature\Builder;

use App\Builder\DocumentMigrator;
use App\Builder\Registry\WidgetRegistry;
use App\Models\Client;
use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use App\Services\Builder\DocumentService;
use App\Services\WeddingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderDocumentTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private Template $template;

    private Wedding $wedding;

    private User $superAdmin;

    private User $clientAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create(['name' => 'C', 'email' => 'c@test.com', 'status' => 'active']);

        $this->template = Template::create([
            'key' => 'minang-elegance',
            'name' => 'Minang',
            'is_active' => true,
            'sort_order' => 1,
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

        $this->superAdmin = User::create([
            'name' => 'Super', 'email' => 'super@test.com', 'password' => bcrypt('pass'),
            'role' => 'super_admin', 'is_active' => true,
        ]);

        $this->clientAdmin = User::create([
            'name' => 'Client', 'email' => 'client@test.com', 'password' => bcrypt('pass'),
            'client_id' => $this->client->id, 'role' => 'client_admin', 'is_active' => true,
        ]);
    }

    // ── Access control ──────────────────────────────────────────────────────

    public function test_super_admin_can_open_the_builder(): void
    {
        $this->actingAs($this->superAdmin)
            ->get("/admin/weddings/{$this->wedding->id}/builder")
            ->assertOk()
            ->assertSee('builder-root', false);
    }

    public function test_client_admin_cannot_open_the_builder(): void
    {
        $this->actingAs($this->clientAdmin)
            ->get("/admin/weddings/{$this->wedding->id}/builder")
            ->assertForbidden();
    }

    public function test_client_admin_cannot_save_a_document(): void
    {
        $this->actingAs($this->clientAdmin)
            ->putJson("/admin/weddings/{$this->wedding->id}/builder/document", ['nodes' => []])
            ->assertForbidden();
    }

    public function test_client_admin_cannot_revert_a_document(): void
    {
        $this->actingAs($this->clientAdmin)
            ->deleteJson("/admin/weddings/{$this->wedding->id}/builder/document")
            ->assertForbidden();
    }

    public function test_guest_cannot_open_the_builder(): void
    {
        $this->get("/admin/weddings/{$this->wedding->id}/builder")->assertRedirect();
    }

    // ── Legacy bridge ───────────────────────────────────────────────────────

    public function test_builder_synthesises_a_document_from_existing_sections(): void
    {
        // No document yet: the editor must still show something meaningful.
        $response = $this->actingAs($this->superAdmin)
            ->getJson("/admin/weddings/{$this->wedding->id}/builder/document")
            ->assertOk();

        $document = $response->json('document');

        $this->assertSame(2, $document['version']);
        $this->assertFalse($response->json('active'));
        $this->assertCount(count(config('ngundang.sections')), $document['nodes']);
    }

    public function test_synthesised_document_keeps_section_order_and_disabled_state(): void
    {
        $sections = $this->wedding->sections()->orderBy('sort_order')->get();

        // Disable the gift section and give the quote a custom title.
        $sections->firstWhere('section_key', 'gift')->update(['is_enabled' => false]);

        $document = app(DocumentMigrator::class)->migrate(null, $this->wedding->fresh());

        $keys = array_map(fn ($node) => $node['children'][0]['type'] ?? null, $document['nodes']);

        $this->assertSame('opening', $keys[0]);
        $this->assertContains('gift', $keys);

        $giftIndex = array_search('gift', $keys, true);
        $this->assertTrue($document['nodes'][$giftIndex]['disabled']);
    }

    public function test_legacy_section_text_migrates_into_widget_props(): void
    {
        $this->wedding->sections()->where('section_key', 'gift')->update([
            'settings' => ['text' => ['heading' => 'Tanda Kasih']],
        ]);

        $document = app(DocumentMigrator::class)->migrate(null, $this->wedding->fresh());

        $gift = collect($document['nodes'])->firstWhere(
            fn ($node) => ($node['children'][0]['type'] ?? null) === 'gift'
        );

        $this->assertSame('Tanda Kasih', $gift['children'][0]['props']['heading']);
    }

    public function test_theme_is_derived_from_the_template_palette(): void
    {
        $document = app(DocumentMigrator::class)->migrate(null, $this->wedding->fresh());

        $this->assertSame('#7C3238', $document['theme']['colors']['primary']);
        $this->assertSame('Playfair Display', $document['theme']['typography']['headingFont']);
    }

    // ── Template bridge ─────────────────────────────────────────────────────

    public function test_template_synthesises_a_document_from_its_stored_layout(): void
    {
        $this->template->update(['default_sections' => [
            ['key' => 'opening', 'enabled' => true, 'title' => null, 'settings' => []],
            ['key' => 'gift', 'enabled' => false, 'title' => 'Hadiah Kami', 'settings' => ['text' => ['heading' => 'Tanda Kasih']]],
        ]]);

        $document = app(DocumentMigrator::class)->migrate(null, $this->template->fresh());

        $types = array_map(fn ($node) => $node['children'][0]['type'] ?? null, $document['nodes']);

        $this->assertSame(['opening', 'gift'], $types);

        $gift = $document['nodes'][1];

        $this->assertTrue($gift['disabled']);
        $this->assertSame('Hadiah Kami', $gift['props']['label']);
        $this->assertSame('Tanda Kasih', $gift['children'][0]['props']['heading']);
    }

    public function test_template_theme_comes_from_its_own_palette(): void
    {
        $this->template->update(['default_settings' => [
            'palette' => ['primary' => '#111111', 'secondary' => '#FAFAFA', 'accent' => '#C9A96E', 'dark' => '#000000'],
            'fonts' => ['display' => 'Lora', 'body' => 'Inter'],
        ]]);

        $document = app(DocumentMigrator::class)->migrate(null, $this->template->fresh());

        $this->assertSame('#111111', $document['theme']['colors']['primary']);
        $this->assertSame('#000000', $document['theme']['colors']['text']);
        $this->assertSame('Lora', $document['theme']['typography']['headingFont']);
        $this->assertSame('Inter', $document['theme']['typography']['bodyFont']);
    }

    public function test_template_without_a_layout_yields_an_empty_document(): void
    {
        $this->template->update(['default_sections' => null]);

        $document = app(DocumentMigrator::class)->migrate(null, $this->template->fresh());

        $this->assertSame([], $document['nodes']);
        $this->assertSame(DocumentMigrator::VERSION, $document['version']);
    }

    public function test_migration_is_idempotent(): void
    {
        $migrator = app(DocumentMigrator::class);

        $once = $migrator->migrate(null, $this->wedding->fresh());
        $twice = $migrator->migrate($once, $this->wedding->fresh());

        $this->assertSame(json_encode($once['nodes']), json_encode($twice['nodes']));
    }

    public function test_future_version_does_not_break(): void
    {
        $document = app(DocumentMigrator::class)->migrate([
            'version' => 999,
            'nodes' => [],
            'theme' => [],
        ], $this->wedding);

        $this->assertSame(DocumentMigrator::VERSION, $document['version']);
    }

    public function test_malformed_document_degrades_safely(): void
    {
        $document = app(DocumentMigrator::class)->migrate([
            'nodes' => 'not-an-array',
            'overlays' => null,
        ], $this->wedding);

        // Never throws, and always yields a renderable document — here by
        // falling back to synthesising from the wedding's own sections.
        $this->assertIsArray($document['nodes']);
        $this->assertNotEmpty($document['nodes']);
        $this->assertSame(DocumentMigrator::VERSION, $document['version']);
    }

    // ── Save / enable / revert ──────────────────────────────────────────────

    public function test_saving_a_document_persists_the_sanitised_version(): void
    {
        $payload = [
            'version' => 2,
            'nodes' => [[
                'id' => 'hero-1',
                'type' => 'heading',
                'props' => ['text' => 'Halo Dunia', 'level' => 1],
                'styles' => ['desktop' => ['fontSize' => 48, 'color' => 'accent']],
                'children' => [],
            ]],
        ];

        $this->actingAs($this->superAdmin)
            ->putJson("/admin/weddings/{$this->wedding->id}/builder/document", $payload)
            ->assertOk()
            ->assertJsonPath('saved', true);

        $wedding = $this->wedding->fresh();

        $this->assertTrue($wedding->hasBuilderDocument());
        $this->assertSame('Halo Dunia', $wedding->builder_document['nodes'][0]['props']['text']);
        $this->assertSame(48, $wedding->builder_document['nodes'][0]['styles']['desktop']['fontSize']);
    }

    public function test_unknown_widget_type_is_dropped(): void
    {
        $this->actingAs($this->superAdmin)->putJson(
            "/admin/weddings/{$this->wedding->id}/builder/document",
            ['nodes' => [
                ['id' => 'a', 'type' => 'heading', 'props' => ['text' => 'ok']],
                ['id' => 'b', 'type' => 'evil-script-widget', 'props' => ['text' => 'nope']],
            ]]
        )->assertOk();

        $nodes = $this->wedding->fresh()->builder_document['nodes'];

        $this->assertCount(1, $nodes);
        $this->assertSame('heading', $nodes[0]['type']);
    }

    public function test_unknown_props_are_dropped(): void
    {
        $this->actingAs($this->superAdmin)->putJson(
            "/admin/weddings/{$this->wedding->id}/builder/document",
            ['nodes' => [[
                'id' => 'a',
                'type' => 'heading',
                'props' => ['text' => 'ok', 'onclick' => 'alert(1)', 'evil' => 'x'],
            ]]]
        )->assertOk();

        $props = $this->wedding->fresh()->builder_document['nodes'][0]['props'];

        $this->assertArrayHasKey('text', $props);
        $this->assertArrayNotHasKey('onclick', $props);
        $this->assertArrayNotHasKey('evil', $props);
    }

    public function test_unknown_style_keys_are_dropped(): void
    {
        $this->actingAs($this->superAdmin)->putJson(
            "/admin/weddings/{$this->wedding->id}/builder/document",
            ['nodes' => [[
                'id' => 'a',
                'type' => 'heading',
                'props' => ['text' => 'ok'],
                'styles' => ['desktop' => ['fontSize' => 30, 'position' => 'fixed', 'background' => 'url(javascript:alert(1))']],
            ]]]
        )->assertOk();

        $styles = $this->wedding->fresh()->builder_document['nodes'][0]['styles']['desktop'];

        $this->assertArrayHasKey('fontSize', $styles);
        // `background` is not an allowed key, so it never reaches storage.
        $this->assertArrayNotHasKey('background', $styles);
    }

    public function test_injected_style_values_are_rejected(): void
    {
        $this->actingAs($this->superAdmin)->putJson(
            "/admin/weddings/{$this->wedding->id}/builder/document",
            ['nodes' => [[
                'id' => 'a',
                'type' => 'heading',
                'props' => ['text' => 'ok'],
                'styles' => ['desktop' => [
                    'color' => 'javascript:alert(1)',
                    'backgroundColor' => 'url(//evil.test/x.png)',
                    'backgroundImage' => '../../etc/passwd',
                ]],
            ]]]
        )->assertOk();

        $styles = $this->wedding->fresh()->builder_document['nodes'][0]['styles']['desktop'] ?? [];

        $this->assertArrayNotHasKey('color', $styles);
        $this->assertArrayNotHasKey('backgroundColor', $styles);
        $this->assertArrayNotHasKey('backgroundImage', $styles);
    }

    public function test_unknown_animation_type_is_dropped(): void
    {
        $this->actingAs($this->superAdmin)->putJson(
            "/admin/weddings/{$this->wedding->id}/builder/document",
            ['nodes' => [[
                'id' => 'a',
                'type' => 'heading',
                'props' => ['text' => 'ok'],
                'animation' => [
                    'entrance' => ['type' => 'fade-up'],
                    'continuous' => ['type' => 'not-a-real-animation'],
                ],
            ]]]
        )->assertOk();

        $animation = $this->wedding->fresh()->builder_document['nodes'][0]['animation'];

        $this->assertSame('fade-up', $animation['entrance']['type']);
        $this->assertArrayNotHasKey('continuous', $animation);
    }

    public function test_duplicate_ids_are_regenerated(): void
    {
        $this->actingAs($this->superAdmin)->putJson(
            "/admin/weddings/{$this->wedding->id}/builder/document",
            ['nodes' => [
                ['id' => 'same', 'type' => 'heading', 'props' => ['text' => 'a']],
                ['id' => 'same', 'type' => 'heading', 'props' => ['text' => 'b']],
            ]]
        )->assertOk();

        $nodes = $this->wedding->fresh()->builder_document['nodes'];

        $this->assertNotSame($nodes[0]['id'], $nodes[1]['id']);
    }

    public function test_children_are_rejected_on_non_container_widgets(): void
    {
        $this->actingAs($this->superAdmin)->putJson(
            "/admin/weddings/{$this->wedding->id}/builder/document",
            ['nodes' => [[
                'id' => 'a',
                'type' => 'heading',
                'props' => ['text' => 'x'],
                'children' => [['id' => 'b', 'type' => 'text', 'props' => ['text' => 'nested']]],
            ]]]
        )->assertOk();

        $this->assertSame([], $this->wedding->fresh()->builder_document['nodes'][0]['children']);
    }

    public function test_nested_children_are_accepted_on_containers(): void
    {
        $this->actingAs($this->superAdmin)->putJson(
            "/admin/weddings/{$this->wedding->id}/builder/document",
            ['nodes' => [[
                'id' => 'sec',
                'type' => 'section',
                'props' => [],
                'children' => [['id' => 'h', 'type' => 'heading', 'props' => ['text' => 'nested']]],
            ]]]
        )->assertOk();

        $children = $this->wedding->fresh()->builder_document['nodes'][0]['children'];

        $this->assertCount(1, $children);
        $this->assertSame('heading', $children[0]['type']);
    }

    public function test_grid_only_accepts_columns(): void
    {
        $this->actingAs($this->superAdmin)->putJson(
            "/admin/weddings/{$this->wedding->id}/builder/document",
            ['nodes' => [[
                'id' => 'g',
                'type' => 'grid',
                'props' => ['columns' => 2],
                'children' => [
                    ['id' => 'c1', 'type' => 'column', 'props' => []],
                    ['id' => 'h1', 'type' => 'heading', 'props' => ['text' => 'not allowed here']],
                ],
            ]]]
        )->assertOk();

        $children = $this->wedding->fresh()->builder_document['nodes'][0]['children'];

        $this->assertCount(1, $children);
        $this->assertSame('column', $children[0]['type']);
    }

    public function test_enable_then_revert_round_trip(): void
    {
        $this->actingAs($this->superAdmin)
            ->postJson("/admin/weddings/{$this->wedding->id}/builder/enable")
            ->assertOk();

        $this->assertTrue($this->wedding->fresh()->hasBuilderDocument());

        $this->actingAs($this->superAdmin)
            ->deleteJson("/admin/weddings/{$this->wedding->id}/builder/document")
            ->assertOk();

        $this->assertFalse($this->wedding->fresh()->hasBuilderDocument());
    }

    public function test_preview_stages_an_unsaved_document(): void
    {
        $this->actingAs($this->superAdmin)
            ->postJson("/admin/weddings/{$this->wedding->id}/builder/preview", [
                'document' => json_encode([
                    'version' => 2,
                    'nodes' => [['id' => 'h', 'type' => 'heading', 'props' => ['text' => 'Pratinjau']]],
                ]),
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        // Nothing is persisted by staging.
        $this->assertFalse($this->wedding->fresh()->hasBuilderDocument());

        $this->actingAs($this->superAdmin)
            ->get("/admin/weddings/{$this->wedding->id}/builder/preview")
            ->assertOk()
            ->assertSee('Pratinjau', false);
    }

    public function test_preview_stages_a_document_sent_as_a_json_object(): void
    {
        // The editor sends the document as an object in the JSON body (not a
        // pre-encoded string), so the controller must accept an array here.
        $this->actingAs($this->superAdmin)
            ->postJson("/admin/weddings/{$this->wedding->id}/builder/preview", [
                'document' => [
                    'version' => 2,
                    'nodes' => [['id' => 'h', 'type' => 'heading', 'props' => ['text' => 'Objek']]],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('ok', true);

        $this->assertFalse($this->wedding->fresh()->hasBuilderDocument());

        $this->actingAs($this->superAdmin)
            ->get("/admin/weddings/{$this->wedding->id}/builder/preview")
            ->assertOk()
            ->assertSee('Objek', false);
    }

    public function test_preview_rejects_a_document_that_is_not_an_object(): void
    {
        $this->actingAs($this->superAdmin)
            ->postJson("/admin/weddings/{$this->wedding->id}/builder/preview", [
                'document' => 'not json',
            ])
            ->assertStatus(422)
            ->assertJsonPath('ok', false);
    }

    public function test_publish_materialises_the_document_and_publishes(): void
    {
        $this->actingAs($this->superAdmin)
            ->postJson("/admin/weddings/{$this->wedding->id}/builder/publish")
            ->assertOk()
            ->assertJsonPath('published', true);

        $wedding = $this->wedding->fresh();

        $this->assertTrue($wedding->hasBuilderDocument());
        $this->assertSame('published', $wedding->status);
    }

    public function test_registry_payload_reaches_the_editor(): void
    {
        $html = $this->actingAs($this->superAdmin)
            ->get("/admin/weddings/{$this->wedding->id}/builder")
            ->assertOk()
            ->getContent();

        // The registry is embedded as a JSON data attribute (quote-escaped for
        // safe inclusion in the attribute), so decode it before asserting.
        $this->assertMatchesRegularExpression('/data-builder="([^"]+)"/', $html, 'builder bootstrap attribute missing');

        preg_match('/data-builder="([^"]+)"/', $html, $matches);

        $payload = json_decode(html_entity_decode($matches[1], ENT_QUOTES), true);

        $this->assertIsArray($payload);
        $this->assertArrayHasKey('registry', $payload);
        $this->assertArrayHasKey('document', $payload);

        $registry = $payload['registry'];

        foreach (['widgets', 'decorations', 'overlays', 'animations', 'effects'] as $key) {
            $this->assertArrayHasKey($key, $registry, "registry is missing {$key}");
        }

        // Every registered widget reaches the editor with its inspector schema.
        $this->assertCount(count(config('builder.widgets')), $registry['widgets']['items']);
        $this->assertNotEmpty($registry['decorations']['items']);
        $this->assertNotEmpty($registry['overlays']['items']);
        $this->assertNotEmpty($registry['animations']['items']);
    }

    public function test_adding_a_registry_entry_needs_no_core_change(): void
    {
        // The extensibility guarantee: a widget declared in config appears in
        // the editor payload automatically.
        config(['builder.widgets.test-only-widget' => [
            'name' => 'Uji Coba',
            'category' => 'basic',
            'icon' => 'star',
            'view' => 'invitation.widgets.text',
            'defaultProps' => ['text' => 'uji'],
            'defaultStyles' => ['desktop' => []],
            'allowedChildren' => false,
            'inspector' => ['content' => [], 'style' => [], 'advanced' => []],
        ]]);

        $this->app->forgetInstance(WidgetRegistry::class);

        $this->assertTrue(app(WidgetRegistry::class)->has('test-only-widget'));
    }

    public function test_duplicate_copies_a_document_without_sharing_ids(): void
    {
        $this->actingAs($this->superAdmin)->putJson(
            "/admin/weddings/{$this->wedding->id}/builder/document",
            ['nodes' => [['id' => 'original', 'type' => 'heading', 'props' => ['text' => 'x']]]]
        )->assertOk();

        $other = app(WeddingService::class)->create($this->client->id, [
            'template_id' => $this->template->id,
            'groom_name' => 'Budi',
            'bride_name' => 'Rina',
        ]);

        $copy = app(DocumentService::class)->duplicate($this->wedding->fresh(), $other);

        $this->assertNotSame('original', $copy['nodes'][0]['id']);
        $this->assertSame('x', $copy['nodes'][0]['props']['text']);
    }
}
