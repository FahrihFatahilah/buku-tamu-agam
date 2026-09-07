<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Template;
use App\Models\User;
use App\Models\Wedding;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Client $clientA;
    private Client $clientB;
    private Wedding $weddingA;
    private Wedding $weddingB;
    private User $adminA;
    private User $adminB;
    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $template = Template::create(['key' => 'test', 'name' => 'Test', 'is_active' => true, 'sort_order' => 1]);

        $this->clientA = Client::create(['name' => 'A', 'email' => 'a@test.com', 'status' => 'active']);
        $this->clientB = Client::create(['name' => 'B', 'email' => 'b@test.com', 'status' => 'active']);

        $this->weddingA = Wedding::create([
            'client_id' => $this->clientA->id, 'template_id' => $template->id,
            'public_id' => 'INV-AUTHA1', 'slug' => 'auth-a', 'title' => 'A',
            'groom_name' => 'A', 'bride_name' => 'A', 'status' => 'published', 'published_at' => now(),
        ]);

        $this->weddingB = Wedding::create([
            'client_id' => $this->clientB->id, 'template_id' => $template->id,
            'public_id' => 'INV-AUTHB1', 'slug' => 'auth-b', 'title' => 'B',
            'groom_name' => 'B', 'bride_name' => 'B', 'status' => 'published', 'published_at' => now(),
        ]);

        $this->adminA = User::create([
            'name' => 'Admin A', 'email' => 'admina@test.com', 'password' => bcrypt('pass'),
            'client_id' => $this->clientA->id, 'role' => 'client_admin', 'is_active' => true,
        ]);

        $this->adminB = User::create([
            'name' => 'Admin B', 'email' => 'adminb@test.com', 'password' => bcrypt('pass'),
            'client_id' => $this->clientB->id, 'role' => 'client_admin', 'is_active' => true,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super', 'email' => 'super@test.com', 'password' => bcrypt('pass'),
            'role' => 'super_admin', 'is_active' => true,
        ]);
    }

    public function test_unauthenticated_cannot_access_admin(): void
    {
        $this->get('/admin/weddings')->assertRedirect('/login');
    }

    public function test_client_admin_cannot_edit_other_client_wedding(): void
    {
        $response = $this->actingAs($this->adminA)
            ->get("/admin/weddings/{$this->weddingB->id}/edit");

        $response->assertForbidden();
    }

    public function test_client_admin_can_edit_own_wedding(): void
    {
        $response = $this->actingAs($this->adminA)
            ->get("/admin/weddings/{$this->weddingA->id}/edit");

        $response->assertOk();
    }

    public function test_super_admin_can_edit_any_wedding(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get("/admin/weddings/{$this->weddingB->id}/edit");

        $response->assertOk();
    }

    public function test_client_admin_cannot_access_other_wedding_guests(): void
    {
        $response = $this->actingAs($this->adminA)
            ->get("/admin/weddings/{$this->weddingB->id}/guests");

        $response->assertForbidden();
    }

    public function test_tenant_isolation_guest_cannot_be_moved_to_other_wedding(): void
    {
        $guest = \App\Models\Guest::create([
            'wedding_id' => $this->weddingA->id,
            'name' => 'Test Guest', 'max_pax' => 1,
        ]);

        // Admin B tries to update a guest belonging to Wedding A
        $response = $this->actingAs($this->adminB)
            ->put("/admin/weddings/{$this->weddingB->id}/guests/{$guest->id}", [
                'name' => 'Hacked', 'max_pax' => 1,
            ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('guests', ['id' => $guest->id, 'name' => 'Test Guest']);
    }

    public function test_checkin_operator_cannot_access_admin_wedding_edit(): void
    {
        $operator = User::create([
            'name' => 'Op', 'email' => 'op@test.com', 'password' => bcrypt('pass'),
            'client_id' => $this->clientA->id, 'role' => 'checkin_operator', 'is_active' => true,
        ]);

        $response = $this->actingAs($operator)
            ->get("/admin/weddings/{$this->weddingA->id}/edit");

        $response->assertForbidden();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $inactive = User::create([
            'name' => 'Inactive', 'email' => 'inactive@test.com', 'password' => bcrypt('password'),
            'role' => 'client_admin', 'is_active' => false,
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@test.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
    }
}
