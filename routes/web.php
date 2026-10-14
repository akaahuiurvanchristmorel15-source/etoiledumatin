<?php

use App\Http\Controllers\Admin\BookController as AdminBookController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Routes Publiques
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/books', [HomeController::class, 'index'])->name('books.index');
Route::get('/catalogue', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/livre/{slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/livre/{book}/apercu-pdf', [CatalogController::class, 'previewPdf'])->name('catalog.preview-pdf');
Route::post('/livre/{book}/avis', [CatalogController::class, 'addReview'])->name('books.review');
Route::get('/suivi-commande', [LibraryController::class, 'guestLookup'])->name('orders.lookup');

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
Route::prefix('commander')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/traiter', [CheckoutController::class, 'process'])->name('process');
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
Route::get('/ma-bibliotheque', [LibraryController::class, 'index'])->name('library.index');
Route::get('/ma-bibliotheque/generer-lien/{download}', [LibraryController::class, 'generateDownloadLink'])->name('library.download');

// Téléchargement sécurisé signé (expirant et protégé par filigrane FPDI)
Route::get('/telechargement/securise/{token}', [DownloadController::class, 'process'])
    ->name('downloads.process')
    ->middleware('signed');

/*
|--------------------------------------------------------------------------
| Authentification
|--------------------------------------------------------------------------
*/
Route::get('/connexion', [AuthController::class, 'showLogin'])->name('login');
Route::post('/connexion', [AuthController::class, 'login']);
Route::get('/inscription', [AuthController::class, 'showRegister'])->name('register');
Route::post('/inscription', [AuthController::class, 'register']);
Route::post('/deconnexion', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Back-Office Administrateur & Gestion
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Livres
    Route::resource('livres', AdminBookController::class)
        ->names([
            'index' => 'books.index',
            'create' => 'books.create',
            'store' => 'books.store',
            'edit' => 'books.edit',
            'update' => 'books.update',
            'destroy' => 'books.destroy',
        ])
        ->except(['show']);

    // Commandes & Expéditions
    Route::get('/commandes', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/commandes/export/csv', [AdminOrderController::class, 'exportCsv'])->name('orders.export-csv');
    Route::get('/commandes/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/commandes/{order}/statut-expedition', [AdminOrderController::class, 'updateShipping'])->name('orders.update-shipping');

    // Modération des avis
    Route::get('/avis', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::post('/avis/{review}/approuver', [AdminReviewController::class, 'approve'])->name('reviews.approve');
    Route::delete('/avis/{review}', [AdminReviewController::class, 'destroy'])->name('reviews.destroy');
});
