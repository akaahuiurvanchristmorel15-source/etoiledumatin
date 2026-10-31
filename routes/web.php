<?php

use App\Http\Controllers\Admin\AudioTrackController as AdminAudioTrackController;
use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\BookPartController as AdminBookPartController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\NewsController as AdminNewsController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\RoleController as AdminRoleController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\Admin\SpeechSynthesisController as AdminSpeechSynthesisController;
use App\Http\Controllers\Admin\StockController as AdminStockController;
use App\Http\Controllers\Admin\TeamController as AdminTeamController;
use App\Http\Controllers\AdminTwoFactorController;
use App\Http\Controllers\AudioListeningController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\NewsController;
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
Route::get('/livre/{slug}/extrait', [CatalogController::class, 'preview'])->name('catalog.preview');
Route::get('/livre/{book}/apercu-pdf', [CatalogController::class, 'previewPdf'])->name('catalog.preview-pdf')->middleware('throttle:downloads');
Route::post('/livre/{book}/avis', [CatalogController::class, 'addReview'])->name('books.review')->middleware(['verified.account', 'throttle:reviews']);
Route::get('/actualites', [NewsController::class, 'index'])->name('news.index');
Route::get('/actualites/{slug}', [NewsController::class, 'show'])->name('news.show');
Route::view('/mentions-legales', 'pages.legal.notice')->name('legal.notice');
Route::view('/confidentialite', 'pages.legal.privacy')->name('legal.privacy');
Route::view('/conditions-generales-de-vente', 'pages.legal.terms')->name('legal.terms');
Route::get('/suivi-commande', [LibraryController::class, 'guestLookup'])->name('orders.lookup')->middleware('throttle:10,1');

/*
|--------------------------------------------------------------------------
| Gestion du Panier (Mixte PDF + Physique)
|--------------------------------------------------------------------------
*/
Route::prefix('panier')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::middleware('throttle:cart')->group(function () {
        Route::post('/ajouter', [CartController::class, 'add'])->name('add');
        Route::match(['post', 'patch'], '/modifier', [CartController::class, 'update'])->name('update');
        Route::match(['post', 'delete'], '/supprimer', [CartController::class, 'remove'])->name('remove');
        Route::post('/zone-livraison', [CartController::class, 'setZone'])->name('zone');
        Route::post('/vider', [CartController::class, 'clear'])->name('clear');
    });
});

/*
|--------------------------------------------------------------------------
| Tunnel de Commande & Paiement Paystack
|--------------------------------------------------------------------------
*/
Route::prefix('commander')->name('checkout.')->middleware('verified.account')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/traiter', [CheckoutController::class, 'process'])->name('process')->middleware('throttle:checkout');
    Route::get('/retour-paystack', [CheckoutController::class, 'callback'])->name('callback')->middleware('throttle:payment-callback');
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
    Route::get('/ma-bibliotheque/generer-lien/{download}', [LibraryController::class, 'generateDownloadLink'])->name('library.download')->middleware('throttle:downloads');
});

// Écoute audio par abonnement (streaming réservé aux abonnés en cours)
Route::middleware(['auth', 'verified.account'])->group(function () {
    Route::get('/ma-bibliotheque/ecoute/{book:slug}', [AudioListeningController::class, 'show'])->name('library.listen');
    Route::get('/ma-bibliotheque/ecoute/piste/{track}', [AudioListeningController::class, 'stream'])->name('library.listen.stream')->middleware('throttle:audio-stream');
});

// Téléchargement sécurisé signé (expirant et protégé par filigrane FPDI)
Route::get('/telechargement/securise/{token}', [DownloadController::class, 'process'])
    ->name('downloads.process')
    ->middleware(['signed', 'throttle:downloads']);

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

    // Double authentification de l'équipe : code reçu par e-mail après le mot de passe
    Route::get('/admin/verification', [AdminTwoFactorController::class, 'show'])->name('admin.two-factor');
    Route::post('/admin/verification', [AdminTwoFactorController::class, 'verify'])->name('admin.two-factor.verify')->middleware('throttle:login');
    Route::post('/admin/verification/renvoyer', [AdminTwoFactorController::class, 'resend'])->name('admin.two-factor.resend')->middleware('throttle:password-reset');

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

    // Chaque groupe correspond à une permission attribuée aux rôles (Paramètres › Rôles et permissions)
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

        // Découpage en parties & pistes audio par langue
        Route::post('/livres/{book}/parties', [AdminBookPartController::class, 'store'])->name('books.parts.store');
        Route::put('/parties/{part}', [AdminBookPartController::class, 'update'])->name('parts.update');
        Route::delete('/parties/{part}', [AdminBookPartController::class, 'destroy'])->name('parts.destroy');
        Route::post('/parties/{part}/pistes', [AdminAudioTrackController::class, 'store'])->name('parts.tracks.store');
        Route::delete('/pistes/{track}', [AdminAudioTrackController::class, 'destroy'])->name('tracks.destroy');
        Route::put('/parties/{part}/texte/{language}', [AdminSpeechSynthesisController::class, 'update'])->name('parts.synthesis.update')->middleware('throttle:speech-synthesis');

        Route::resource('categories', AdminCategoryController::class)->except(['show']);
    });

    Route::middleware('can:manage-news')->group(function () {
        Route::resource('actualites', AdminNewsController::class)
            ->parameters(['actualites' => 'news'])
            ->names([
                'index' => 'news.index',
                'create' => 'news.create',
                'store' => 'news.store',
                'edit' => 'news.edit',
                'update' => 'news.update',
                'destroy' => 'news.destroy',
            ])
            ->except(['show']);
    });

    Route::middleware('can:manage-stock')->group(function () {
        Route::get('/stock', [AdminStockController::class, 'index'])->name('stock.index');
        Route::patch('/stock/{book}', [AdminStockController::class, 'update'])->name('stock.update');
    });

    Route::middleware('can:manage-reviews')->group(function () {
        Route::get('/avis', [AdminReviewController::class, 'index'])->name('reviews.index');
        Route::post('/avis/{review}/approuver', [AdminReviewController::class, 'approve'])->name('reviews.approve');
        Route::post('/avis/{review}/masquer', [AdminReviewController::class, 'hide'])->name('reviews.hide');
        Route::delete('/avis/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
    });

    Route::middleware('can:manage-orders')->group(function () {
        Route::get('/notifications/commandes', [AdminNotificationController::class, 'orders'])->name('notifications.orders');
        Route::get('/commandes', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/commandes/export/csv', [AdminOrderController::class, 'exportCsv'])->name('orders.export-csv')->middleware('throttle:exports');
        Route::get('/commandes/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/commandes/{order}/statut-expedition', [AdminOrderController::class, 'updateShipping'])->name('orders.update-shipping');
    });

    // Paramètres : « Mon compte » pour tout le personnel, le reste selon les permissions
    Route::get('/parametres', [AdminSettingsController::class, 'profile'])->name('settings.profile');
    Route::put('/parametres/profil', [AdminSettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/parametres/mot-de-passe', [AdminSettingsController::class, 'updatePassword'])->name('settings.password.update')->middleware('throttle:password-change');

    Route::middleware('can:manage-store')->group(function () {
        Route::get('/parametres/livraison', [AdminSettingsController::class, 'shipping'])->name('settings.shipping');
        Route::put('/parametres/livraison', [AdminSettingsController::class, 'updateShipping'])->name('settings.shipping.update');

        Route::get('/parametres/contenu', [AdminSettingsController::class, 'content'])->name('settings.content');
        Route::put('/parametres/contenu', [AdminSettingsController::class, 'updateContent'])->name('settings.content.update');

        Route::get('/parametres/maintenance', [AdminSettingsController::class, 'maintenance'])->name('settings.maintenance');
        Route::put('/parametres/maintenance', [AdminSettingsController::class, 'updateMaintenance'])->name('settings.maintenance.update');
    });

    Route::middleware('can:manage-team')->group(function () {
        Route::get('/parametres/roles', [AdminRoleController::class, 'index'])->name('settings.roles');
        Route::post('/parametres/roles', [AdminRoleController::class, 'store'])->name('settings.roles.store');
        Route::put('/parametres/roles/{role}', [AdminRoleController::class, 'update'])->name('settings.roles.update');
        Route::delete('/parametres/roles/{role}', [AdminRoleController::class, 'destroy'])->name('settings.roles.destroy');

        Route::get('/parametres/equipe', [AdminTeamController::class, 'index'])->name('settings.team');
        Route::post('/parametres/equipe', [AdminTeamController::class, 'store'])->name('settings.team.store')->middleware('throttle:team-invites');
        Route::put('/parametres/equipe/{member}', [AdminTeamController::class, 'update'])->name('settings.team.update');
        Route::delete('/parametres/equipe/{member}', [AdminTeamController::class, 'destroy'])->name('settings.team.destroy');
    });
});
