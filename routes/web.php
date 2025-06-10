<?php

use App\Http\Controllers as Controllers;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\WelcomeController::class, 'index'])->name('home');
Route::get('issuesByMonth', [\App\Http\Controllers\IssuesByMonthController::class, 'index'])->name('issues-issuesByMonth');
Route::get('prsByMonth', [\App\Http\Controllers\PrsByMonthController::class, 'index'])->name('prs-PRsByMonth');
Route::get('labels/allLabels', [\App\Http\Controllers\LabelController::class, 'listAllLabels'])->name('labels-listAllLabels');
Route::get('labels/prsMissingComponent', [\App\Http\Controllers\LabelController::class, 'listPrWithoutComponentLabel'])->name('labels-PRsWithoutComponentLabel');
Route::get('leaderboard/index', [\App\Http\Controllers\LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('/api/charts/{method}', [\App\Http\Controllers\ChartController::class, 'dispatch']);
// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/recommend-company', [\App\Http\Controllers\CompanyRecommendationController::class, 'create']);
    Route::post('/recommend-company', [\App\Http\Controllers\CompanyRecommendationController::class, 'store'])->name('companies.recommend');
});
// Github Social Login
Route::get('/auth/github', [\App\Http\Controllers\Auth\LoginController::class, 'redirectToGitHub'])->name('github_login');
Route::get('/auth/github/callback', [\App\Http\Controllers\Auth\LoginController::class, 'handleGitHubCallback']);
Route::get('/api/universe-bar', [Controllers\UniverseBarController::class, 'render']);

// Render employment form
Route::middleware('auth')->group(function () {
    Route::get('/employment', [\App\Http\Controllers\EmploymentController::class, 'create'])->name('employment');
    Route::post('/employment', [\App\Http\Controllers\EmploymentController::class, 'store']);
    Route::get('/employment/{id}/edit', [\App\Http\Controllers\EmploymentController::class, 'edit'])->name('employment.edit');
    Route::put('/employment/{id}', [\App\Http\Controllers\EmploymentController::class, 'update'])->name('employment.update');
    Route::delete('/employment/{id}', [\App\Http\Controllers\EmploymentController::class, 'destroy'])->name('employment.destroy');
});

