<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\OriginController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\VendeurController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\EquivalentController;
use App\Http\Controllers\ErpSyncController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ArticleController::class, 'examples']);

Auth::routes();
Route::get('/client/pending', [ClientController::class, 'pending'])->name('client.pending');

Route::get('/home', [HomeController::class, 'index'])->name('home');

// ── Admin: notifications & dashboard ───────────────────────────────────────
Route::get('/notifications', [AdminController::class, 'notification'])->name('notifications')->middleware(['auth', 'admin']);
Route::get('/notifications/mark-as-read/{id}', [AdminController::class, 'markAsRead'])->name('notifications.markAsRead')->middleware(['auth', 'admin']);
Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->middleware(['auth', 'admin']);

// ── Client area ────────────────────────────────────────────────────────────
Route::middleware(['auth', 'client'])->group(function () {
    Route::get('/client/dashboard', [ClientController::class, 'dashboard'])->name('client.dashboard');
    Route::get('/client/article/{id}/details', [ClientController::class, 'productDetails'])->name('details');
    Route::get('/client/article/autocomplete', [ClientController::class, 'autocomplete'])->name('client.article.autocomplete');
    Route::get('/client/article/recherche', [ClientController::class, 'search'])->name('client.article.search');
    Route::get('/client/commandes', [ClientController::class, 'commandes'])->name('client.commandes');
    Route::get('/client/panier', [ClientController::class, 'panier'])->name('client.panier');
    Route::post('/client/commande/store', [CommandeController::class, 'store'])->name('commande.store');
    Route::post('/update-quantity', [CommandeController::class, 'updateQuantity'])->name('update-quantity');
    Route::delete('/lignecommande/{id}', [CommandeController::class, 'destroy'])->name('lignecommande.destroy');
    Route::post('/commande/valider/{commandeId}', [CommandeController::class, 'validerCommande'])->name('commande.valider');
});

Route::get('/search/suggestions', [ClientController::class, 'suggestions'])->name('search.suggestions');

// ── Admin area ─────────────────────────────────────────────────────────────
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/clients/check', [ClientController::class, 'checkCode'])->name('clients.check');
    Route::get('/admin/clients/search-ajax', [ClientController::class, 'searchAjax'])->name('clients.search.ajax');
    Route::get('/admin/clients/recherche', [ClientController::class, 'recherche'])->name('clients.recherche');
    Route::get('/admin/articles/recherche', [ArticleController::class, 'recherche'])->name('articles.recherche');
    Route::get('/admin/articles', [ArticleController::class, 'index'])->name('articles');
    Route::get('/admin/commandes', [CommandeController::class, 'index'])->name('commandes');
    Route::post('/admin/commandes/{commandeId}/renvoyer-erp', [CommandeController::class, 'renvoyerErp'])->name('commandes.renvoyer');
    Route::post('/admin/articles/store', [ArticleController::class, 'store'])->name('articles.store');

    Route::post('/admin/articles/update', [ArticleController::class, 'update'])->name('articles.update');
    Route::get('/admin/articles/{id}/delete', [ArticleController::class, 'destroy'])->name('articles.delete');
    Route::get('/admin/equiv', [EquivalentController::class, 'importcsv'])->name('equi.import');
    Route::get('/admin/origin', [OriginController::class, 'importcsv'])->name('origin.import');
    Route::post('/admin/update-sel', [ArticleController::class, 'updateSel'])->name('updateSel');
    Route::post('/admin/upload/img', [AdminController::class, 'UploadImg'])->name('up.img');

    Route::get('/admin/vendeurs', [VendeurController::class, 'index'])->name('vendeurs');
    Route::post('/admin/vendeurs/store', [VendeurController::class, 'store'])->name('vendeurs.store');

    Route::get('/admin/users', [UserController::class, 'index'])->name('Users');
    Route::post('/admin/users/update', [UserController::class, 'update'])->name('users.update');
    Route::get('/admin/users/{id}/delete', [UserController::class, 'destroy'])->name('users.delete');
    Route::get('/admin/clients', [ClientController::class, 'index'])->name('clients');
    Route::post('/admin/clients/store', [ClientController::class, 'store'])->name('clients.store');

    Route::get('/preview', function () {
        return view('preview');
    });
});

// ── Guest routes ───────────────────────────────────────────────────────────
Route::get('/guest/recherche', [GuestController::class, 'search'])->name('guest.article.search');
Route::get('/guest/article/{id}/details', [GuestController::class, 'productDetails'])->name('guest.details');
Route::get('/guest/autocomplete', [GuestController::class, 'autocomplete'])->name('autocomplete');

// ── iDempiere ERP Sync Dashboard (Admin Only) ──────────────────────────────
Route::middleware(['auth', 'admin'])->prefix('admin/erp')->name('erp.')->group(function () {
    Route::get('/status',         [ErpSyncController::class, 'status'])->name('status');
    Route::post('/sync',          [ErpSyncController::class, 'triggerSync'])->name('sync');
    Route::post('/sync-batch',    [ErpSyncController::class, 'syncBatch'])->name('sync.batch');
    Route::post('/sync-clients',  [ErpSyncController::class, 'syncClients'])->name('sync.clients');
    Route::get('/sync-stock-now', [ErpSyncController::class, 'syncStock'])->name('sync.stock');
    Route::get('/products',       [ErpSyncController::class, 'browseErpProducts'])->name('products');
    Route::get('/progress',       [ErpSyncController::class, 'progress'])->name('progress');
});
