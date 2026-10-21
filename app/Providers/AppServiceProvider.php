<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Book;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payment\PaystackPaymentGateway;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            PaymentGatewayInterface::class,
            PaystackPaymentGateway::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Hébergements mutualisés (MySQL/MariaDB anciens) : index limités à 1000 octets, soit 191 caractères en utf8mb4
        Schema::defaultStringLength(191);

        $this->defineAdminAbilities();
        $this->applyShippingSettings();
        $this->configureSecurity();

        View::composer('layouts.admin', function ($view): void {
            $canManageCatalog = Gate::allows('manage-catalog');

            $view->with([
                'pendingReviewsCount' => $canManageCatalog ? Review::where('is_approved', false)->count() : 0,
                'outOfStockCount' => $canManageCatalog ? Book::where('is_physical_available', true)->where('stock_physical', '<=', 0)->count() : 0,
            ]);
        });
    }

    /**
     * Droits du back-office par rôle :
     * catalogue (livres, catégories, avis) → manager, commandes → logistique, paramètres boutique & équipe → administrateur.
     */
    protected function defineAdminAbilities(): void
    {
        Gate::define('manage-catalog', fn (User $user): bool => $user->isManager());
        Gate::define('manage-orders', fn (User $user): bool => $user->isLogistics());
        Gate::define('manage-store', fn (User $user): bool => $user->isAdmin());
    }

    /**
     * Politique de mot de passe, limites de tentatives et e-mails de sécurité en français.
     */
    protected function configureSecurity(): void
    {
        // En production : 10 caractères, majuscules et minuscules, chiffres, et refus des mots de passe ayant fuité
        Password::defaults(fn () => $this->app->isProduction()
            ? Password::min(10)->letters()->mixedCase()->numbers()->uncompromised()
            : Password::min(8)->letters()->numbers());

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(30)->by($request->ip()));
        RateLimiter::for('register', fn (Request $request) => Limit::perHour(10)->by($request->ip()));
        RateLimiter::for('password-reset', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));
        RateLimiter::for('reviews', fn (Request $request) => Limit::perMinutes(10, 3)->by($request->user()?->id ?: $request->ip()));
        RateLimiter::for('checkout', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        VerifyEmail::toMailUsing(fn (object $notifiable, string $url) => (new MailMessage)
            ->subject('Confirmez votre adresse e-mail — NSOROMMA')
            ->greeting('Bienvenue sur NSOROMMA')
            ->line('Merci pour votre inscription. Confirmez votre adresse e-mail pour sécuriser votre compte.')
            ->action('Confirmer mon adresse', $url)
            ->line('Ce lien expire dans 60 minutes. Si vous n\'êtes pas à l\'origine de cette inscription, ignorez ce message.'));

        ResetPassword::toMailUsing(fn (object $notifiable, string $token) => (new MailMessage)
            ->subject('Réinitialisation de votre mot de passe — NSOROMMA')
            ->greeting('Bonjour')
            ->line('Vous avez demandé à réinitialiser le mot de passe de votre compte NSOROMMA.')
            ->action('Choisir un nouveau mot de passe', route('password.reset', ['token' => $token, 'email' => $notifiable->getEmailForPasswordReset()]))
            ->line('Ce lien expire dans 60 minutes. Si vous n\'avez rien demandé, aucune action n\'est nécessaire.'));
    }

    /**
     * Remplace les tarifs et délais de livraison par ceux saisis dans les paramètres.
     */
    protected function applyShippingSettings(): void
    {
        try {
            $settings = Setting::allValues();
        } catch (QueryException) {
            return;
        }

        foreach (array_keys(config('shipping.zones', [])) as $zone) {
            if (isset($settings["shipping.{$zone}.price"])) {
                config(["shipping.zones.{$zone}.price" => (int) $settings["shipping.{$zone}.price"]]);
            }

            if (isset($settings["shipping.{$zone}.delay"])) {
                config(["shipping.zones.{$zone}.delay" => $settings["shipping.{$zone}.delay"]]);
            }
        }
    }
}
