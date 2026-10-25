<?php

namespace Tests\Feature;

use App\Enums\NewsType;
use App\Models\Book;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class NewsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_news_page_lists_published_news_only(): void
    {
        $published = News::factory()->create(['title' => 'Rencontre avec nos auteurs']);
        $draft = News::factory()->draft()->create(['title' => 'Annonce encore secrète']);
        $scheduled = News::factory()->scheduled()->create(['title' => 'Annonce programmée']);

        $this->get(route('news.index'))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($scheduled->title)
            ->assertSee('<link rel="canonical" href="'.route('news.index').'">', false);
    }

    public function test_news_page_filters_by_type(): void
    {
        $conference = News::factory()->create(['type' => NewsType::Conference, 'title' => 'Conférence sur la pensée bantoue']);
        $release = News::factory()->create(['type' => NewsType::BookRelease, 'title' => 'Parution d\'un nouveau roman']);

        $this->get(route('news.index', ['type' => NewsType::Conference->value]))
            ->assertOk()
            ->assertSee($conference->title)
            ->assertDontSee($release->title);
    }

    public function test_news_page_is_paginated(): void
    {
        News::query()->delete();
        News::factory()->count(10)->create();
        $oldest = News::factory()->create(['title' => 'La plus ancienne actualité', 'published_at' => now()->subYear()]);

        $this->get(route('news.index'))->assertOk()->assertDontSee($oldest->title);
        $this->get(route('news.index', ['page' => 2]))->assertOk()->assertSee($oldest->title);
    }

    public function test_news_detail_shows_event_details_seo_tags_and_linked_book(): void
    {
        $book = Book::active()->firstOrFail();
        $article = News::factory()->event()->create([
            'title' => 'Dédicace au Salon du livre',
            'excerpt' => 'Venez rencontrer l\'auteur sur notre stand.',
            'location' => 'Abidjan, Parc des expositions',
            'book_id' => $book->id,
            'cover_image' => 'storage/news/salon.jpg',
        ]);

        $this->get(route('news.show', $article->slug))
            ->assertOk()
            ->assertSee($article->title)
            ->assertSee('Abidjan, Parc des expositions')
            ->assertSee(route('catalog.show', $book->slug), false)
            ->assertSee('<title>Dédicace au Salon du livre — Actualités NSOROMMA</title>', false)
            ->assertSee('<meta name="description" content="Venez rencontrer l&#039;auteur sur notre stand.">', false)
            ->assertSee('<link rel="canonical" href="'.route('news.show', $article->slug).'">', false)
            ->assertSee('<meta property="og:image" content="'.asset('storage/news/salon.jpg').'">', false);
    }

    public function test_draft_and_scheduled_news_details_are_not_found(): void
    {
        $this->get(route('news.show', News::factory()->draft()->create()->slug))->assertNotFound();
        $this->get(route('news.show', News::factory()->scheduled()->create()->slug))->assertNotFound();
    }

    public function test_home_page_shows_latest_news_and_navigation_link(): void
    {
        $article = News::factory()->create(['title' => 'Une actualité toute fraîche', 'published_at' => now()->subMinute()]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Dernières actualités')
            ->assertSee($article->title)
            ->assertSee(route('news.index'), false);
    }

    public function test_sitemap_lists_published_news_only(): void
    {
        $published = News::factory()->create();
        $draft = News::factory()->draft()->create();

        $this->get(route('seo.sitemap'))
            ->assertOk()
            ->assertSee('<loc>'.route('news.index').'</loc>', false)
            ->assertSee('<loc>'.route('news.show', $published->slug).'</loc>', false)
            ->assertDontSee(route('news.show', $draft->slug), false);
    }

    public function test_manager_creates_news_with_cover_image(): void
    {
        Storage::fake('public');

        $this->actingAs($this->userWithRole('manager'))
            ->post(route('admin.news.store'), $this->newsPayload([
                'cover_file' => UploadedFile::fake()->image('affiche.jpg'),
            ]))
            ->assertRedirect(route('admin.news.index'));

        $article = News::where('title', 'Conférence sur Cheikh Anta Diop')->firstOrFail();

        $this->assertSame('conference-sur-cheikh-anta-diop', $article->slug);
        $this->assertSame(NewsType::Conference, $article->type);
        $this->assertTrue($article->is_published);
        $this->assertNotNull($article->published_at);
        $this->assertSame('2026-12-12 18:30', $article->event_date->format('Y-m-d H:i'));
        $this->assertStringStartsWith('storage/news/', $article->cover_image);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $article->cover_image));

        $this->get(route('news.show', $article->slug))->assertOk();
    }

    public function test_news_saved_unchecked_stays_a_draft(): void
    {
        $this->actingAs($this->userWithRole('manager'))
            ->post(route('admin.news.store'), $this->newsPayload(['is_published' => null]))
            ->assertRedirect(route('admin.news.index'));

        $article = News::where('title', 'Conférence sur Cheikh Anta Diop')->firstOrFail();

        $this->assertFalse($article->is_published);
        $this->assertNull($article->published_at);
        $this->get(route('news.show', $article->slug))->assertNotFound();
    }

    public function test_news_can_be_updated_and_its_previous_cover_replaced(): void
    {
        Storage::fake('public');
        $oldPath = UploadedFile::fake()->image('old.jpg')->store('news', 'public');
        $article = News::factory()->create(['cover_image' => 'storage/'.$oldPath]);

        $this->actingAs($this->userWithRole('super_admin'))
            ->put(route('admin.news.update', $article), $this->newsPayload([
                'title' => 'Titre mis à jour',
                'cover_file' => UploadedFile::fake()->image('new.jpg'),
            ]))
            ->assertRedirect(route('admin.news.edit', $article));

        $article->refresh();
        $this->assertSame('Titre mis à jour', $article->title);
        $this->assertSame('titre-mis-a-jour', $article->slug);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists(str_replace('storage/', '', $article->cover_image));
    }

    public function test_news_can_be_deleted(): void
    {
        $article = News::factory()->create();

        $this->actingAs($this->userWithRole('manager'))
            ->delete(route('admin.news.destroy', $article))
            ->assertRedirect(route('admin.news.index'));

        $this->assertModelMissing($article);
    }

    public function test_news_form_reports_validation_errors(): void
    {
        $this->actingAs($this->userWithRole('manager'))
            ->from(route('admin.news.create'))
            ->post(route('admin.news.store'), ['title' => '', 'type' => 'inconnu'])
            ->assertRedirect(route('admin.news.create'))
            ->assertSessionHasErrors(['title', 'type', 'excerpt', 'content']);
    }

    public function test_admin_news_pages_render_for_catalog_managers(): void
    {
        $manager = $this->userWithRole('manager');
        $article = News::factory()->event()->create();

        $this->actingAs($manager)->get(route('admin.news.index'))->assertOk()->assertSee($article->title);
        $this->actingAs($manager)->get(route('admin.news.create'))->assertOk();
        $this->actingAs($manager)->get(route('admin.news.edit', $article))->assertOk();
        $this->actingAs($manager)->get(route('admin.dashboard'))->assertOk()->assertSee(route('admin.news.index'), false);
    }

    public function test_staff_without_catalog_rights_cannot_manage_news(): void
    {
        $logistics = $this->userWithRole('logistics');
        $article = News::factory()->create();

        $this->actingAs($logistics)->get(route('admin.dashboard'))->assertOk()->assertDontSee(route('admin.news.index'), false);
        $this->actingAs($logistics)->get(route('admin.news.index'))->assertForbidden();
        $this->actingAs($logistics)->post(route('admin.news.store'), $this->newsPayload())->assertForbidden();
        $this->actingAs($logistics)->delete(route('admin.news.destroy', $article))->assertForbidden();

        $this->assertModelExists($article);
    }

    public function test_guests_and_customers_cannot_reach_news_admin(): void
    {
        $this->get(route('admin.news.index'))->assertRedirect(route('admin.login'));

        $customer = User::factory()->create();
        $this->actingAs($customer)->get(route('admin.news.index'))->assertForbidden();
        $this->actingAs($customer)->post(route('admin.news.store'), $this->newsPayload())->assertForbidden();

        $this->assertDatabaseMissing('news', ['title' => 'Conférence sur Cheikh Anta Diop']);
    }

    private function userWithRole(string $role): User
    {
        return User::where('role', $role)->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function newsPayload(array $overrides = []): array
    {
        return array_filter([
            'title' => 'Conférence sur Cheikh Anta Diop',
            'type' => NewsType::Conference->value,
            'excerpt' => 'Une soirée consacrée à l\'œuvre de l\'historien sénégalais.',
            'content' => "Premier paragraphe.\n\nSecond paragraphe.",
            'event_date' => '2026-12-12T18:30',
            'location' => 'Dakar, Université Cheikh Anta Diop',
            'is_published' => '1',
            ...$overrides,
        ], fn ($value) => $value !== null);
    }
}
