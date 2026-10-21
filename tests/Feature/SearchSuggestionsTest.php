<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_suggestions_return_matching_books_by_title_or_author(): void
    {
        $book = Book::active()->firstOrFail();
        $term = mb_substr($book->title, 0, 30);

        $this->getJson(route('search.suggestions', ['q' => $term]))
            ->assertOk()
            ->assertJsonFragment(['title' => $book->title, 'url' => route('catalog.show', $book->slug)])
            ->assertJsonPath('catalogUrl', route('catalog.index', ['search' => $term]));

        $this->getJson(route('search.suggestions', ['q' => $book->author]))
            ->assertOk()
            ->assertJsonFragment(['author' => $book->author]);
    }

    public function test_suggestions_exclude_hidden_books(): void
    {
        $book = Book::active()->firstOrFail();
        $book->update(['is_active' => false]);

        $this->getJson(route('search.suggestions', ['q' => mb_substr($book->title, 0, 30)]))
            ->assertOk()
            ->assertJsonMissing(['url' => route('catalog.show', $book->slug)]);
    }

    public function test_suggestions_require_at_least_two_characters(): void
    {
        $this->getJson(route('search.suggestions', ['q' => 'a']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('q');
    }

    public function test_search_window_suggests_real_collections_and_authors(): void
    {
        $category = Category::whereHas('books', fn ($query) => $query->active())->firstOrFail();
        $author = Book::active()->select('author')->groupBy('author')->orderByRaw('COUNT(*) DESC')->orderBy('author')->value('author');

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('class="search-panel"', false)
            ->assertSee(str_replace('/', '\/', route('search.suggestions')), false)
            ->assertSee(route('catalog.index', ['category' => $category->slug]), false)
            ->assertSee(route('catalog.index', ['author' => $author]), false)
            ->assertDontSee('Recherches suggérées');
    }
}
