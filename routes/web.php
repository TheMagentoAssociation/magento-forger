<?php

use App\Http\Controllers as Controllers;
use Illuminate\Support\Facades\Route;


Route::get('/api/charts/{method}', [ChartController::class, 'dispatch']);
Route::get('/', [Controllers\WelcomeController::class, 'index'])->name('home');
Route::get('issuesByMonth', [Controllers\IssuesByMonthController::class, 'index'])->name('issues-issuesByMonth');
Route::get('prsByMonth', [Controllers\PrsByMonthController::class, 'index'])->name('prs-PRsByMonth');
Route::get('labels/allLabels', [Controllers\LabelController::class, 'listAllLabels'])->name('labels-listAllLabels');
Route::get('labels/prsMissingComponent', [Controllers\LabelController::class, 'listPrWithoutComponentLabel'])->name('labels-PRsWithoutComponentLabel');
Route::get('/api/charts/{method}', [Controllers\ChartController::class, 'dispatch']);
Route::get('/api/universe-bar', [Controllers\UniverseBarController::class, 'render']);
Route::get('leaderboard/index', [Controllers\LeaderboardController::class, 'index'])->name('leaderboard');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/recommend-company', [Controllers\CompanyRecommendationController::class, 'create']);
    Route::post('/recommend-company', [Controllers\CompanyRecommendationController::class, 'store'])->name('companies.recommend');
});
// Github Social Login
Route::get('/auth/github', [Controllers\Auth\LoginController::class, 'redirectToGitHub'])->name('github_login');
Route::get('/auth/github/callback', [Controllers\Auth\LoginController::class, 'handleGitHubCallback']);

// Render employment form
Route::middleware('auth')->group(function () {
    Route::get('/employment', [Controllers\EmploymentController::class, 'create'])->name('employment');
    Route::post('/employment', [Controllers\EmploymentController::class, 'store']);
    Route::get('/employment/{id}/edit', [Controllers\EmploymentController::class, 'edit'])->name('employment.edit');
    Route::put('/employment/{id}', [Controllers\EmploymentController::class, 'update'])->name('employment.update');
    Route::delete('/employment/{id}', [Controllers\EmploymentController::class, 'destroy'])->name('employment.destroy');
});

