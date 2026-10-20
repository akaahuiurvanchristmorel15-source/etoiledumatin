<?php

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use App\Services\Payment\PaystackPaymentGateway;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        $this->defineAdminAbilities();
        $this->applyShippingSettings();

        View::composer('layouts.admin', function ($view): void {
            $view->with('pendingReviewsCount', Gate::allows('manage-catalog') ? Review::where('is_approved', false)->count() : 0);
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
