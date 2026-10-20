<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\PurchasedDownload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_book_page_shows_formats_add_to_cart_and_review_form(): void
    {
        $book = Book::active()->where('is_pdf_available', true)->firstOrFail();

        $this->get(route('catalog.show', $book->slug))
            ->assertOk()
            ->assertSee($book->title)
            ->assertSee('e-Book PDF')
            ->assertSee($book->formatted_price_pdf)
            ->assertSee(route('cart.add'), false)
            ->assertSee(route('books.review', $book), false)
            ->assertSee('Avis des lecteurs');
    }

    public function test_out_of_stock_paper_format_cannot_be_selected(): void
    {
        $book = Book::active()->firstOrFail();
        $book->update(['is_physical_available' => true, 'stock_physical' => 0]);

        $response = $this->get(route('catalog.show', $book->slug))
            ->assertOk()
            ->assertSee('Rupture de stock');

        $this->assertMatchesRegularExpression('/value="physical"[^>]*\bdisabled\b/', $response->getContent());
    }

    public function test_checkout_form_sends_the_fields_expected_by_the_controller(): void
    {
        $book = Book::active()->where('is_physical_available', true)->where('stock_physical', '>', 0)->firstOrFail();
        $this->post(route('cart.add'), ['book_id' => $book->id, 'format' => 'physical']);

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('name="delivery_zone"', false)
            ->assertSee('value="abidjan"', false)
            ->assertSee('name="address_line"', false)
            ->assertSee('name="city"', false)
            ->assertSee('name="country"', false)
            ->assertSee('name="payment_channel"', false)
            ->assertDontSee('name="shipping_address"', false);
    }

    public function test_paper_order_can_be_placed_from_the_checkout_form(): void
    {
        $book = Book::active()->where('is_physical_available', true)->where('stock_physical', '>', 0)->firstOrFail();
        $this->post(route('cart.add'), ['book_id' => $book->id, 'format' => 'physical']);

        $this->post(route('checkout.process'), [
            'name' => 'Aya Kouassi',
            'email' => 'aya.kouassi@test.ci',
            'phone' => '+225 07 12 34 56 78',
            'delivery_zone' => 'interior',
            'address_line' => 'Quartier Commerce, près de la gare',
            'city' => 'Bouaké',
            'country' => "Côte d'Ivoire",
            'payment_channel' => 'paystack_momo',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('shipping_addresses', ['city' => 'Bouaké', 'delivery_zone' => 'interior']);
    }

    public function test_login_and_register_pages_render(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Connexion')->assertSee('name="remember"', false);
        $this->get(route('register'))->assertOk()->assertSee('Créer un compte')->assertSee('name="password_confirmation"', false);
    }

    public function test_library_invites_guests_to_sign_in(): void
    {
        $this->get(route('library.index'))
            ->assertOk()
            ->assertSee('Votre bibliothèque vous attend')
            ->assertSee(route('login'), false);
    }

    public function test_library_lists_the_customer_downloads_and_orders(): void
    {
        $download = PurchasedDownload::with('book', 'order')->whereNotNull('user_id')->firstOrFail();
        $customer = User::findOrFail($download->user_id);

        $this->actingAs($customer)
            ->get(route('library.index'))
            ->assertOk()
            ->assertSee($download->book->title)
            ->assertSee(route('library.download', $download), false)
            ->assertSee($download->order->order_number);
    }
}
