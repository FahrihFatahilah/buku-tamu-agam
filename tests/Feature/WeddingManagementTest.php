<?php

namespace Tests\Feature;

use App\Jobs\ImportGuestsCsv;
use App\Models\Client;
use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WeddingManagementTest extends TestCase
{
    use RefreshDatabase;

    private Wedding $wedding;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $client = Client::create(['name' => 'T', 'email' => 't@t.com', 'status' => 'active']);
        $template = Template::create(['key' => 'minang-elegance', 'name' => 'Minang', 'is_active' => true, 'sort_order' => 1]);

        $this->wedding = Wedding::create([
            'client_id'   => $client->id,
            'template_id' => $template->id,
            'public_id'   => 'INV-MGMT01',
            'slug'        => 'mgmt-test',
            'title'       => 'Test Wedding',
            'groom_name'  => 'Andi',
            'bride_name'  => 'Sari',
            'status'      => 'draft',
        ]);

        $this->admin = User::create([
            'name'      => 'Admin',
            'email'     => 'admin@test.com',
            'password'  => bcrypt('pass'),
            'client_id' => $client->id,
            'role'      => 'client_admin',
            'is_active' => true,
        ]);
    }

    // ── Archive ────────────────────────────────────────────────────────────────

    public function test_admin_can_archive_wedding(): void
    {
        $this->wedding->update(['status' => 'published', 'published_at' => now()]);

        $this->actingAs($this->admin)
            ->post("/admin/weddings/{$this->wedding->id}/archive");

        $this->wedding->refresh();
        $this->assertEquals('archived', $this->wedding->status);
    }

    public function test_archived_wedding_returns_404_publicly(): void
    {
        $this->wedding->update(['status' => 'archived']);

        $response = $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}");

        $response->assertNotFound();
    }

    // ── Preview ────────────────────────────────────────────────────────────────

    public function test_draft_wedding_returns_404_without_preview_session(): void
    {
        $response = $this->get("/{$this->wedding->public_id}/{$this->wedding->slug}");
        $response->assertNotFound();
    }

    public function test_admin_can_preview_draft_wedding(): void
    {
        $response = $this->actingAs($this->admin)
            ->get("/admin/weddings/{$this->wedding->id}/preview");

        // Should redirect to public URL
        $response->assertRedirect($this->wedding->publicUrl());
    }

    public function test_preview_session_allows_draft_access(): void
    {
        // Set preview session
        $this->actingAs($this->admin)
            ->get("/admin/weddings/{$this->wedding->id}/preview");

        // Now access public URL with session
        $response = $this->actingAs($this->admin)
            ->withSession(["preview_wedding_{$this->wedding->id}" => true])
            ->get("/{$this->wedding->public_id}/{$this->wedding->slug}");

        $response->assertOk();
    }

    // ── OG Image Upload ────────────────────────────────────────────────────────

    public function test_og_image_upload_saves_path(): void
    {
        $file = UploadedFile::fake()->image('og.jpg', 1200, 630);

        $this->actingAs($this->admin)
            ->put("/admin/weddings/{$this->wedding->id}", [
                'groom_name' => 'Andi',
                'bride_name' => 'Sari',
                'og_image'   => $file,
            ]);

        $this->wedding->refresh();
        $this->assertNotNull($this->wedding->og_image);
        Storage::disk('public')->assertExists($this->wedding->og_image);
    }

    public function test_favicon_upload_saves_path(): void
    {
        $file = UploadedFile::fake()->create('favicon.png', 10, 'image/png');

        $this->actingAs($this->admin)
            ->put("/admin/weddings/{$this->wedding->id}", [
                'groom_name' => 'Andi',
                'bride_name' => 'Sari',
                'favicon'    => $file,
            ]);

        $this->wedding->refresh();
        $this->assertNotNull($this->wedding->favicon);
    }

    // ── CSV Import Async ───────────────────────────────────────────────────────

    public function test_small_csv_import_is_sync(): void
    {
        Queue::fake();

        $csv = "name,phone,email,max_pax\n";
        for ($i = 1; $i <= 10; $i++) {
            $csv .= "Tamu {$i},0812345678{$i},,1\n";
        }

        $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

        $this->actingAs($this->admin)
            ->post("/admin/weddings/{$this->wedding->id}/guests/import", ['file' => $file]);

        Queue::assertNotPushed(ImportGuestsCsv::class);
        $this->assertDatabaseCount('guests', 10);
    }

    public function test_large_csv_import_dispatches_to_queue(): void
    {
        Queue::fake();

        $csv = "name,phone,email,max_pax\n";
        for ($i = 1; $i <= 150; $i++) {
            $csv .= "Tamu {$i},08123456{$i},,1\n";
        }

        $file = UploadedFile::fake()->createWithContent('guests.csv', $csv);

        $this->actingAs($this->admin)
            ->post("/admin/weddings/{$this->wedding->id}/guests/import", ['file' => $file]);

        Queue::assertPushed(ImportGuestsCsv::class);
    }

    // ── Wedding creation ───────────────────────────────────────────────────────

    private function superAdmin(): User
    {
        return User::create([
            'name'      => 'Root',
            'email'     => 'root@test.com',
            'password'  => bcrypt('pass'),
            'client_id' => null,
            'role'      => 'super_admin',
            'is_active' => true,
        ]);
    }

    public function test_super_admin_must_choose_a_client_when_creating_a_wedding(): void
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/weddings', ['groom_name' => 'Tanpa', 'bride_name' => 'Client'])
            ->assertSessionHasErrors('client_id');

        $this->assertDatabaseMissing('weddings', ['groom_name' => 'Tanpa']);
    }

    public function test_super_admin_can_create_a_wedding_for_a_client(): void
    {
        $admin = $this->superAdmin();
        $client = Client::first();

        $this->actingAs($admin)
            ->get('/admin/weddings/create')
            ->assertOk()
            ->assertSee('name="client_id"', false);

        $this->actingAs($admin)
            ->post('/admin/weddings', [
                'groom_name' => 'Dengan',
                'bride_name' => 'Client',
                'client_id'  => $client->id,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('weddings', [
            'groom_name' => 'Dengan',
            'client_id'  => $client->id,
        ]);
    }
}
