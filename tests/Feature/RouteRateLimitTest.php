<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RouteRateLimitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Routes sensibles et la limite de débit qui les protège.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function protectedRoutes(): array
    {
        return [
            'connexion' => ['login', 'throttle:login'],
            'mot de passe oublié' => ['password.email', 'throttle:password-reset'],
            'avis' => ['books.review', 'throttle:reviews'],
            'paiement' => ['checkout.process', 'throttle:checkout'],
            'retour Paystack' => ['checkout.callback', 'throttle:payment-callback'],
            'suivi de commande' => ['orders.lookup', 'throttle:10,1'],
            'ajout au panier' => ['cart.add', 'throttle:cart'],
            'vider le panier' => ['cart.clear', 'throttle:cart'],
            'aperçu PDF' => ['catalog.preview-pdf', 'throttle:downloads'],
            'lien de téléchargement' => ['library.download', 'throttle:downloads'],
            'téléchargement sécurisé' => ['downloads.process', 'throttle:downloads'],
            'écoute audio' => ['library.listen.stream', 'throttle:audio-stream'],
            'synthèse vocale' => ['admin.parts.synthesis.update', 'throttle:speech-synthesis'],
            'ajout de membre' => ['admin.settings.team.store', 'throttle:team-invites'],
            'export CSV' => ['admin.orders.export-csv', 'throttle:exports'],
            'mot de passe admin' => ['admin.settings.password.update', 'throttle:password-change'],
        ];
    }

    #[DataProvider('protectedRoutes')]
    public function test_sensitive_routes_are_rate_limited(string $routeName, string $throttle): void
    {
        $route = $routeName === 'login'
            ? Route::getRoutes()->match(request()->create('/connexion', 'POST'))
            : Route::getRoutes()->getByName($routeName);

        $this->assertContains($throttle, $route->gatherMiddleware());
    }

    public function test_cart_flooding_is_blocked_with_a_french_message(): void
    {
        $this->seed();
        $book = Book::firstOrFail();

        for ($attempt = 0; $attempt < 60; $attempt++) {
            $this->post(route('cart.add'), ['book_id' => $book->id, 'format' => 'pdf'])->assertRedirect();
        }

        $this->post(route('cart.add'), ['book_id' => $book->id, 'format' => 'pdf'])
            ->assertTooManyRequests()
            ->assertHeader('Retry-After')
            ->assertSee('Trop de requêtes');
    }
}
