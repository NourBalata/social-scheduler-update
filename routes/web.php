<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\FacebookController;
Route::get('/', function () {
    return view('auth.login');
});
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminUserController::class, 'index'])->name('admin.dashboard');
    Route::post('/users', [AdminUserController::class, 'store'])->name('admin.users.store');
});
Route::get('/dashboard', function () {
    return view('subscriber/dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/dashboard/subscriber', [AdminUserController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('subscriber.dashboard');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {

    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    // Route::get('/auth/facebook', [FacebookController::class, 'redirect'])->name('facebook.redirect');
    // Route::get('/auth/facebook/callback', [FacebookController::class, 'callback'])->name('facebook.callback');
});
Route::middleware('auth')->group(function () {
    Route::get('/facebook/redirect', [FacebookController::class, 'redirect'])->name('facebook.redirect');
    Route::get('/facebook/callback', [FacebookController::class, 'callback'])->name('facebook.callback');
});

Route::post('/storeAnotherPage', [PostController::class, 'storeAnotherPage'])->name('pages.storeAnotherPage')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::post('/ai/generate', [AIController::class, 'generate'])->name('ai.generate');
Route::post('/posts/bulk', [PostController::class, 'bulkSchedule'])->name('posts.bulk');
    });
Route::post('/ai/generate-caption', [App\Http\Controllers\PostController::class, 'generateCaption'])->name('ai.caption');
 

require __DIR__.'/auth.php';
