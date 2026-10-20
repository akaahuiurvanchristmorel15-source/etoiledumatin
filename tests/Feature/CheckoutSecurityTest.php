<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PurchasedDownload;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_sandbox_confirmation_is_unavailable_when_real_keys_are_configured(): void
    {
        $this->useRealPaystackKeys();
        $order = $this->createPendingPdfOrder();

        $this->withSession($this->accessTo($order))
            ->post(route('checkout.sandbox-confirm', $order), [
                'payment_channel' => 'Wave',
                'reference' => $order->payment_reference,
            ])
            ->assertNotFound();

        $this->assertFalse($order->fresh()->isPaid());
    }

    public function test_sandbox_confirmation_is_forbidden_for_another_visitor(): void
    {
        $order = $this->createPendingPdfOrder();

        $this->post(route('checkout.sandbox-confirm', $order), [
            'payment_channel' => 'Wave',
            'reference' => $order->payment_reference,
        ])->assertForbidden();

        $this->assertFalse($order->fresh()->isPaid());
    }

    public function test_sandbox_confirmation_rejects_a_reference_that_does_not_belong_to_the_order(): void
    {
        $order = $this->createPendingPdfOrder();

        $this->withSession($this->accessTo($order))
            ->post(route('checkout.sandbox-confirm', $order), [
                'payment_channel' => 'Wave',
                'reference' => 'SANDBOX_FORGED',
            ])
            ->assertSessionHasErrors('reference');

        $this->assertFalse($order->fresh()->isPaid());
    }

    public function test_callback_ignores_order_id_and_does_not_trust_forged_sandbox_reference(): void
    {
        $this->useRealPaystackKeys();
        Http::fake(['*/transaction/verify/*' => Http::response(['status' => false, 'message' => 'Transaction reference not found'], 400)]);
        $order = $this->createPendingPdfOrder();

        $this->get(route('checkout.callback', ['order_id' => $order->id, 'reference' => 'SANDBOX_FORGED']))
            ->assertRedirect(route('home'));

        $this->assertFalse($order->fresh()->isPaid());
    }

    public function test_callback_rejects_an_amount_lower_than_the_order_total(): void
    {
        $this->useRealPaystackKeys();
        $order = $this->createPendingPdfOrder();
        $this->fakeVerifiedPayment($order->total_amount - 1);

        $this->get(route('checkout.callback', ['reference' => $order->payment_reference]))
            ->assertRedirect(route('cart.index'));

        $this->assertFalse($order->fresh()->isPaid());
    }

    public function test_callback_completes_order_when_the_full_amount_is_paid(): void
    {
        $this->useRealPaystackKeys();
        $order = $this->createPendingPdfOrder();
        $this->fakeVerifiedPayment($order->total_amount);

        $this->get(route('checkout.callback', ['reference' => $order->payment_reference]))
            ->assertRedirect(route('checkout.confirmation', $order));

        $this->assertTrue($order->fresh()->isPaid());
    }

    public function test_webhook_rejects_unsigned_payload_when_real_keys_are_configured(): void
    {
        $this->useRealPaystackKeys();
        $order = $this->createPendingPdfOrder();

        $this->postJson(route('paystack.webhook'), [
            'event' => 'charge.success',
            'data' => ['reference' => $order->payment_reference, 'amount' => $order->total_amount * 100],
        ])->assertStatus(400);

        $this->assertFalse($order->fresh()->isPaid());
    }

    public function test_webhook_does_not_complete_an_underpaid_order(): void
    {
        $order = $this->createPendingPdfOrder();

        $this->postJson(route('paystack.webhook'), [
            'event' => 'charge.success',
            'data' => ['reference' => $order->payment_reference, 'amount' => 100],
        ])->assertOk();

        $this->assertFalse($order->fresh()->isPaid());
    }

    public function test_confirmation_page_is_forbidden_for_a_stranger(): void
    {
        $order = Order::where('order_number', 'EDM-2026-DEMO1')->firstOrFail();

        $this->get(route('checkout.confirmation', $order))->assertForbidden();
    }

    public function test_confirmation_page_is_visible_to_the_owner(): void
    {
        $order = Order::where('order_number', 'EDM-2026-DEMO1')->firstOrFail();

        $this->actingAs($order->user)
            ->get(route('checkout.confirmation', $order))
            ->assertOk()
            ->assertSee($order->order_number);
    }

    public function test_download_link_is_forbidden_for_a_guest_stranger(): void
    {
        $download = PurchasedDownload::firstOrFail();

        $this->get(route('library.download', $download))->assertForbidden();
    }

    public function test_download_link_is_forbidden_for_another_customer(): void
    {
        $download = PurchasedDownload::firstOrFail();
        $otherCustomer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($otherCustomer)
            ->get(route('library.download', $download))
            ->assertForbidden();
    }

    public function test_guest_can_download_after_finding_their_order(): void
    {
        $download = PurchasedDownload::with('order')->firstOrFail();
        $order = $download->order;

        $this->get(route('orders.lookup', ['order_number' => $order->order_number, 'contact' => $order->customer_email]))
            ->assertOk()
            ->assertSee($order->order_number);

        $this->get(route('library.download', $download))
            ->assertRedirectContains('/telechargement/securise/');
    }

    public function test_guest_lookup_page_renders_without_search_parameters(): void
    {
        $this->get(route('orders.lookup'))
            ->assertOk()
            ->assertSee('Suivre une commande');
    }

    public function test_guest_lookup_with_wrong_contact_does_not_reveal_the_order(): void
    {
        $order = Order::where('order_number', 'EDM-2026-DEMO1')->firstOrFail();

        $this->get(route('orders.lookup', ['order_number' => $order->order_number, 'contact' => 'intrus@test.ci']))
            ->assertOk()
            ->assertDontSee('Commande '.$order->order_number);
    }

    public function test_deleting_a_book_keeps_customer_orders_and_licenses(): void
    {
        $download = PurchasedDownload::firstOrFail();
        $book = $download->book;
        $admin = User::where('role', 'super_admin')->firstOrFail();

        $this->actingAs($admin)->delete(route('admin.books.destroy', $book))->assertRedirect();

        $this->assertSoftDeleted($book);
        $this->assertDatabaseHas('purchased_downloads', ['id' => $download->id]);
        $this->assertDatabaseHas('order_items', ['book_id' => $book->id]);
        $this->assertSame($book->title, $download->fresh()->book->title);
    }

    private function createPendingPdfOrder(): Order
    {
        $book = Book::where('is_pdf_available', true)->firstOrFail();

        $order = Order::create([
            'customer_name' => 'Awa Traoré',
            'customer_email' => 'awa.traore@test.ci',
            'customer_phone' => '+225 07 00 00 00 00',
            'subtotal_pdf' => $book->price_pdf,
            'subtotal_physical' => 0,
            'shipping_cost' => 0,
            'total_amount' => $book->price_pdf,
            'payment_status' => 'pending',
            'payment_reference' => 'PAY_TEST_'.uniqid(),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'book_id' => $book->id,
            'format' => 'pdf',
            'quantity' => 1,
            'unit_price' => $book->price_pdf,
            'total_price' => $book->price_pdf,
        ]);

        return $order;
    }

    /**
     * @return array<string, array<int, int>>
     */
    private function accessTo(Order $order): array
    {
        return [OrderService::SESSION_ACCESS_KEY => [$order->id]];
    }

    private function useRealPaystackKeys(): void
    {
        config(['paystack.secret_key' => 'sk_test_0123456789abcdef', 'paystack.sandbox_mode' => true]);
    }

    private function fakeVerifiedPayment(int $amountInFcfa): void
    {
        Http::fake(['*/transaction/verify/*' => Http::response([
            'status' => true,
            'data' => [
                'status' => 'success',
                'amount' => $amountInFcfa * 100,
                'channel' => 'mobile_money',
                'gateway_response' => 'Approved',
            ],
        ])]);
    }
}
