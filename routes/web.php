<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AutopilotController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\FacebookPageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriberDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PlanController;

Route::get('/', fn () => view('auth.login'));

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminUserController::class, 'index'])->name('dashboard');
    Route::post('/users',    [AdminUserController::class, 'store'])->name('users.store');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [SubscriberDashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/facebook/redirect', [FacebookController::class, 'redirect'])->name('facebook.redirect');
    Route::get('/facebook/callback', [FacebookController::class, 'callback'])->name('facebook.callback');

    Route::post('/facebook/pages', [FacebookPageController::class, 'store'])->name('facebook.pages.store');

    Route::post('/posts',      [PostController::class, 'store'])->name('posts.store');
    Route::post('/posts/bulk', [PostController::class, 'bulkSchedule'])->name('posts.bulk');

    Route::post('/ai/generate-caption', [PostController::class, 'generateCaption'])->name('ai.caption');

    Route::prefix('autopilot')->name('autopilot.')->group(function () {
        Route::post('/generate', [AutopilotController::class, 'generate'])
            ->middleware('throttle:5,60')
            ->name('generate');
        Route::post('/confirm', [AutopilotController::class, 'confirm'])
            ->name('confirm');
        Route::post('/generate-single', [AutopilotController::class, 'generateSingle'])
            ->middleware('throttle:20,60')
            ->name('generate.single');
        Route::post('/confirm-single', [AutopilotController::class, 'confirmSingle'])
            ->name('confirm.single');
    });

    Route::prefix('media')->name('media.')->group(function () {
        Route::get('/',       [MediaController::class, 'index'])->name('index');
        Route::get('stats',   [MediaController::class, 'stats'])->name('stats');
        Route::get('filters', [MediaController::class, 'filters'])->name('filters');

        Route::get('tags',          [MediaController::class, 'tags'])->name('tags.index');
        Route::post('tags',         [MediaController::class, 'createTag'])->name('tags.create');
        Route::post('{media}/tags', [MediaController::class, 'syncTags'])->name('tags.sync');

        Route::get('folders',             [MediaController::class, 'folders'])->name('folders.index');
        Route::post('folders',            [MediaController::class, 'createFolder'])->name('folders.create');
        Route::delete('folders/{folder}', [MediaController::class, 'deleteFolder'])->name('folders.delete');

        Route::post('upload',       [MediaController::class, 'upload'])->name('upload');
        Route::post('upload/batch', [MediaController::class, 'uploadBatch'])->name('upload.batch');

        Route::delete('batch', [MediaController::class, 'destroyBatch'])->name('batch.delete');

        Route::get('{media}',              [MediaController::class, 'show'])->name('show');
        Route::post('{media}/transform',   [MediaController::class, 'transform'])->name('transform');
        Route::post('{media}/filter',      [MediaController::class, 'applyFilter'])->name('filter');
        Route::post('{media}/text-overlay',[MediaController::class, 'addText'])->name('text');
        Route::post('{media}/watermark',   [MediaController::class, 'addWatermark'])->name('watermark');
        Route::post('{media}/compress',    [MediaController::class, 'compressVideo'])->name('compress');
        Route::post('{media}/trim',        [MediaController::class, 'trimVideo'])->name('trim');
        Route::patch('{media}/move',       [MediaController::class, 'moveToFolder'])->name('move');
        Route::delete('{media}',           [MediaController::class, 'destroy'])->name('delete');
    });


    Route::post('/billing/fake-checkout/{plan}', [BillingController::class, 'fakeCheckout'])
        ->name('billing.fake.checkout');
});

Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');

Route::middleware(['auth', 'verified'])->prefix('billing')->name('billing.')->group(function () {
    Route::post('/checkout/{plan}', [BillingController::class, 'checkout'])->name('checkout');
    Route::get('/success',          [BillingController::class, 'success'])->name('success');
    Route::post('/portal',          [BillingController::class, 'portal'])->name('portal');
    Route::post('/cancel',          [BillingController::class, 'cancel'])->name('cancel');
    Route::get('/invoices',         [BillingController::class, 'invoices'])->name('invoices');
});


require __DIR__ . '/auth.php';