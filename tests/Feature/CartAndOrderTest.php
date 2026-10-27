<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartAndOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Nouveautés');
        $response->assertSee(Book::active()->latest()->first()->title);
    }

    public function test_catalog_displays_books_and_filters(): void
    {
        $response = $this->get('/catalogue');

        $response->assertStatus(200);
        $response->assertSee('Nations nègres et culture');

        // Test filtre PDF
        $pdfResponse = $this->get('/catalogue?format=pdf');
        $pdfResponse->assertStatus(200);
    }

    public function test_can_add_pdf_and_physical_books_to_cart(): void
    {
        $book = Book::first();

        // Ajout PDF
        $response = $this->post('/panier/ajouter', [
            'book_id' => $book->id,
            'format' => 'pdf',
        ]);
        $response->assertRedirect('/panier');

        // Ajout Physique
        $response2 = $this->post('/panier/ajouter', [
            'book_id' => $book->id,
            'format' => 'physical',
            'quantity' => 2,
        ]);
        $response2->assertRedirect('/panier');

        // Consultation du panier
        $cartPage = $this->get('/panier');
        $cartPage->assertStatus(200);
        $cartPage->assertSee($book->title);
        $cartPage->assertSee('e-Book PDF');
        $cartPage->assertSee('Livre papier');
    }

    public function test_checkout_creates_order_and_redirects_to_payment(): void
    {
        $book = Book::first();

        // Ajouter un livre papier et un livre PDF au panier
        $this->post('/panier/ajouter', ['book_id' => $book->id, 'format' => 'pdf']);
        $this->post('/panier/ajouter', ['book_id' => $book->id, 'format' => 'physical', 'quantity' => 1]);

        $checkoutResponse = $this->post('/commander/traiter', [
            'name' => 'Amadou Diallo',
            'email' => 'amadou.diallo@test.ci',
            'phone' => '+225 07 11 22 33 44',
            'payment_channel' => 'paystack_momo',
            'delivery_zone' => 'abidjan',
            'address_line' => 'Cocody Deux-Plateaux Vallons',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
        ]);

        // Doit rediriger vers l'URL d'autorisation de paiement (Paystack sandbox ou API)
        $checkoutResponse->assertStatus(302);

        // Vérifier que la commande existe en base de données
        $order = Order::where('customer_email', 'amadou.diallo@test.ci')->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertTrue($order->hasPhysicalItems());
        $this->assertTrue($order->hasPdfItems());
        $this->assertNotNull($order->shippingAddress);
        $this->assertEquals('Cocody Deux-Plateaux Vallons', $order->shippingAddress->address_line);
    }
}
