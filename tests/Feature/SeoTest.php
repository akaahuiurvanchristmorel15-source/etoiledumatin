<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_sitemap_lists_public_pages_categories_and_books(): void
    {
        $book = Book::active()->firstOrFail();
        $category = Category::whereHas('books', fn ($query) => $query->active())->firstOrFail();

        $response = $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8');

        $locations = [];
        foreach (simplexml_load_string($response->getContent())->url as $url) {
            $locations[] = (string) $url->loc;
        }

        $this->assertContains(route('home'), $locations);
        $this->assertContains(route('catalog.index'), $locations);
        $this->assertContains(route('catalog.show', $book->slug), $locations);
        $this->assertContains(route('catalog.index', ['category' => $category->slug]), $locations);
    }

    public function test_sitemap_excludes_hidden_and_deleted_books(): void
    {
        [$hidden, $deleted] = Book::active()->take(2)->get();
        $hidden->update(['is_active' => false]);
        $deleted->delete();

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertDontSee(route('catalog.show', $hidden->slug), false)
            ->assertDontSee(route('catalog.show', $deleted->slug), false);
    }

    public function test_robots_blocks_everything_outside_production(): void
    {
        $this->get(route('seo.robots'))
            ->assertOk()
            ->assertSee('Disallow: /', false)
            ->assertDontSee('Sitemap:', false);
    }

    public function test_robots_hides_private_pages_and_points_to_the_sitemap_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->get(route('seo.robots'))
            ->assertOk()
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Disallow: /commander', false)
            ->assertSee('Sitemap: '.route('seo.sitemap'), false);
    }

    public function test_book_page_has_canonical_share_preview_and_book_structured_data(): void
    {
        $book = Book::active()->where('is_pdf_available', true)->firstOrFail();

        $content = $this->get(route('catalog.show', $book->slug))->assertOk()->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="'.route('catalog.show', $book->slug).'">', $content);
        $this->assertStringContainsString('<meta property="og:type" content="book">', $content);
        $this->assertStringContainsString('<meta name="robots" content="index, follow">', $content);

        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $content, $matches);
        $schema = json_decode($matches[1], true);

        $this->assertSame(['Book', 'Product'], $schema['@type']);
        $this->assertSame($book->title, $schema['name']);
        $this->assertSame($book->author, $schema['author']['name']);
        $this->assertSame('XOF', $schema['offers'][0]['priceCurrency']);
    }

    public function test_home_declares_the_bookstore_and_catalog_search(): void
    {
        $content = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"BookStore"', $content);
        $this->assertStringContainsString('search_term_string', $content);
    }

    public function test_private_pages_are_excluded_from_search_engines(): void
    {
        foreach ([route('cart.index'), route('login'), route('register'), route('orders.lookup')] as $url) {
            $this->get($url)->assertOk()->assertSee('<meta name="robots" content="noindex, nofollow">', false);
        }
    }

    public function test_filtered_catalog_pages_are_not_indexed_and_point_to_the_main_catalog(): void
    {
        $content = $this->get(route('catalog.index', ['search' => 'diop', 'sort' => 'author']))->assertOk()->getContent();

        $this->assertStringContainsString('<meta name="robots" content="noindex, follow">', $content);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('catalog.index').'">', $content);
    }
}
