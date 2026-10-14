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
        $response->assertSee('BROKS');
        $response->assertSee('The Last Thing He Told Me');
        $response->assertSee('Recent Bestsellers');
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
        $cartPage->assertSee('e-Book Numérique (PDF)');
        $cartPage->assertSee('Livre Papier Broché');
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

    public function test_can_add_audio_book_to_cart(): void
    {
        $book = Book::where('is_audio_available', true)->first();
        $this->assertNotNull($book);

        // Ajout Audio
        $response = $this->post('/panier/ajouter', [
            'book_id' => $book->id,
            'format' => 'audio',
        ]);
        $response->assertRedirect('/panier');

        // Consultation du panier
        $cartPage = $this->get('/panier');
        $cartPage->assertStatus(200);
        $cartPage->assertSee($book->title);
        $cartPage->assertSee('Livre Audio MP3');
    }

    public function test_checkout_audio_only_creates_digital_order_without_shipping(): void
    {
        $book = Book::where('is_audio_available', true)->first();
        $this->assertNotNull($book);

        // Ajout audio uniquement
        $this->post('/panier/ajouter', [
            'book_id' => $book->id,
            'format' => 'audio',
        ]);

        $checkoutResponse = $this->post('/commander/traiter', [
            'name' => 'Fatou Koné',
            'email' => 'fatou.kone@test.ci',
            'phone' => '+225 05 99 88 77 66',
            'payment_channel' => 'paystack_momo',
        ]);

        $checkoutResponse->assertStatus(302);

        $order = Order::where('customer_email', 'fatou.kone@test.ci')->first();
        $this->assertNotNull($order);
        $this->assertTrue($order->hasAudioItems());
        $this->assertFalse($order->hasPhysicalItems());
        $this->assertEquals(0, $order->shipping_cost);
        $this->assertEquals($book->price_audio, $order->subtotal_audio);
        $this->assertEquals($book->price_audio, $order->total_amount);
        $this->assertNull($order->shippingAddress);

        // Simuler la complétion du paiement
        $orderService = app(\App\Services\OrderService::class);
        $orderService->completeOrderPayment($order, 'PAY_TEST_AUDIO_123', 'paystack_momo');

        $download = \App\Models\PurchasedDownload::where('order_id', $order->id)->first();
        $this->assertNotNull($download);
        $this->assertEquals('audio', $download->format);
        $this->assertTrue($download->isAudio());
    }
}
