<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_book_page_links_to_the_reader(): void
    {
        $book = Book::active()->where('is_pdf_available', true)->firstOrFail();

        $this->get(route('catalog.show', $book->slug))
            ->assertOk()
            ->assertSee(route('catalog.preview', $book->slug), false)
            ->assertDontSee(route('catalog.preview-pdf', $book), false);
    }

    public function test_reader_embeds_the_excerpt_and_offers_to_buy_the_ebook(): void
    {
        $book = Book::active()->where('is_pdf_available', true)->firstOrFail();

        $this->get(route('catalog.preview', $book->slug))
            ->assertOk()
            ->assertSee($book->title)
            ->assertSee('<iframe src="'.route('catalog.preview-pdf', $book), false)
            ->assertSee(route('cart.add'), false)
            ->assertSee('name="format" value="pdf"', false)
            ->assertSee($book->formatted_price_pdf)
            ->assertSee('<meta name="robots" content="noindex, follow">', false);
    }

    public function test_excerpt_pdf_is_generated_with_the_nsoromma_layout(): void
    {
        $book = Book::active()->where('is_pdf_available', true)->firstOrFail();

        $response = $this->get(route('catalog.preview-pdf', $book))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $content = $response->streamedContent();

        $this->assertStringStartsWith('%PDF', $content);
        $this->assertStringContainsString('NSOROMMA', $content);
        $this->assertStringNotContainsString('letoiledumatin', $content);
    }

    public function test_hidden_books_have_no_excerpt(): void
    {
        $book = Book::active()->where('is_pdf_available', true)->firstOrFail();
        $book->update(['is_active' => false]);

        $this->get(route('catalog.preview', $book->slug))->assertNotFound();
        $this->get(route('catalog.preview-pdf', $book))->assertNotFound();
    }

    public function test_books_without_pdf_have_no_excerpt(): void
    {
        $book = Book::active()->firstOrFail();
        $book->update(['is_pdf_available' => false]);

        $this->get(route('catalog.preview', $book->slug))->assertNotFound();
        $this->get(route('catalog.preview-pdf', $book))->assertNotFound();
    }
}
