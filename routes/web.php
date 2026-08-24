<?php

use App\Http\Controllers\ConceptDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/concepts', [ConceptDashboardController::class, 'index'])->name('concepts.dashboard');
Route::post('/concepts/{concept}/toggle', [ConceptDashboardController::class, 'toggle'])->name('concepts.toggle');
