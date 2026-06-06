<?php

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AutopilotController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\FacebookPageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriberDashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PostRetryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostTemplateController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AIController;
use App\Http\Controllers\LeadFormController;

Route::get('/', fn () => view('auth.login'));

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminUserController::class, 'index'])->name('dashboard');
    Route::post('/users',    [AdminUserController::class, 'store'])->name('users.store');

    Route::post('/users/{user}/plan',   [AdminUserController::class, 'updatePlan'])->name('users.update-plan');
    Route::post('/users/{user}/toggle', [AdminUserController::class, 'toggleActive'])->name('users.toggle');
    Route::delete('/users/{user}',      [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::post('/plans',         [AdminUserController::class, 'storePlan'])->name('plans.store');
    Route::patch('/plans/{plan}', [AdminUserController::class, 'updatePlanDetails'])->name('plans.update');


 Route::post('/repost-rules',               [RepostRuleController::class, 'store'])->name('repost-rules.store');
    Route::post('/repost-rules/{rule}/toggle', [RepostRuleController::class, 'toggle'])->name('repost-rules.toggle');
    Route::delete('/repost-rules/{rule}',      [RepostRuleController::class, 'destroy'])->name('repost-rules.destroy');


    });

Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');

Route::get('/webhooks/facebook',  [LeadFormController::class, 'webhookVerify'])->name('webhook.verify');
Route::post('/webhooks/facebook', [LeadFormController::class, 'webhookReceive'])->name('webhook.receive');

Route::post('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
    }
    return back();
})->name('lang.switch');

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
    Route::get('/posts/best-time', [PostController::class, 'bestTime'])->name('posts.best-time');
    Route::patch('/posts/{post}/reschedule', [PostController::class, 'reschedule'])->name('posts.reschedule');

    Route::get('/posts/{post}/retry/analyze',        [PostRetryController::class, 'analyze'])->name('posts.retry.analyze');
    Route::post('/posts/{post}/retry/fix',           [PostRetryController::class, 'fix'])->name('posts.retry.fix');
    Route::patch('/posts/{post}/retry/update-token', [PostRetryController::class, 'updateToken'])->name('posts.retry.update-token');

    Route::post('/ai/generate-caption', [PostController::class, 'generateCaption'])->name('ai.caption');
    Route::post('/ai/generate-image',   [AIController::class, 'generateImage'])->name('ai.image');

    Route::prefix('autopilot')->name('autopilot.')->group(function () {
        Route::post('/generate',        [AutopilotController::class, 'generate'])->middleware('throttle:5,60')->name('generate');
        Route::post('/confirm',         [AutopilotController::class, 'confirm'])->name('confirm');
        Route::post('/generate-single', [AutopilotController::class, 'generateSingle'])->middleware('throttle:20,60')->name('generate.single');
        Route::post('/confirm-single',  [AutopilotController::class, 'confirmSingle'])->name('confirm.single');
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
        Route::delete('batch',      [MediaController::class, 'destroyBatch'])->name('batch.delete');

        Route::get('{media}',               [MediaController::class, 'show'])->name('show');
        Route::post('{media}/transform',    [MediaController::class, 'transform'])->name('transform');
        Route::post('{media}/filter',       [MediaController::class, 'applyFilter'])->name('filter');
        Route::post('{media}/text-overlay', [MediaController::class, 'addText'])->name('text');
        Route::post('{media}/watermark',    [MediaController::class, 'addWatermark'])->name('watermark');
        Route::post('{media}/compress',     [MediaController::class, 'compressVideo'])->name('compress');
        Route::post('{media}/trim',         [MediaController::class, 'trimVideo'])->name('trim');
        Route::patch('{media}/move',        [MediaController::class, 'moveToFolder'])->name('move');
        Route::delete('{media}',            [MediaController::class, 'destroy'])->name('delete');
    });

    Route::get('/templates',               [PostTemplateController::class, 'index'])->name('templates.index');
    Route::post('/templates',              [PostTemplateController::class, 'store'])->name('templates.store');
    Route::delete('/templates/{template}', [PostTemplateController::class, 'destroy'])->name('templates.destroy');

    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/page/{page}', [AnalyticsController::class, 'pageInsights'])->name('page');
        Route::get('/post',        [AnalyticsController::class, 'postInsights'])->name('post');
    });

    Route::get('/reports/monthly', [ReportController::class, 'download'])->name('reports.monthly');

    Route::get('/revenue-analyzer', \App\Livewire\RevenueAnalyzer::class)->name('revenue.analyzer');

    Route::prefix('billing')->name('billing.')->group(function () {
        Route::post('/checkout/{plan}', [BillingController::class, 'checkout'])->name('checkout');
        Route::get('/success',          [BillingController::class, 'success'])->name('success');
        Route::post('/portal',          [BillingController::class, 'portal'])->name('portal');
        Route::post('/cancel',          [BillingController::class, 'cancel'])->name('cancel');
        Route::get('/invoices',         [BillingController::class, 'invoices'])->name('invoices');
    });

    Route::post('/billing/fake-checkout/{plan}', [BillingController::class, 'fakeCheckout'])
        ->middleware('throttle:3,60')
        ->name('billing.fake.checkout');

    Route::get('/notifications',           [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});



Route::middleware(['auth'])->group(function () {
    Route::get('/plans', [PlanController::class, 'index'])->name('plans.index');
    Route::post('/plans/{plan}/subscribe', [PlanController::class, 'subscribe'])->name('plans.subscribe');
    Route::post('/plans/cancel', [PlanController::class, 'cancel'])->name('plans.cancel');
});

// Route::post('/repost-rules', [RepostRuleController::class, 'store'])->name('repost-rules.store');
// Route::post('/repost-rules/{rule}/toggle', [RepostRuleController::class, 'toggle'])->name('repost-rules.toggle');
// Route::delete('/repost-rules/{rule}', [RepostRuleController::class, 'destroy'])->name('repost-rules.destroy');

Route::post('/repost-rules',               [\App\Http\Controllers\RepostRuleController::class, 'store'])->name('user.repost-rules.store');
Route::post('/repost-rules/{rule}/toggle', [\App\Http\Controllers\RepostRuleController::class, 'toggle'])->name('user.repost-rules.toggle');
Route::delete('/repost-rules/{rule}',      [\App\Http\Controllers\RepostRuleController::class, 'destroy'])->name('user.repost-rules.destroy');

// Salla Webhook — خارج auth وخارج CSRF
Route::post('/webhooks/salla', [\App\Http\Controllers\SallaWebhookController::class, 'handle'])
    ->name('webhooks.salla');

// Salla Routes — داخل auth
Route::middleware(['auth', 'verified'])->prefix('salla')->name('salla.')->group(function () {
    Route::get('/redirect',          [\App\Http\Controllers\SallaController::class, 'redirect'])->name('redirect');
    Route::get('/callback',          [\App\Http\Controllers\SallaController::class, 'callback'])->name('callback');
    Route::get('/products',          [\App\Http\Controllers\SallaController::class, 'products'])->name('products');
    Route::post('/sync',             [\App\Http\Controllers\SallaController::class, 'syncProducts'])->name('sync');
    Route::post('/toggle-auto-post', [\App\Http\Controllers\SallaController::class, 'toggleAutoPost'])->name('toggle-auto-post');
    Route::delete('/disconnect',     [\App\Http\Controllers\SallaController::class, 'disconnect'])->name('disconnect');

    Route::post('/generate-caption', [\App\Http\Controllers\SallaPostController::class, 'generateCaption'])
        ->middleware('throttle:20,60')->name('generate-caption');
    Route::post('/schedule-post',    [\App\Http\Controllers\SallaPostController::class, 'schedulePost'])->name('schedule-post');
});

require __DIR__ . '/auth.php';