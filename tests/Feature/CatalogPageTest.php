<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_catalog_lists_active_books_with_collections(): void
    {
        $book = Book::active()->firstOrFail();
        $category = Category::firstOrFail();

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Tous les livres')
            ->assertSee($book->title)
            ->assertSee($category->name)
            ->assertSee(route('catalog.show', $book->slug), false);
    }

    public function test_category_filter_shows_the_category_as_title(): void
    {
        $category = Category::whereHas('books', fn ($query) => $query->active())->firstOrFail();

        $this->get(route('catalog.index', ['category' => $category->slug]))
            ->assertOk()
            ->assertSee('<h1 class="catalog-title">'.e($category->name).'</h1>', false);
    }

    public function test_price_filter_ignores_formats_that_are_not_for_sale(): void
    {
        $book = Book::active()->firstOrFail();
        $book->update([
            'is_pdf_available' => false,
            'price_pdf' => 100,
            'is_physical_available' => true,
            'price_physical' => 50000,
            'is_audio_available' => false,
        ]);

        $this->get(route('catalog.index', ['price_max' => 1000]))
            ->assertOk()
            ->assertDontSee(route('catalog.show', $book->slug), false);
    }

    public function test_search_without_results_shows_empty_state(): void
    {
        $this->get(route('catalog.index', ['search' => 'zzz-aucun-livre-zzz']))
            ->assertOk()
            ->assertSee('Aucun livre trouvé');
    }
}
