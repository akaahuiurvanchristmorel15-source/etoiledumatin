<?php

use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\StockController as AdminStockController;
use App\Http\Controllers\Admin\TeamController as AdminTeamController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Publiques
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('seo.robots');
Route::get('/books', [HomeController::class, 'index'])->name('books.index');
Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/recherche/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions')->middleware('throttle:60,1');
Route::get('/livre/{slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/livre/{book}/apercu-pdf', [CatalogController::class, 'previewPdf'])->name('catalog.preview-pdf');
Route::post('/livre/{book}/avis', [CatalogController::class, 'addReview'])->name('books.review')->middleware(['verified.account', 'throttle:reviews']);
Route::get('/suivi-commande', [LibraryController::class, 'guestLookup'])->name('orders.lookup')->middleware('throttle:10,1');

/*
|--------------------------------------------------------------------------
| Gestion du Panier (Mixte PDF + Physique)
|--------------------------------------------------------------------------
*/
Route::prefix('panier')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/ajouter', [CartController::class, 'add'])->name('add');
    Route::match(['post', 'patch'], '/modifier', [CartController::class, 'update'])->name('update');
    Route::match(['post', 'delete'], '/supprimer', [CartController::class, 'remove'])->name('remove');
    Route::post('/zone-livraison', [CartController::class, 'setZone'])->name('zone');
    Route::post('/vider', [CartController::class, 'clear'])->name('clear');
});

/*
|--------------------------------------------------------------------------
| Tunnel de Commande & Paiement Paystack
|--------------------------------------------------------------------------
*/
Route::prefix('commander')->name('checkout.')->middleware('verified.account')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/traiter', [CheckoutController::class, 'process'])->name('process')->middleware('throttle:checkout');
    Route::get('/retour-paystack', [CheckoutController::class, 'callback'])->name('callback');
    Route::get('/confirmation/{order}', [CheckoutController::class, 'confirmation'])->name('confirmation');
    Route::get('/simulation/{order}', [CheckoutController::class, 'sandboxPayment'])->name('sandbox-payment');
    Route::post('/simulation/{order}', [CheckoutController::class, 'sandboxConfirm'])->name('sandbox-confirm');
});

// Webhook Paystack (exempt de CSRF dans bootstrap/app.php)
Route::post('/paystack/webhook', [CheckoutController::class, 'webhook'])->name('paystack.webhook');

/*
|--------------------------------------------------------------------------
| Espace Client & Téléchargements Sécurisés
|--------------------------------------------------------------------------
*/
Route::middleware('verified.account')->group(function () {
    Route::get('/ma-bibliotheque', [LibraryController::class, 'index'])->name('library.index');
    Route::get('/ma-bibliotheque/generer-lien/{download}', [LibraryController::class, 'generateDownloadLink'])->name('library.download');
});

// Téléchargement sécurisé signé (expirant et protégé par filigrane FPDI)
Route::get('/telechargement/securise/{token}', [DownloadController::class, 'process'])
    ->name('downloads.process')
    ->middleware('signed');

/*
|--------------------------------------------------------------------------
| Authentification
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/connexion', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/inscription', [AuthController::class, 'register'])->middleware('throttle:register');

    Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'email'])->name('password.email')->middleware('throttle:password-reset');
    Route::get('/reinitialiser-mot-de-passe/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [PasswordResetController::class, 'update'])->name('password.update')->middleware('throttle:password-reset');

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback')->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    Route::get('/email/verification', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verification/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify')->middleware(['signed', 'throttle:6,1']);
    Route::post('/email/verification/renvoyer', [EmailVerificationController::class, 'send'])->name('verification.send')->middleware('throttle:3,1');
});
Route::get('/admin/connexion', [AuthController::class, 'showAdminLogin'])->name('admin.login');
Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Back-Office Administrateur & Gestion
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Catalogue : livres, catégories & avis (gestionnaires et administrateurs)
    Route::middleware('can:manage-catalog')->group(function () {
        Route::resource('livres', AdminBookController::class)
            ->parameters(['livres' => 'book'])
            ->names([
                'index' => 'books.index',
                'create' => 'books.create',
                'store' => 'books.store',
                'edit' => 'books.edit',
                'update' => 'books.update',
                'destroy' => 'books.destroy',
            ])
            ->except(['show']);

        Route::resource('categories', AdminCategoryController::class)->except(['show']);

        Route::get('/stock', [AdminStockController::class, 'index'])->name('stock.index');
        Route::patch('/stock/{book}', [AdminStockController::class, 'update'])->name('stock.update');

        Route::get('/avis', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::post('/avis/{review}/approuver', [AdminReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('/avis/{review}/masquer', [AdminReviewController::class, 'hide'])->name('reviews.hide');
        Route::delete('/avis/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    });

    // Commandes & Expéditions (logistique et administrateurs)
    Route::middleware('can:manage-orders')->group(function () {
        Route::get('/commandes', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/commandes/export/csv', [AdminOrderController::class, 'exportCsv'])->name('orders.export-csv');
        Route::get('/commandes/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/commandes/{order}/statut-expedition', [AdminOrderController::class, 'updateShipping'])->name('orders.update-shipping');
    });

    // Paramètres : profil pour tout le personnel, boutique & équipe pour les administrateurs
    Route::get('/parametres', [AdminSettingsController::class, 'profile'])->name('settings.profile');
    Route::put('/parametres/profil', [AdminSettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/parametres/mot-de-passe', [AdminSettingsController::class, 'updatePassword'])->name('settings.password.update');

    Route::middleware('can:manage-store')->group(function () {
        Route::get('/parametres/livraison', [AdminSettingsController::class, 'shipping'])->name('settings.shipping');
        Route::put('/parametres/livraison', [AdminSettingsController::class, 'updateShipping'])->name('settings.shipping.update');

        Route::get('/parametres/equipe', [AdminTeamController::class, 'index'])->name('settings.team');
        Route::post('/parametres/equipe', [AdminTeamController::class, 'store'])->name('settings.team.store');
        Route::put('/parametres/equipe/{member}', [AdminTeamController::class, 'update'])->name('settings.team.update');
        Route::delete('/parametres/equipe/{member}', [AdminTeamController::class, 'destroy'])->name('settings.team.destroy');
    });
});
