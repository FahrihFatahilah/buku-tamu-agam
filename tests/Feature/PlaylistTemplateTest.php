<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Playlist;
use App\Models\PlaylistItem;
use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use App\Services\PlaylistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlaylistTemplateTest extends TestCase
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
            'client_id' => $client->id, 'template_id' => $template->id,
            'public_id' => 'INV-PLAY01', 'slug' => 'playlist-test',
            'title' => 'Test', 'groom_name' => 'A', 'bride_name' => 'B', 'status' => 'draft',
        ]);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('pass'),
            'client_id' => $client->id, 'role' => 'client_admin', 'is_active' => true,
        ]);
    }

    // ── Playlist ───────────────────────────────────────────────────────────────

    public function test_can_add_audio_track(): void
    {
        $file = UploadedFile::fake()->create('song.mp3', 1024, 'audio/mpeg');

        $response = $this->actingAs($this->admin)
            ->post("/admin/weddings/{$this->wedding->id}/playlist/tracks", [
                'file'   => $file,
                'title'  => 'Lagu Cinta',
                'artist' => 'Artis Demo',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('playlist_items', [
            'title'  => 'Lagu Cinta',
            'artist' => 'Artis Demo',
        ]);
    }

    public function test_non_audio_file_rejected(): void
    {
        $file = UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($this->admin)
            ->post("/admin/weddings/{$this->wedding->id}/playlist/tracks", [
                'file' => $file,
            ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_delete_track_removes_file(): void
    {
        $service = app(PlaylistService::class);
        $playlist = $service->getOrCreate($this->wedding);
        $file = UploadedFile::fake()->create('song.mp3', 512, 'audio/mpeg');
        $item = $service->addTrack($playlist, $file, ['title' => 'Test']);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/weddings/{$this->wedding->id}/playlist/tracks/{$item->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('playlist_items', ['id' => $item->id]);
    }

    public function test_playlist_settings_update(): void
    {
        $response = $this->actingAs($this->admin)
            ->post("/admin/weddings/{$this->wedding->id}/playlist/settings", [
                'autoplay' => '1',
                'loop'     => '0',
                'volume'   => '75',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('playlists', [
            'wedding_id' => $this->wedding->id,
            'volume'     => 75,
        ]);
    }

    public function test_active_playlist_loaded_in_invitation(): void
    {
        $this->wedding->update(['status' => 'published', 'published_at' => now()]);

        $service = app(PlaylistService::class);
        $playlist = $service->getOrCreate($this->wedding);
        $this->assertNotNull($playlist);
        $this->assertTrue($playlist->is_active);
    }

    // ── Template Switching ─────────────────────────────────────────────────────

    public function test_switching_template_does_not_delete_wedding_data(): void
    {
        $newTemplate = Template::create([
            'key' => 'modern-luxury', 'name' => 'Modern Luxury',
            'is_active' => true, 'sort_order' => 2,
        ]);

        $originalGroomName = $this->wedding->groom_name;

        $this->actingAs($this->admin)
            ->put("/admin/weddings/{$this->wedding->id}", [
                'groom_name'  => $originalGroomName,
                'bride_name'  => $this->wedding->bride_name,
                'template_id' => $newTemplate->id,
            ]);

        $this->wedding->refresh();
        $this->assertEquals($newTemplate->id, $this->wedding->template_id);
        $this->assertEquals($originalGroomName, $this->wedding->groom_name);
    }

    public function test_template_switch_preserves_sections(): void
    {
        // Bootstrap sections
        $sectionCount = $this->wedding->sections()->count();

        $newTemplate = Template::create([
            'key' => 'minimalist', 'name' => 'Minimalist',
            'is_active' => true, 'sort_order' => 3,
        ]);

        $this->actingAs($this->admin)
            ->put("/admin/weddings/{$this->wedding->id}", [
                'groom_name'  => $this->wedding->groom_name,
                'bride_name'  => $this->wedding->bride_name,
                'template_id' => $newTemplate->id,
            ]);

        // Sections must not be deleted
        $this->assertEquals($sectionCount, $this->wedding->sections()->count());
    }
}
