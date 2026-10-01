<?php

use App\Http\Controllers\AnswerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('documents', [DocumentController::class, 'store'])->middleware('throttle:10,1')->name('documents.store');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

    Route::get('ask', [AnswerController::class, 'create'])->name('answers.create');
    Route::post('ask', [AnswerController::class, 'store'])->middleware('throttle:10,1')->name('answers.store');
    Route::get('answers', [AnswerController::class, 'index'])->name('answers.index');
    Route::get('answers/{answer}', [AnswerController::class, 'show'])->name('answers.show');
});

require __DIR__.'/settings.php';
