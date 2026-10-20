<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalLibraryPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_shows_an_empty_state_without_books(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Les étagères se remplissent bientôt.')
            ->assertSee('css/home.css');
    }

    public function test_homepage_displays_one_shelf_per_category_with_its_books(): void
    {
        $this->seed();

        $category = Category::whereHas('books', fn ($query) => $query->active())->firstOrFail();
        $book = $category->books()->active()->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Nouveautés')
            ->assertSee($category->name)
            ->assertSee(route('catalog.index', ['category' => $category->slug]), false)
            ->assertSee(route('catalog.show', $book->slug), false)
            ->assertSee('Nos collections');
    }

    public function test_homepage_spotlights_featured_books(): void
    {
        $this->seed();

        $featured = Book::active()->featured()->firstOrFail();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee($featured->title)
            ->assertSee($featured->author)
            ->assertSee('Découvrir le livre')
            ->assertSee(route('cart.add'), false);
    }

    public function test_hidden_books_are_not_placed_on_shelves(): void
    {
        $this->seed();

        $book = Book::firstOrFail();
        $book->update(['is_active' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee(route('catalog.show', $book->slug), false);
    }

    public function test_catalog_page_remains_functional(): void
    {
        $this->seed();

        $this->get(route('catalog.index'))
            ->assertOk()
            ->assertSee('Tous les livres');
    }
}
