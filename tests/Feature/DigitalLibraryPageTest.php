<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DigitalLibraryPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the digital library page loads successfully with all expected components.
     */
    public function test_digital_library_page_renders_successfully(): void
    {
        $response = $this->get(route('books.index'));

        $response->assertStatus(200);
        $response->assertSee('BROKS');
        $response->assertSee('The Last Thing He Told Me');
        $response->assertSee('Laura Dave');
        $response->assertSee('Stephen King');
        $response->assertSee('78 books');
        $response->assertSee('False Witness');
        $response->assertSee('Karin Slaughter');
        $response->assertSee('Recent Bestsellers');

        // Verify the 5 bestsellers
        $response->assertSee('Left to Fear');
        $response->assertSee('Adele Parks');
        $response->assertSee('Malibu Rising');
        $response->assertSee('Taylor Jenkins Reid');
        $response->assertSee('Black Ice');
        $response->assertSee('Brad Thor');
        $response->assertSee('Blind Tiger');
        $response->assertSee('Sandra Brown');

        // Verify assets and components
        $response->assertSee('css/books.css');
        $response->assertSee('js/books.js');
        $response->assertSee('book-search-input');
        $response->assertSee('audio-play-btn');
    }

    /**
     * Test that original store pages still render properly without breaking.
     */
    public function test_existing_store_homepage_and_catalog_remain_functional(): void
    {
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('BROKS');
        $homeResponse->assertSee('The Last Thing He Told Me');

        $catalogResponse = $this->get(route('catalog.index'));
        $catalogResponse->assertStatus(200);
        $catalogResponse->assertSee('NSOROMMA', false);
        $catalogResponse->assertSee('Catalogue des Livres', false);
    }
}
