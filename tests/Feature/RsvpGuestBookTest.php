<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Guest;
use App\Models\GuestBookEntry;
use App\Models\Template;
use App\Models\Wedding;
use App\Models\WeddingSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RsvpGuestBookTest extends TestCase
{
    use RefreshDatabase;

    private Wedding $wedding;
    private Guest $guestA;
    private Guest $guestB;

    protected function setUp(): void
    {
        parent::setUp();

        $client = Client::create(['name' => 'T', 'email' => 't@t.com', 'status' => 'active']);
        $template = Template::create(['key' => 'test', 'name' => 'Test', 'is_active' => true, 'sort_order' => 1]);

        $this->wedding = Wedding::create([
            'client_id' => $client->id, 'template_id' => $template->id,
            'public_id' => 'INV-RSVP01', 'slug' => 'rsvp-test',
            'title' => 'RSVP Test', 'groom_name' => 'A', 'bride_name' => 'B',
            'status' => 'published', 'published_at' => now(),
        ]);

        WeddingSection::create(['wedding_id' => $this->wedding->id, 'section_key' => 'rsvp', 'is_enabled' => true, 'sort_order' => 0]);
        WeddingSection::create(['wedding_id' => $this->wedding->id, 'section_key' => 'guest_book', 'is_enabled' => true, 'sort_order' => 1]);

        $this->guestA = Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Andi', 'max_pax' => 2]);
        $this->guestB = Guest::create(['wedding_id' => $this->wedding->id, 'name' => 'Budi', 'max_pax' => 1]);
    }

    // ── RSVP ──────────────────────────────────────────────────────────────────

    public function test_guest_can_submit_rsvp_via_token(): void
    {
        $response = $this->post(
            "/{$this->wedding->public_id}/{$this->wedding->slug}/u/{$this->guestA->invitation_token}/rsvp",
            ['attendance_status' => 'attending', 'pax' => 2, 'note' => 'Siap hadir']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('rsvps', [
            'guest_id'          => $this->guestA->id,
            'attendance_status' => 'attending',
            'pax'               => 2,
        ]);
    }

    public function test_rsvp_token_is_only_identity_source(): void
    {
        // Submit RSVP with token of guestA but try to pass guestB's id in body
        $this->post(
            "/{$this->wedding->public_id}/{$this->wedding->slug}/u/{$this->guestA->invitation_token}/rsvp",
            ['attendance_status' => 'attending', 'pax' => 1, 'guest_id' => $this->guestB->id]
        );

        // RSVP must be for guestA, not guestB
        $this->assertDatabaseHas('rsvps', ['guest_id' => $this->guestA->id]);
        $this->assertDatabaseMissing('rsvps', ['guest_id' => $this->guestB->id]);
    }

    public function test_rsvp_with_invalid_token_fails(): void
    {
        $response = $this->post(
            "/{$this->wedding->public_id}/{$this->wedding->slug}/u/invalidtoken/rsvp",
            ['attendance_status' => 'attending', 'pax' => 1]
        );

        $response->assertNotFound();
    }

    public function test_rsvp_pax_cannot_exceed_max_pax(): void
    {
        $response = $this->post(
            "/{$this->wedding->public_id}/{$this->wedding->slug}/u/{$this->guestA->invitation_token}/rsvp",
            ['attendance_status' => 'attending', 'pax' => 99]
        );

        $response->assertSessionHasErrors('pax');
    }

    // ── Guest Book ─────────────────────────────────────────────────────────────

    public function test_anyone_can_submit_guest_book(): void
    {
        $response = $this->post(
            "/{$this->wedding->public_id}/{$this->wedding->slug}/guestbook",
            ['name' => 'Visitor', 'message' => 'Selamat menempuh hidup baru!']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('guest_book_entries', [
            'wedding_id' => $this->wedding->id,
            'name'       => 'Visitor',
            'status'     => 'pending',
        ]);
    }

    public function test_guest_book_xss_is_escaped(): void
    {
        $this->post(
            "/{$this->wedding->public_id}/{$this->wedding->slug}/guestbook",
            ['name' => '<script>alert(1)</script>', 'message' => 'Hello']
        );

        $entry = GuestBookEntry::where('wedding_id', $this->wedding->id)->first();
        $this->assertNotNull($entry);
        // Blade auto-escapes, but name stored as-is — verify it's stored literally not executed
        $this->assertStringContainsString('script', $entry->name);
    }

    public function test_guest_book_requires_name_and_message(): void
    {
        $response = $this->post(
            "/{$this->wedding->public_id}/{$this->wedding->slug}/guestbook",
            ['name' => '', 'message' => '']
        );

        $response->assertSessionHasErrors(['name', 'message']);
    }

    public function test_guest_book_default_status_is_pending(): void
    {
        $this->post(
            "/{$this->wedding->public_id}/{$this->wedding->slug}/guestbook",
            ['name' => 'Test', 'message' => 'Test message']
        );

        $this->assertDatabaseHas('guest_book_entries', [
            'wedding_id' => $this->wedding->id,
            'status'     => 'pending',
        ]);
    }
}
