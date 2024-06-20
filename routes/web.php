<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\VendeurController;

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::get('/admin/dashboard',[AdminController::class, 'dashboard']);
Route::get('/client/dashboard',[ClientController::class, 'dashboard']);


Route::get('/admin/articles', [ArticleController::class, 'index'])->name('articles');
Route::post('/admin/articles/store', [ArticleController::class, 'store'])->name('articles.store');
Route::post('/admin/articles/update',[ArticleController::class, 'update'])->name('articles.update');
Route::get('/admin/articles/{id}/delete', [ArticleController::class, 'destroy'])->name('articles.delete');

Route::get('/admin/vendeurs', [VendeurController::class, 'index'])->name('vendeurs');
Route::post('/admin/vendeurs/store', [VendeurController::class, 'store'])->name('vendeurs.store');
