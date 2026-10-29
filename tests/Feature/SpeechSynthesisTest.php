<?php

namespace Tests\Feature;

use App\Models\AudioSynthesis;
use App\Models\AudioTrack;
use App\Models\Book;
use App\Models\BookPart;
use App\Models\User;
use App\Services\SpeechSynthesisService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SpeechSynthesisTest extends TestCase
{
    use RefreshDatabase;

    private BookPart $part;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('local');
        config(['services.openai.key' => 'sk-test', 'audio.synthesis.chunk_length' => 60]);

        $this->part = BookPart::factory()->for(Book::firstOrFail())->create(['position' => 1]);
        $this->manager = User::where('role', 'manager')->firstOrFail();
    }

    public function test_french_text_is_read_in_chunks_and_becomes_the_part_track(): void
    {
        Http::fake(['api.openai.com/*' => Http::sequence()->push('MP3-A')->push('MP3-B')]);
        $oldTrack = AudioTrack::factory()->for($this->part, 'part')->create(['language' => 'fr']);

        $this->actingAs($this->manager)
            ->put(route('admin.parts.synthesis.update', [$this->part, 'fr']), [
                'text' => 'Au commencement était la parole des anciens. Elle passait de bouche à oreille.',
                'generate' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        Http::assertSentCount(2);
        Http::assertSent(fn (Request $request) => $request->hasHeader('Authorization', 'Bearer sk-test')
            && $request['voice'] === config('audio.synthesis.voice')
            && str_contains($request['instructions'], 'français'));

        $track = $this->part->tracks()->where('language', 'fr')->sole();
        $this->assertNotEquals($oldTrack->id, $track->id);
        $this->assertSame('MP3-AMP3-B', Storage::disk('local')->get($track->path));
        $this->assertSame('done', AudioSynthesis::where('book_part_id', $this->part->id)->value('status'));
        $this->assertSame('Au commencement était la parole des anciens. Elle passait de bouche à oreille.', $this->part->fresh()->text('fr'));
    }

    public function test_api_failure_keeps_the_existing_track_and_reports_the_error(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['error' => ['message' => 'Clé invalide']], 401)]);
        $existingTrack = AudioTrack::factory()->for($this->part, 'part')->create(['language' => 'en']);

        $this->actingAs($this->manager)
            ->put(route('admin.parts.synthesis.update', [$this->part, 'en']), ['text' => 'In the beginning was the word.', 'generate' => '1']);

        $this->assertModelExists($existingTrack);
        $synthesis = AudioSynthesis::where('book_part_id', $this->part->id)->sole();
        $this->assertSame('failed', $synthesis->status);
        $this->assertStringContainsString('401', $synthesis->error);
        $this->assertSame([], Storage::disk('local')->allFiles(config('audio.directory')));
    }

    public function test_saving_text_alone_does_not_call_the_api(): void
    {
        Http::fake();

        $this->actingAs($this->manager)
            ->put(route('admin.parts.synthesis.update', [$this->part, 'fr']), ['text' => 'Premier chapitre.'])
            ->assertSessionHas('success');

        Http::assertNothingSent();
        $this->assertSame('Premier chapitre.', $this->part->fresh()->text('fr'));
    }

    public function test_generation_is_refused_without_api_key_or_for_national_languages(): void
    {
        Http::fake();
        config(['services.openai.key' => null]);

        $this->actingAs($this->manager)
            ->put(route('admin.parts.synthesis.update', [$this->part, 'fr']), ['text' => 'Premier chapitre.', 'generate' => '1'])
            ->assertSessionHas('error');

        $this->actingAs($this->manager)
            ->put(route('admin.parts.synthesis.update', [$this->part, 'baoule']), ['text' => 'Texte.', 'generate' => '1'])
            ->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_long_sentences_are_split_between_words_under_the_limit(): void
    {
        $chunks = app(SpeechSynthesisService::class)->chunks(str_repeat('mot ', 40).'Fin.');

        $this->assertGreaterThan(1, count($chunks));
        $this->assertTrue(collect($chunks)->every(fn (string $chunk) => mb_strlen($chunk) <= 60));
        $this->assertSame(trim(str_repeat('mot ', 40)).' Fin.', implode(' ', $chunks));
    }

    public function test_edit_page_shows_the_synthesis_forms(): void
    {
        $this->actingAs($this->manager)
            ->get(route('admin.books.edit', $this->part->book))
            ->assertOk()
            ->assertSee('Voix de synthèse (Français, Anglais)')
            ->assertSee('Générer la voix');
    }
}
