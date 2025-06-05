<?php

use App\Http\Controllers\ChartController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('home');
Route::get('issuesByMonth', [\App\Http\Controllers\IssuesByMonthController::class, 'index'])->name('issues-issuesByMonth');
Route::get('prsByMonth', [\App\Http\Controllers\PrsByMonthController::class, 'index'])->name('prs-PRsByMonth');
Route::get('labels/allLabels', [\App\Http\Controllers\LabelController::class, 'listAllLabels'])->name('labels-listAllLabels');
Route::get('labels/prsMissingComponent', [\App\Http\Controllers\LabelController::class, 'listPrWithoutComponentLabel'])->name('labels-PRsWithoutComponentLabel');
Route::get('/api/charts/{method}', [ChartController::class, 'dispatch']);
// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/recommend-company', [\App\Http\Controllers\CompanyRecommendationController::class, 'create']);
    Route::post('/recommend-company', [\App\Http\Controllers\CompanyRecommendationController::class, 'store'])->name('companies.recommend');
});
// Github Social Login
Route::get('/auth/github', [\App\Http\Controllers\Auth\LoginController::class, 'redirectToGitHub'])->name('github_login');
Route::get('/auth/github/callback', [\App\Http\Controllers\Auth\LoginController::class, 'handleGitHubCallback']);
