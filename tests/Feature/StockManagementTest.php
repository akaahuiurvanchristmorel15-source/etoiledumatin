<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_stock_label_reflects_the_remaining_quantity(): void
    {
        $book = new Book(['stock_physical' => 0]);
        $this->assertSame('Épuisé', $book->stockLabel());

        $book->stock_physical = 1;
        $this->assertSame('Plus que 1 exemplaire', $book->stockLabel());

        $book->stock_physical = 4;
        $this->assertSame('Plus que 4 exemplaires', $book->stockLabel());

        $book->stock_physical = 25;
        $this->assertSame('25 exemplaires en stock', $book->stockLabel());
    }

    public function test_readers_see_the_remaining_quantity_on_book_and_catalog_pages(): void
    {
        $book = $this->paperBook(12);

        $this->get(route('catalog.show', $book->slug))->assertOk()->assertSee('12 exemplaires en stock');
        $this->get(route('catalog.index'))->assertOk()->assertSee('Papier : 12 exemplaires en stock');
    }

    public function test_cart_shows_the_remaining_quantity_of_paper_books(): void
    {
        $book = $this->paperBook(3);
        $this->post(route('cart.add'), ['book_id' => $book->id, 'format' => 'physical']);

        $this->get(route('cart.index'))->assertOk()->assertSee('Plus que 3 exemplaires');
    }

    public function test_admin_stock_page_lists_paper_books_from_lowest_stock(): void
    {
        $low = $this->paperBook(0);

        $this->actingAs(User::where('role', 'manager')->firstOrFail())
            ->get(route('admin.stock.index'))
            ->assertOk()
            ->assertSee('Stock des livres papier')
            ->assertSeeInOrder([$low->title, 'Épuisé']);
    }

    public function test_admin_stock_filters_out_of_stock_books(): void
    {
        $out = $this->paperBook(0);
        $available = Book::where('is_physical_available', true)->whereKeyNot($out->id)->firstOrFail();
        $available->update(['stock_physical' => 40]);

        $this->actingAs(User::where('role', 'manager')->firstOrFail())
            ->get(route('admin.stock.index', ['level' => 'out']))
            ->assertOk()
            ->assertSee($out->title)
            ->assertDontSee($available->title);
    }

    public function test_manager_can_adjust_stock_and_it_is_logged(): void
    {
        $book = $this->paperBook(2);
        $manager = User::where('role', 'manager')->firstOrFail();

        $this->actingAs($manager)
            ->patch(route('admin.stock.update', $book), ['stock_physical' => 30])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(30, $book->fresh()->stock_physical);
        $this->assertDatabaseHas('audit_logs', ['user_id' => $manager->id, 'action' => 'stock_updated']);
    }

    public function test_stock_cannot_be_negative(): void
    {
        $book = $this->paperBook(2);

        $this->actingAs(User::where('role', 'manager')->firstOrFail())
            ->patch(route('admin.stock.update', $book), ['stock_physical' => -5])
            ->assertSessionHasErrors('stock_physical');

        $this->assertSame(2, $book->fresh()->stock_physical);
    }

    public function test_logistics_cannot_manage_stock(): void
    {
        $this->actingAs(User::where('role', 'logistics')->firstOrFail())
            ->get(route('admin.stock.index'))
            ->assertForbidden();
    }

    private function paperBook(int $stock): Book
    {
        $book = Book::active()->firstOrFail();
        $book->update(['is_physical_available' => true, 'stock_physical' => $stock]);

        return $book;
    }
}
