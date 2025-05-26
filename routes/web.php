<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PullRequestController;
use App\Http\Controllers\DocumentViewController;
use Illuminate\Support\Facades\Route;

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

Route::get('home', function () {
    return redirect()->route('dashboard');
})
    ->middleware(['auth', 'verified'])
    ->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('pull-requests', [PullRequestController::class, 'index'])->name('pull-requests.index');

    Route::get('docs', [DocumentViewController::class, 'index'])->name('docs.index');
    Route::get('docs/{category}', [DocumentViewController::class, 'show'])->name('docs.category');
    Route::get('docs/{category}/{file}', [DocumentViewController::class, 'show'])->name('docs.show');
    Route::get('docs/{category}/{subcategory}/{file}', [DocumentViewController::class, 'show'])->name('docs.subcategory.show');
});

require __DIR__.'/auth.php';
