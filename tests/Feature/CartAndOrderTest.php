<?php

namespace Tests\Feature;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Book;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Mockery\MockInterface;
use Tests\TestCase;

class CartAndOrderTest extends TestCase
{
    use RefreshDatabase;

    private const REFERENCE = 'PAY_CART_REF';

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

    public function test_cart_is_kept_when_payment_service_is_unavailable(): void
    {
        $this->mock(PaymentGatewayInterface::class, function (MockInterface $gateway) {
            $gateway->shouldReceive('initializePayment')->andThrow(new \RuntimeException('Paystack indisponible'));
        });

        $this->addPdfToCart();
        $this->submitCheckout()->assertRedirect(route('cart.index'));

        $this->assertNotEmpty(session('letoile_cart'));
    }

    public function test_cart_is_kept_when_payment_is_refused(): void
    {
        $this->mockGateway(paymentSucceeds: false);

        $this->addPdfToCart();
        $this->submitCheckout();

        $this->get(route('checkout.callback', ['reference' => self::REFERENCE]))->assertRedirect(route('cart.index'));

        $this->assertNotEmpty(session('letoile_cart'));
        $this->assertSame('pending', Order::where('payment_reference', self::REFERENCE)->value('payment_status'));
    }

    public function test_cart_is_emptied_once_payment_is_validated_on_return(): void
    {
        $this->mockGateway(paymentSucceeds: true);

        $this->addPdfToCart();
        $this->submitCheckout();

        // Tant que le client n'est pas revenu de Paystack, le panier reste disponible
        $this->assertNotEmpty(session('letoile_cart'));

        $order = Order::where('payment_reference', self::REFERENCE)->firstOrFail();

        $this->get(route('checkout.callback', ['reference' => self::REFERENCE]))
            ->assertRedirect(route('checkout.confirmation', $order));

        $this->assertEmpty(session('letoile_cart'));
    }

    public function test_cart_is_emptied_when_webhook_validated_the_order_before_the_return(): void
    {
        $this->mockGateway(paymentSucceeds: true);

        $this->addPdfToCart();
        $this->submitCheckout();

        $order = Order::where('payment_reference', self::REFERENCE)->firstOrFail();
        app(OrderService::class)->completeOrderPayment($order, self::REFERENCE);

        $this->get(route('checkout.callback', ['reference' => self::REFERENCE]))
            ->assertRedirect(route('checkout.confirmation', $order));

        $this->assertEmpty(session('letoile_cart'));
    }

    public function test_returning_to_a_paid_order_keeps_a_cart_composed_since(): void
    {
        $this->mockGateway(paymentSucceeds: true);

        $this->addPdfToCart();
        $this->submitCheckout();
        $this->get(route('checkout.callback', ['reference' => self::REFERENCE]));

        $this->addPdfToCart();
        $this->get(route('checkout.callback', ['reference' => self::REFERENCE]));

        $this->assertNotEmpty(session('letoile_cart'));
    }

    public function test_cart_is_emptied_after_simulated_payment(): void
    {
        $this->addPdfToCart();
        $this->submitCheckout();

        $order = Order::where('customer_email', 'amadou.diallo@test.ci')->firstOrFail();

        $this->post(route('checkout.sandbox-confirm', $order), [
            'payment_channel' => 'Orange Money',
            'reference' => $order->payment_reference,
        ])->assertRedirect(route('checkout.confirmation', $order));

        $this->assertEmpty(session('letoile_cart'));
    }

    private function addPdfToCart(): void
    {
        $this->post(route('cart.add'), ['book_id' => Book::where('is_pdf_available', true)->firstOrFail()->id, 'format' => 'pdf']);
    }

    private function submitCheckout(): TestResponse
    {
        return $this->post(route('checkout.process'), [
            'name' => 'Amadou Diallo',
            'email' => 'amadou.diallo@test.ci',
            'phone' => '+225 07 11 22 33 44',
            'payment_channel' => 'paystack_momo',
        ]);
    }

    private function mockGateway(bool $paymentSucceeds): void
    {
        $this->mock(PaymentGatewayInterface::class, function (MockInterface $gateway) use ($paymentSucceeds) {
            $gateway->shouldReceive('initializePayment')->andReturn([
                'authorization_url' => 'https://checkout.paystack.test/session',
                'access_code' => 'code',
                'reference' => self::REFERENCE,
            ]);
            $gateway->shouldReceive('verifyTransaction')->andReturnUsing(fn () => [
                'success' => $paymentSucceeds,
                'reference' => self::REFERENCE,
                'amount' => (int) Order::where('payment_reference', self::REFERENCE)->value('total_amount'),
                'message' => $paymentSucceeds ? 'Approuvé' : 'Refusé',
                'raw_data' => ['channel' => 'mobile_money'],
            ]);
        });
    }
}
