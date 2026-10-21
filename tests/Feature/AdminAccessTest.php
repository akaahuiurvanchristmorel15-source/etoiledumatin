<?php

namespace Tests\Feature;

use App\Models\Book;
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
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_page_renders(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Connexion')
            ->assertSee('Administration')
            ->assertSee('action="'.route('login').'"', false);
    }

    public function test_logged_in_staff_skip_the_admin_login_page(): void
    {
        $admin = User::where('role', 'super_admin')->first();

        $this->actingAs($admin)->get(route('admin.login'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_staff_reach_the_requested_admin_page_after_signing_in(): void
    {
        $this->get(route('admin.orders.index'))->assertRedirect(route('admin.login'));

        $this->post(route('login'), ['email' => 'logistique@letoiledumatin.ci', 'password' => 'Logistique@123456'])
            ->assertRedirect(route('admin.orders.index'));
    }

    public function test_customer_signing_in_from_admin_is_sent_to_their_library(): void
    {
        $this->get(route('admin.dashboard'));

        $this->post(route('login'), ['email' => 'lecteur@gmail.com', 'password' => 'Lecteur@123456'])
            ->assertRedirect(route('library.index'));
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
        $dashboardResponse->assertSee('Ventes par format');

        $booksResponse = $this->actingAs($admin)->get('/admin/livres');
        $booksResponse->assertStatus(200);
        $booksResponse->assertSee('Ajouter un livre');

        $ordersResponse = $this->actingAs($admin)->get('/admin/commandes');
        $ordersResponse->assertStatus(200);
        $ordersResponse->assertSee('Paiements Paystack et suivi des expéditions.');

        // Test export CSV
        $csvResponse = $this->actingAs($admin)->get('/admin/commandes/export/csv');
        $csvResponse->assertStatus(200);
        $csvResponse->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_admin_edit_page_loads_the_selected_book(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $book = Book::first();

        $this->actingAs($admin)
            ->get(route('admin.books.edit', $book))
            ->assertOk()
            ->assertSee(route('admin.books.update', $book), false);
    }
}
