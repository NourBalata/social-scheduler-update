<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\FacebookController;

use App\Http\Controllers\MediaController;
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
//   Route::get('/media', [MediaLibraryController::class, 'index'])->name('media.index');
// Route::post('/media', [MediaLibraryController::class, 'store'])->name('media.store');
// Route::delete('/media/{media}', [MediaLibraryController::class, 'destroy'])->name('media.destroy');
Route::post('/ai/generate-caption', [App\Http\Controllers\PostController::class, 'generateCaption'])->name('ai.caption');

});
Route::middleware(['auth'])
    ->prefix('media')
    ->name('media.')
    ->group(function () {
 
        // ── Stats ─────────────────────────────────────────────────────
        Route::get('stats',   [MediaController::class, 'stats'])->name('stats');
 
        // ── Filters List ──────────────────────────────────────────────
        Route::get('filters', [MediaController::class, 'filters'])->name('filters');
 
        // ── Tags ──────────────────────────────────────────────────────
        Route::get('tags',          [MediaController::class, 'tags'])->name('tags.index');
        Route::post('tags',         [MediaController::class, 'createTag'])->name('tags.create');
        Route::post('{media}/tags', [MediaController::class, 'syncTags'])->name('tags.sync');
 
        // ── Folders ───────────────────────────────────────────────────
        Route::get('folders',             [MediaController::class, 'folders'])->name('folders.index');
        Route::post('folders',            [MediaController::class, 'createFolder'])->name('folders.create');
        Route::delete('folders/{folder}', [MediaController::class, 'deleteFolder'])->name('folders.delete');
 
        // ── Upload ────────────────────────────────────────────────────
        Route::post('upload',       [MediaController::class, 'upload'])->name('upload');
        Route::post('upload/batch', [MediaController::class, 'uploadBatch'])->name('upload.batch');
 
        // ── Delete Batch (قبل {media} عشان ما يتعارضوا) ──────────────
        Route::delete('batch', [MediaController::class, 'destroyBatch'])->name('batch.delete');
 
        // ── Library ───────────────────────────────────────────────────
        Route::get('/',       [MediaController::class, 'index'])->name('index');
        Route::get('{media}', [MediaController::class, 'show'])->name('show');
 
        // ── Image Editing ─────────────────────────────────────────────
        Route::post('{media}/transform',    [MediaController::class, 'transform'])->name('transform');
        Route::post('{media}/filter',       [MediaController::class, 'applyFilter'])->name('filter');
        Route::post('{media}/text-overlay', [MediaController::class, 'addText'])->name('text');
        Route::post('{media}/watermark',    [MediaController::class, 'addWatermark'])->name('watermark');
 
        // ── Video ─────────────────────────────────────────────────────
        Route::post('{media}/compress', [MediaController::class, 'compressVideo'])->name('compress');
        Route::post('{media}/trim',     [MediaController::class, 'trimVideo'])->name('trim');
 
        // ── Move ──────────────────────────────────────────────────────
        Route::patch('{media}/move', [MediaController::class, 'moveToFolder'])->name('move');
 
        // ── Delete ────────────────────────────────────────────────────
        Route::delete('{media}', [MediaController::class, 'destroy'])->name('delete');
    });
 Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');
// Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
require __DIR__.'/auth.php';