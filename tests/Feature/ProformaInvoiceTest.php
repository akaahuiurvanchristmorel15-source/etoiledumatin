<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookPart;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use App\Services\ProformaInvoicePdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProformaInvoiceTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->customer = User::where('role', 'customer')->firstOrFail();
    }

    public function test_owner_downloads_a_pdf_for_an_order_mixing_pdf_paper_and_audio(): void
    {
        $order = $this->makeOrder();

        $response = $this->actingAs($this->customer)->get(route('orders.proforma', $order));

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition', 'attachment; filename="facture-proforma-'.$order->order_number.'.pdf"');

        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_strangers_cannot_download_the_invoice(): void
    {
        $order = $this->makeOrder();

        $this->get(route('orders.proforma', $order))->assertForbidden();

        $stranger = User::factory()->create();
        $this->actingAs($stranger)->get(route('orders.proforma', $order))->assertForbidden();
    }

    public function test_guest_who_placed_the_order_and_staff_can_download(): void
    {
        $order = $this->makeOrder();

        $this->withSession([OrderService::SESSION_ACCESS_KEY => [$order->id]])
            ->get(route('orders.proforma', $order))
            ->assertOk();

        $admin = User::where('role', 'super_admin')->firstOrFail();
        $this->actingAs($admin)->get(route('orders.proforma', $order))->assertOk();
    }

    public function test_very_long_orders_continue_on_a_new_page(): void
    {
        $order = $this->makeOrder(itemCount: 40);

        $pdf = new ProformaInvoicePdf($order->load(['items.book', 'items.part', 'shippingAddress']));
        $pdf->render();

        $this->assertGreaterThan(1, $pdf->PageNo());
    }

    public function test_the_document_number_is_derived_from_the_order(): void
    {
        $order = $this->makeOrder();
        $order->order_number = 'EDM-20261102-A1B2C3';

        $this->assertSame('PF-20261102-A1B2C3', (new ProformaInvoicePdf($order))->number());
    }

    public function test_order_pages_link_to_the_invoice(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->customer)->get(route('checkout.confirmation', $order))
            ->assertOk()->assertSee(route('orders.proforma', $order));

        $this->actingAs($this->customer)->get(route('library.index'))
            ->assertOk()->assertSee(route('orders.proforma', $order));

        $admin = User::where('role', 'super_admin')->firstOrFail();
        $this->actingAs($admin)->get(route('admin.orders.show', $order))
            ->assertOk()->assertSee(route('orders.proforma', $order));
    }

    private function makeOrder(int $itemCount = 1): Order
    {
        $book = Book::firstOrFail();
        $part = BookPart::create(['book_id' => $book->id, 'position' => 1, 'title' => 'Première partie']);

        $order = Order::create([
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'customer_email' => $this->customer->email,
            'customer_phone' => '+225 07 00 00 00 00',
            'subtotal_pdf' => 5000,
            'subtotal_physical' => 12000,
            'subtotal_audio' => 3000,
            'shipping_cost' => 2000,
            'total_amount' => 22000,
            'currency' => 'FCFA',
            'payment_status' => 'paid',
            'payment_method' => 'paystack',
            'payment_reference' => 'REF-TEST-1',
            'paid_at' => now(),
        ]);

        $order->items()->create(['book_id' => $book->id, 'format' => 'pdf', 'quantity' => 1, 'unit_price' => 5000, 'total_price' => 5000]);
        $order->items()->create(['book_id' => $book->id, 'format' => 'physical', 'quantity' => 2, 'unit_price' => 6000, 'total_price' => 12000]);
        $order->items()->create(['book_id' => $book->id, 'book_part_id' => $part->id, 'format' => 'audio', 'access_days' => 30, 'quantity' => 1, 'unit_price' => 3000, 'total_price' => 3000]);

        for ($index = 1; $index < $itemCount; $index++) {
            $order->items()->create(['book_id' => $book->id, 'format' => 'pdf', 'quantity' => 1, 'unit_price' => 100, 'total_price' => 100]);
        }

        $order->shippingAddress()->create([
            'recipient_name' => $this->customer->name,
            'phone' => '+225 07 00 00 00 00',
            'delivery_zone' => 'abidjan',
            'address_line' => 'Cocody, Riviera 3',
            'city' => 'Abidjan',
            'country' => "Côte d'Ivoire",
            'status' => 'pending',
        ]);

        return $order;
    }
}
