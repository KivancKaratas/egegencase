<?php


use App\Http\Controllers\NewsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['log.requests', 'token.check'])->group(function () {
    Route::post('/news', [NewsController::class, 'store'])->name('news.store');
    Route::post('/news/{id}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/{id}', [NewsController::class, 'destroy'])->name('news.destroy');
    Route::get('/news/search', [NewsController::class, 'search'])->name('news.search');
});
