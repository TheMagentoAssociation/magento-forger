<?php

use App\Http\Controllers as Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [Controllers\WelcomeController::class, 'index'])->name('home');
Route::get('issuesByMonth', [Controllers\IssuesByMonthController::class, 'index'])->name('issues-issuesByMonth');
Route::get('prsByMonth', [Controllers\PrsByMonthController::class, 'index'])->name('prs-PRsByMonth');
Route::get('labels/allLabels', [Controllers\LabelController::class, 'listAllLabels'])->name('labels-listAllLabels');
Route::get('labels/prsMissingComponent', [Controllers\LabelController::class, 'listPrWithoutComponentLabel'])->name('labels-PRsWithoutComponentLabel');
Route::get('leaderboard', [Controllers\LeaderboardController::class, 'index'])->name('leaderboard');
Route::get('leaderboard/{year}', [Controllers\LeaderboardController::class, 'showMonth'])->where('year', '[0-9]+')->name('leaderboard-month');
Route::get('/api/charts/{method}', [Controllers\ChartController::class, 'dispatch']);
Route::get('/api/universe-bar', [Controllers\UniverseBarController::class, 'render']);

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::post('/api/companies/propose', [Controllers\Api\CompanyProposalController::class, 'propose'])
        ->middleware('throttle:30,60'); // 30 submissions per hour per user
});

// Login page (required by auth middleware)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Github Social Login
Route::get('/auth/github', [Controllers\Auth\LoginController::class, 'redirectToGitHub'])->name('github_login');
Route::get('/auth/github/callback', [Controllers\Auth\LoginController::class, 'handleGitHubCallback'])
    ->middleware('throttle:10,1'); // Limit to 10 attempts per minute per IP

// Logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// Render employment form
Route::middleware('auth')->group(function () {
    Route::get('/employment', [Controllers\EmploymentController::class, 'create'])->name('employment');
    Route::post('/employment', [Controllers\EmploymentController::class, 'store']);
    Route::get('/employment/{id}/edit', [Controllers\EmploymentController::class, 'edit'])->name('employment.edit');
    Route::put('/employment/{id}', [Controllers\EmploymentController::class, 'update'])->name('employment.update');
    Route::delete('/employment/{id}', [Controllers\EmploymentController::class, 'destroy'])->name('employment.destroy');

    // Company Owner Management
    Route::get('/my-companies', [Controllers\CompanyOwnerController::class, 'index'])->name('company-owner.index');
    Route::get('/my-companies/{id}/edit', [Controllers\CompanyOwnerController::class, 'edit'])->name('company-owner.edit');
    Route::put('/my-companies/{id}', [Controllers\CompanyOwnerController::class, 'update'])->name('company-owner.update');
});
