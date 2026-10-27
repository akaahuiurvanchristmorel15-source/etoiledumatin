<?php

namespace Tests\Feature;

use App\Models\AudioSubscription;
use App\Models\AudioTrack;
use App\Models\Book;
use App\Models\BookPart;
use App\Models\Order;
use App\Models\User;
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

        $this->actingAs($reader)->get(route('library.listen.stream', $track))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->travel(8)->days();

        $this->actingAs($reader)->get(route('library.listen.stream', $track))->assertForbidden();
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
