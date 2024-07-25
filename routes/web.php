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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/',[ArticleController::class, 'examples'], function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::get('/admin/dashboard',[AdminController::class, 'dashboard'])->middleware('auth','admin');
Route::get('/client/dashboard',[ClientController::class, 'dashboard'])->middleware('auth', 'client');
Route::get('/client/article/{id}/details', [ClientController::class, 'productDetails'])->name('details')->middleware('auth', 'client');
Route::get('/client/article/recherche', [ClientController::class, 'search'])->name('client.article.search')->middleware('auth', 'client');
// Route::get('/client/article/recherche', [ClientController::class, 'showSearch'])->name('client.article.showSearch');
Route::get('/client/panier', [ClientController::class, 'panier'])->name('client.panier')->middleware('auth', 'client');
Route::post('/client/commande/store', [CommandeController::class, 'store'])->name('commande.store')->middleware('auth', 'client');
Route::get('/search/suggestions', [ClientController::class, 'suggestions'])->name('search.suggestions');


route::get('/admin/articles/recherche', [ArticleController::class, 'recherche'])->name('articles.recherche')->middleware('auth','admin');
Route::get('/admin/articles', [ArticleController::class, 'index'])->name('articles')->middleware('auth','admin');
Route::post('/admin/articles/store', [ArticleController::class, 'store'])->name('articles.store')->middleware('auth','admin');
Route::post('/admin/articles/update',[ArticleController::class, 'update'])->name('articles.update')->middleware('auth','admin');
Route::get('/admin/articles/{id}/delete', [ArticleController::class, 'destroy'])->name('articles.delete')->middleware('auth','admin');
Route::post('/admin/articles/majart',[ArticleController::class, 'importCSV'])->name('articles.import')->middleware('auth','admin');
Route::post('/admin/equiv',[EquivalentController::class, 'importCSV'])->name('equi.import')->middleware('auth','admin');
Route::post('/admin/origin',[OriginController::class, 'importCSV'])->name('origin.import')->middleware('auth','admin');
Route::post('/admin/update-sel', [ArticleController::class, 'updateSel'])->name('updateSel');


Route::get('/admin/vendeurs', [VendeurController::class, 'index'])->name('vendeurs')->middleware('auth','admin');
Route::post('/admin/vendeurs/store', [VendeurController::class, 'store'])->name('vendeurs.store')->middleware('auth','admin');
// Route::post('/admin/vendeurs/update',[VendeurController::class, 'update'])->name('vendeurs.update');
// Route::get('/admin/vendeurs/{id}/delete', [VendeurController::class, 'destroy'])->name('vendeurs.delete');

Route::get('/admin/users', [UserController::class, 'index'])->name('Users')->middleware('auth','admin');
Route::post('/admin/users/update',[UserController::class, 'update'])->name('users.update')->middleware('auth','admin');
Route::get('/admin/users/{id}/delete', [UserController::class, 'destroy'])->name('users.delete')->middleware('auth','admin');
Route::get('/admin/clients', [ClientController::class, 'index'])->name('clients')->middleware('auth','admin');
Route::post('/admin/clients/store', [ClientController::class, 'store'])->name('clients.store')->middleware('auth','admin');
// Route::post('/admin/clients/update',[ClientController::class, 'update'])->name('clients.update');
// Route::get('/admin/clients/{id}/delete', [ClientController::class, 'destroy'])->name('clients.delete');


//guest routes
Route::get('/guest/recherche', [GuestController::class, 'search'])->name('guest.article.search');
Route::get('/guest/article/{id}/details', [GuestController::class, 'productDetails'])->name('guest.details');
Route::get('/guest/autocomplete', [GuestController::class,'autocomplete'])->name('autocomplete');
