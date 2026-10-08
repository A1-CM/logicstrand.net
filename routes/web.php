<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\SandboxController;
use App\Http\Middleware\EnsurePlanActive;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::post('contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
Route::view('pricing', 'pricing', ['plans' => config('plans')])->name('pricing');
Route::get('checkout/{plan}', [CheckoutController::class, 'show'])->name('checkout.show');
Route::post('checkout/{plan}', [CheckoutController::class, 'complete'])
    ->middleware(['auth', 'verified', 'throttle:5,1'])
    ->name('checkout.complete');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('sandbox', [SandboxController::class, 'index'])->name('sandbox.index');
    Route::post('sandbox/source', [SandboxController::class, 'addSample'])
        ->middleware(EnsurePlanActive::class)->name('sandbox.source');
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('documents/{document}/progress', [DocumentController::class, 'progress'])->middleware(['signed', 'throttle:360,1'])->name('documents.progress');
    Route::post('documents/{document}/process', [DocumentController::class, 'process'])->middleware([EnsurePlanActive::class, 'throttle:30,1'])->name('documents.process');
    Route::post('documents', [DocumentController::class, 'store'])->middleware([EnsurePlanActive::class, 'throttle:10,1'])->name('documents.store');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('documents/{document}/retry', [DocumentController::class, 'retry'])->middleware([EnsurePlanActive::class, 'throttle:10,1'])->name('documents.retry');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('ask', [AnswerController::class, 'create'])->middleware(EnsurePlanActive::class)->name('answers.create');
    Route::post('ask', [AnswerController::class, 'store'])->middleware([EnsurePlanActive::class, 'throttle:10,1'])->name('answers.store');
    Route::get('answers', [AnswerController::class, 'index'])->name('answers.index');
    Route::get('answers/{answer}', [AnswerController::class, 'show'])->name('answers.show');
    Route::patch('answers/{answer}/favorite', [AnswerController::class, 'favorite'])->name('answers.favorite');
    Route::put('answers/{answer}/note', [AnswerController::class, 'note'])->name('answers.note');
    Route::get('answers/{answer}/export', [AnswerController::class, 'export'])->name('answers.export');
    Route::post('onboarding/dismiss', [OnboardingController::class, 'dismiss'])->name('onboarding.dismiss');
});

require __DIR__.'/settings.php';
