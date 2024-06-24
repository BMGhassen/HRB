<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\UserController;
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
Route::post('/admin/articles/majart',[ArticleController::class, 'importCSV'])->name('articles.import');

Route::get('/admin/vendeurs', [VendeurController::class, 'index'])->name('vendeurs');
Route::post('/admin/vendeurs/store', [VendeurController::class, 'store'])->name('vendeurs.store');
Route::post('/admin/vendeurs/update',[VendeurController::class, 'update'])->name('vendeurs.update');
Route::get('/admin/vendeurs/{id}/delete', [VendeurController::class, 'destroy'])->name('vendeurs.delete');

Route::get('/admin/users', [UserController::class, 'index'])->name('Users');
Route::post('/admin/users/update',[UserController::class, 'update'])->name('users.update');
Route::get('/admin/users/{id}/delete', [UserController::class, 'destroy'])->name('users.delete');
