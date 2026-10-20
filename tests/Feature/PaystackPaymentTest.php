<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchasedDownload;
use App\Models\ShippingAddress;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaystackPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_sandbox_payment_confirmation_fulfills_order_and_creates_pdf_license(): void
    {
        $book = Book::first();
        $initialStock = $book->stock_physical;

        $order = Order::create([
            'order_number' => 'EDM-TEST-001',
            'customer_name' => 'Fatou Bamba',
            'customer_email' => 'fatou.bamba@test.ci',
            'customer_phone' => '+225 05 00 00 00 00',
            'subtotal_pdf' => $book->price_pdf,
            'subtotal_physical' => $book->price_physical,
            'shipping_cost' => 1500,
            'total_amount' => $book->price_pdf + $book->price_physical + 1500,
            'payment_status' => 'pending',
            'payment_method' => 'paystack_momo',
            'payment_reference' => 'SANDBOX_REF_123',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'format' => 'pdf',
            'quantity' => 1,
            'unit_price' => $book->price_pdf,
            'total_price' => $book->price_pdf,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'format' => 'physical',
            'quantity' => 1,
            'unit_price' => $book->price_physical,
            'total_price' => $book->price_physical,
        ]);

        ShippingAddress::create([
            'order_id' => $order->id,
            'recipient_name' => 'Fatou Bamba',
            'phone' => '+225 05 00 00 00 00',
            'delivery_zone' => 'abidjan',
            'address_line' => 'Yopougon Selmer',
            'city' => 'Abidjan',
            'status' => 'pending',
        ]);

        // Simuler la validation du paiement via le formulaire sandbox
        $response = $this->withSession([OrderService::SESSION_ACCESS_KEY => [$order->id]])->post(route('checkout.sandbox-confirm', $order), [
            'payment_channel' => 'Orange Money',
            'reference' => 'SANDBOX_REF_123',
        ]);

        $response->assertRedirect(route('checkout.confirmation', $order));

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertNotNull($order->paid_at);

        // Vérifier décrémentation du stock physique
        $book->refresh();
        $this->assertEquals($initialStock - 1, $book->stock_physical);

        // Vérifier création de la licence PDF
        $download = PurchasedDownload::where('order_id', $order->id)->where('book_id', $book->id)->first();
        $this->assertNotNull($download);
        $this->assertEquals('fatou.bamba@test.ci', $download->customer_email);
        $this->assertTrue($download->canDownload());
    }

    public function test_paystack_webhook_validates_order(): void
    {
        $book = Book::first();

        $order = Order::create([
            'order_number' => 'EDM-TEST-WEBHOOK',
            'customer_name' => 'Kone Bakary',
            'customer_email' => 'kone@test.ci',
            'customer_phone' => '+225 01 02 03 04 05',
            'subtotal_pdf' => $book->price_pdf,
            'subtotal_physical' => 0,
            'shipping_cost' => 0,
            'total_amount' => $book->price_pdf,
            'payment_status' => 'pending',
            'payment_reference' => 'PAY_WEBHOOK_REF_999',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'format' => 'pdf',
            'quantity' => 1,
            'unit_price' => $book->price_pdf,
            'total_price' => $book->price_pdf,
        ]);

        $payload = [
            'event' => 'charge.success',
            'data' => [
                'reference' => 'PAY_WEBHOOK_REF_999',
                'amount' => $book->price_pdf * 100,
                'channel' => 'mobile_money',
                'status' => 'success',
            ],
        ];

        $response = $this->postJson(route('paystack.webhook'), $payload);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
        $this->assertDatabaseHas('purchased_downloads', [
            'order_id' => $order->id,
            'customer_email' => 'kone@test.ci',
        ]);
    }
}
