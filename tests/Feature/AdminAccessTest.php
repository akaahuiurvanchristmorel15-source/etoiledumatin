<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');
        $response->assertRedirect('/connexion');
    }

    public function test_customer_cannot_access_admin_dashboard(): void
    {
        $customer = User::where('role', 'customer')->first();

        $response = $this->actingAs($customer)->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_can_access_dashboard_and_manage_catalog(): void
    {
        $admin = User::where('role', 'super_admin')->first();

        $dashboardResponse = $this->actingAs($admin)->get('/admin');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Tableau de Bord & Indicateurs', false);

        $booksResponse = $this->actingAs($admin)->get('/admin/livres');
        $booksResponse->assertStatus(200);
        $booksResponse->assertSee('Catalogue des Livres');

        $ordersResponse = $this->actingAs($admin)->get('/admin/commandes');
        $ordersResponse->assertStatus(200);
        $ordersResponse->assertSee('Commandes & Expéditions');

        // Test export CSV
        $csvResponse = $this->actingAs($admin)->get('/admin/commandes/export/csv');
        $csvResponse->assertStatus(200);
        $csvResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
