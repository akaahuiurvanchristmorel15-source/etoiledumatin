<?php

namespace Tests\Feature;

use App\Models\AudioSubscription;
use App\Models\AudioTrack;
use App\Models\Book;
use App\Models\BookPart;
use App\Models\ListeningSession;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\AudioStreamService;
use App\Services\AudioSubscriptionService;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AudioSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    private Book $book;

    private BookPart $part;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('local');

        $this->book = Book::where('is_audio_available', true)->firstOrFail();
        $this->part = BookPart::factory()->for($this->book)->create(['position' => 1, 'title' => 'Les origines']);

        Storage::disk('local')->put('audio-tracks/partie-1-fr.mp3', 'ID3-fake-audio');
        AudioTrack::factory()->for($this->part, 'part')->create(['language' => 'fr', 'path' => 'audio-tracks/partie-1-fr.mp3']);
        AudioTrack::factory()->for($this->part, 'part')->create(['language' => 'baoule']);
    }

    public function test_book_page_offers_each_part_with_its_plans_and_languages(): void
    {
        $this->get(route('catalog.show', $this->book->slug))
            ->assertOk()
            ->assertSee('Écouter ce livre')
            ->assertSee('Les origines')
            ->assertSee('Français · Baoulé')
            ->assertSee('1 mois — 1 500 FCFA');
    }

    public function test_guest_must_log_in_before_subscribing(): void
    {
        $this->post(route('cart.add'), $this->subscriptionPayload(30))
            ->assertRedirect(route('login'));

        $this->assertEmpty(session('letoile_cart'));
    }

    public function test_reader_pays_for_a_part_and_gets_listening_access_for_the_chosen_duration(): void
    {
        $reader = $this->reader();

        $this->actingAs($reader)->post(route('cart.add'), $this->subscriptionPayload(30))->assertRedirect(route('cart.index'));
        $this->actingAs($reader)->get(route('cart.index'))->assertSee('Écoute audio · Partie 1 · 1 mois');

        $this->actingAs($reader)->post(route('checkout.process'), [
            'name' => $reader->name,
            'email' => $reader->email,
            'phone' => '+225 07 00 00 00 00',
            'payment_channel' => 'paystack_momo',
        ])->assertRedirect();

        $order = Order::where('user_id', $reader->id)->latest('id')->firstOrFail();
        $this->assertSame(1500, $order->total_amount);
        $this->assertSame(1500, $order->subtotal_audio);
        $this->assertNull($order->shippingAddress);

        app(OrderService::class)->completeOrderPayment($order, 'PAY_AUDIO_SUB', 'paystack_momo');

        $subscription = AudioSubscription::where('user_id', $reader->id)->firstOrFail();
        $this->assertSame($this->part->id, $subscription->book_part_id);
        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $subscription->expires_at->timestamp, 5);
        $this->assertTrue($reader->canListenTo($this->part));
    }

    public function test_renewing_before_expiry_extends_the_current_access(): void
    {
        $reader = $this->reader();
        $service = app(AudioSubscriptionService::class);

        $first = $service->grant($reader, $this->part, 30);
        $renewal = $service->grant($reader, $this->part, 7);

        $this->assertEquals($first->expires_at, $renewal->starts_at);
        $this->assertEquals($first->expires_at->copy()->addDays(7), $service->accessEndsAt($reader, $this->part));
    }

    public function test_subscriber_streams_the_track_and_others_are_refused(): void
    {
        $reader = $this->reader();
        $track = $this->part->tracks()->where('language', 'fr')->firstOrFail();

        $this->actingAs($reader)->get(route('library.listen.stream', $track))->assertForbidden();

        app(AudioSubscriptionService::class)->grant($reader, $this->part, 7);
        $this->actingAs($reader)->postJson(route('library.listen.open'))->assertOk();

        $this->actingAs($reader)->get(route('library.listen.stream', $track))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->travel(8)->days();

        $this->actingAs($reader)->get(route('library.listen.stream', $track))->assertForbidden();
    }

    public function test_each_plan_includes_its_listening_hours_and_renewals_add_up(): void
    {
        $reader = $this->reader();
        $service = app(AudioSubscriptionService::class);

        $this->assertSame(30 * 3600, $service->grant($reader, $this->part, 30)->listening_seconds);
        $service->grant($reader, $this->part, 7);

        $this->assertSame(40 * 3600, $service->remainingSeconds($reader, $this->part));

        $this->get(route('catalog.show', $this->book->slug))->assertSee('1 mois — 1 500 FCFA · 30 h d’écoute');
    }

    public function test_a_plan_without_hours_is_unlimited(): void
    {
        Setting::put('content.audio_plan_hours_30', '');

        $subscription = app(AudioSubscriptionService::class)->grant($this->reader(), $this->part, 30);

        $this->assertNull($subscription->listening_seconds);
        $this->assertNull(app(AudioSubscriptionService::class)->remainingSeconds($this->reader(), $this->part));
    }

    public function test_listening_time_is_counted_until_the_quota_runs_out(): void
    {
        $reader = $this->reader();
        $track = $this->part->tracks()->where('language', 'fr')->firstOrFail();
        app(AudioSubscriptionService::class)->grant($reader, $this->part, 7)->update(['listening_seconds' => 50]);

        $this->actingAs($reader)->postJson(route('library.listen.open'))->assertOk();

        $this->travel(20)->seconds();
        $this->actingAs($reader)->postJson(route('library.listen.heartbeat', $track))
            ->assertOk()
            ->assertJson(['remaining_seconds' => 30]);

        // Une longue pause n'est pas facturée en entier : 30 s au plus par signal
        $this->travel(10)->minutes();
        $this->actingAs($reader)->postJson(route('library.listen.heartbeat', $track))
            ->assertForbidden()
            ->assertJson(['reason' => 'quota', 'remaining_seconds' => 0]);

        $this->assertFalse($reader->canListenTo($this->part));
        $this->actingAs($reader)->get(route('library.listen.stream', $track))->assertForbidden();
        $this->actingAs($reader)->get(route('library.listen', $this->book))->assertSee('Temps d’écoute épuisé');
    }

    public function test_subscriptions_bought_before_quotas_stay_unlimited(): void
    {
        $reader = $this->reader();
        $track = $this->part->tracks()->where('language', 'fr')->firstOrFail();
        $subscription = app(AudioSubscriptionService::class)->grant($reader, $this->part, 7);
        $subscription->update(['listening_seconds' => null]);

        $this->actingAs($reader)->postJson(route('library.listen.open'));
        $this->travel(20)->seconds();
        $this->actingAs($reader)->postJson(route('library.listen.heartbeat', $track))->assertOk()->assertJson(['remaining_seconds' => null]);

        $this->assertSame(0, $subscription->fresh()->listened_seconds);
    }

    public function test_a_third_device_stops_the_oldest_one(): void
    {
        $reader = $this->reader();
        $track = $this->part->tracks()->where('language', 'fr')->firstOrFail();
        app(AudioSubscriptionService::class)->grant($reader, $this->part, 30);

        $this->actingAs($reader)->postJson(route('library.listen.open'))->assertOk();
        $firstDevice = session()->all();

        foreach (['deuxième', 'troisième'] as $device) {
            $this->flushSession();
            $this->travel(1)->seconds();
            $this->actingAs($reader)->postJson(route('library.listen.open'))->assertOk();
        }

        $this->assertSame(2, ListeningSession::where('user_id', $reader->id)->whereNull('ended_at')->count());

        $this->flushSession();
        $this->withSession($firstDevice);

        $this->actingAs($reader)->postJson(route('library.listen.heartbeat', $track))
            ->assertStatus(409)
            ->assertJson(['reason' => 'replaced']);
        $this->actingAs($reader)->get(route('library.listen.stream', $track))->assertForbidden();

        // Relancer la lecture reprend la main : c'est alors l'appareil suivant qui est coupé
        $this->actingAs($reader)->postJson(route('library.listen.open'))->assertOk();
        $this->actingAs($reader)->get(route('library.listen.stream', $track))->assertOk();
        $this->assertSame(2, ListeningSession::where('user_id', $reader->id)->whereNull('ended_at')->count());
    }

    public function test_track_is_served_in_small_chunks_to_the_player_only(): void
    {
        $reader = $this->reader();
        $track = $this->part->tracks()->where('language', 'fr')->firstOrFail();
        Storage::disk('local')->put($track->path, str_repeat('a', 3 * 1048576));
        app(AudioSubscriptionService::class)->grant($reader, $this->part, 30);

        // Sans lecteur ouvert sur cet appareil, rien n'est servi
        $this->actingAs($reader)->get(route('library.listen.stream', $track))->assertForbidden();

        $this->actingAs($reader)->postJson(route('library.listen.open'));

        $this->actingAs($reader)->get(route('library.listen.stream', $track), ['Range' => 'bytes=0-'])
            ->assertStatus(206)
            ->assertHeader('Content-Range', 'bytes 0-1048575/3145728')
            ->assertHeader('Content-Length', '1048576');

        // Une ouverture directe de l'adresse (ou « Enregistrer sous ») est refusée
        $this->actingAs($reader)->get(route('library.listen.stream', $track), ['Sec-Fetch-Dest' => 'document'])->assertForbidden();
        $this->actingAs($reader)->get(route('library.listen.stream', $track), ['Sec-Fetch-Dest' => 'audio', 'Range' => 'bytes=2097152-'])
            ->assertStatus(206)
            ->assertHeader('Content-Range', 'bytes 2097152-3145727/3145728');
    }

    public function test_the_track_cannot_be_pulled_much_faster_than_it_is_listened(): void
    {
        $reader = $this->reader();
        $track = $this->part->tracks()->where('language', 'fr')->firstOrFail();
        Storage::disk('local')->put($track->path, str_repeat('a', 3 * 1048576));
        app(AudioSubscriptionService::class)->grant($reader, $this->part, 30);
        $this->actingAs($reader)->postJson(route('library.listen.open'));

        $statuses = collect(range(1, 30))->map(fn () => $this->actingAs($reader)
            ->get(route('library.listen.stream', $track), ['Range' => 'bytes=0-'])
            ->getStatusCode());

        $this->assertSame(206, $statuses->first());
        $this->assertContains(429, $statuses->all());

        // La réserve se reconstitue au rythme de l'écoute
        $this->travel(1)->minutes();
        $this->actingAs($reader)->get(route('library.listen.stream', $track), ['Range' => 'bytes=0-'])->assertStatus(206);
    }

    public function test_the_bitrate_is_read_from_the_mp3_header(): void
    {
        $track = $this->part->tracks()->where('language', 'fr')->firstOrFail();
        $id3Tag = 'ID3'."\x04\x00\x00\x00\x00\x00\x05".'abcde';
        Storage::disk('local')->put($track->path, $id3Tag."\xFF\xFB\x90\x44".str_repeat("\x00", 400));

        $this->assertSame(16000, app(AudioStreamService::class)->bytesPerSecond($track));
    }

    public function test_listening_page_unlocks_only_subscribed_parts(): void
    {
        $reader = $this->reader();
        $otherPart = BookPart::factory()->for($this->book)->create(['position' => 2, 'title' => 'Le retour']);
        AudioTrack::factory()->for($otherPart, 'part')->create(['language' => 'fr']);
        app(AudioSubscriptionService::class)->grant($reader, $this->part, 30);

        $this->actingAs($reader)->get(route('library.listen', $this->book))
            ->assertOk()
            ->assertSee('Les origines')
            ->assertSee('Le retour')
            ->assertViewHas('unlockedTracks', fn ($tracks) => $tracks->keys()->all() === [$this->part->id]
                && $tracks[$this->part->id]->keys()->sort()->values()->all() === ['baoule', 'fr']);

        $this->actingAs($reader)->get(route('library.index'))
            ->assertOk()
            ->assertSee('1 partie en écoute');
    }

    public function test_manager_splits_a_book_into_parts_and_uploads_a_track_per_language(): void
    {
        $manager = User::where('role', 'manager')->firstOrFail();

        $this->actingAs($manager)
            ->post(route('admin.books.parts.store', $this->book), ['position' => 2, 'title' => 'Deuxième partie'])
            ->assertRedirect();

        $part = $this->book->parts()->where('position', 2)->firstOrFail();

        $this->actingAs($manager)
            ->post(route('admin.parts.tracks.store', $part), [
                'language' => 'dida',
                'audio_file' => UploadedFile::fake()->create('partie-2-dida.mp3', 500, 'audio/mpeg'),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $track = $part->tracks()->where('language', 'dida')->firstOrFail();
        Storage::disk('local')->assertExists($track->path);

        $this->actingAs($manager)->delete(route('admin.parts.destroy', $part))->assertRedirect();

        $this->assertModelMissing($part);
        Storage::disk('local')->assertMissing($track->path);
    }

    public function test_admin_sets_plan_prices_and_empty_plans_are_not_sold(): void
    {
        $this->book->update(['audio_plan_prices' => [7 => 0, 30 => 2000, 90 => 0, 365 => 0]]);

        $this->assertSame([30 => 2000], $this->book->fresh()->audioPlans());

        $this->actingAs($this->reader())
            ->post(route('cart.add'), $this->subscriptionPayload(7))
            ->assertSessionHas('error');
    }

    private function reader(): User
    {
        return User::where('email', 'lecteur@gmail.com')->firstOrFail();
    }

    /**
     * @return array<string, int|string>
     */
    private function subscriptionPayload(int $days): array
    {
        return [
            'book_id' => $this->book->id,
            'format' => 'audio',
            'book_part_id' => $this->part->id,
            'access_days' => $days,
        ];
    }
}
