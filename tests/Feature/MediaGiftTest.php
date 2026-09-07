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
use App\Services\GuestVisibilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaGiftTest extends TestCase
{
    use RefreshDatabase;

    private Wedding $wedding;
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $client = Client::create(['name' => 'T', 'email' => 't@t.com', 'status' => 'active']);
        $template = Template::create(['key' => 'test', 'name' => 'Test', 'is_active' => true, 'sort_order' => 1]);

        $this->wedding = Wedding::create([
            'client_id' => $client->id, 'template_id' => $template->id,
            'public_id' => 'INV-MDIA01', 'slug' => 'media-test',
            'title' => 'Media Test', 'groom_name' => 'A', 'bride_name' => 'B', 'status' => 'draft',
        ]);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('pass'),
            'client_id' => $client->id, 'role' => 'client_admin', 'is_active' => true,
        ]);
    }

    // ── Media ──────────────────────────────────────────────────────────────────

    public function test_valid_image_upload_succeeds(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg', 800, 600);

        $response = $this->actingAs($this->admin)
            ->post("/admin/weddings/{$this->wedding->id}/media", [
                'file'       => $file,
                'collection' => 'gallery',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('wedding_media', [
            'wedding_id' => $this->wedding->id,
            'collection' => 'gallery',
        ]);
    }

    public function test_non_image_file_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream');

        $response = $this->actingAs($this->admin)
            ->post("/admin/weddings/{$this->wedding->id}/media", [
                'file'       => $file,
                'collection' => 'gallery',
            ]);

        $response->assertSessionHasErrors('file');
        $this->assertDatabaseMissing('wedding_media', ['wedding_id' => $this->wedding->id]);
    }

    public function test_media_tenant_isolation(): void
    {
        $otherClient = Client::create(['name' => 'Other', 'email' => 'o@o.com', 'status' => 'active']);
        $otherAdmin = User::create([
            'name' => 'Other', 'email' => 'other@test.com', 'password' => bcrypt('pass'),
            'client_id' => $otherClient->id, 'role' => 'client_admin', 'is_active' => true,
        ]);

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->actingAs($otherAdmin)
            ->post("/admin/weddings/{$this->wedding->id}/media", [
                'file'       => $file,
                'collection' => 'gallery',
            ]);

        $response->assertForbidden();
    }

    // ── Gift Visibility ────────────────────────────────────────────────────────

    public function test_gift_visible_to_all_by_default(): void
    {
        $gift = GiftMethod::create([
            'wedding_id' => $this->wedding->id,
            'type' => 'bank_transfer', 'label' => 'BCA', 'is_active' => true, 'sort_order' => 1,
        ]);

        $guest = Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Test', 'max_pax' => 1]);

        $service = app(GuestVisibilityService::class);
        $this->assertTrue($service->isEntityVisible('gift_method', $gift->id, $this->wedding, $guest));
    }

    public function test_gift_hidden_from_category_by_rule(): void
    {
        $cat = GuestCategory::create(['wedding_id' => $this->wedding->id, 'name' => 'Rekan Kerja', 'sort_order' => 1]);
        $gift = GiftMethod::create([
            'wedding_id' => $this->wedding->id,
            'type' => 'bank_transfer', 'label' => 'BCA', 'is_active' => true, 'sort_order' => 1,
        ]);
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id, 'category_id' => $cat->id,
            'name' => 'Rekan', 'max_pax' => 1,
        ]);

        VisibilityRule::create([
            'wedding_id' => $this->wedding->id,
            'entity_type' => 'gift_method', 'entity_id' => $gift->id,
            'scope' => 'category', 'scope_id' => $cat->id, 'is_visible' => false,
        ]);

        $service = app(GuestVisibilityService::class);
        $this->assertFalse($service->isEntityVisible('gift_method', $gift->id, $this->wedding, $guest));
    }

    public function test_individual_override_beats_category_rule(): void
    {
        $cat = GuestCategory::create(['wedding_id' => $this->wedding->id, 'name' => 'VIP', 'sort_order' => 1]);
        $gift = GiftMethod::create([
            'wedding_id' => $this->wedding->id,
            'type' => 'qris', 'label' => 'QRIS', 'is_active' => true, 'sort_order' => 1,
        ]);
        $guest = Guest::create([
            'wedding_id' => $this->wedding->id, 'category_id' => $cat->id,
            'name' => 'VIP Guest', 'max_pax' => 1,
        ]);

        // Category rule: hide
        VisibilityRule::create([
            'wedding_id' => $this->wedding->id,
            'entity_type' => 'gift_method', 'entity_id' => $gift->id,
            'scope' => 'category', 'scope_id' => $cat->id, 'is_visible' => false,
        ]);

        // Individual override: show
        VisibilityRule::create([
            'wedding_id' => $this->wedding->id,
            'entity_type' => 'gift_method', 'entity_id' => $gift->id,
            'scope' => 'guest', 'scope_id' => $guest->id, 'is_visible' => true,
        ]);

        $service = app(GuestVisibilityService::class);
        $this->assertTrue($service->isEntityVisible('gift_method', $gift->id, $this->wedding, $guest));
    }
}
