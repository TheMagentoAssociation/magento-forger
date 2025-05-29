<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('home');
Route::get('issuesByMonth', [\App\Http\Controllers\IssuesByMonthController::class, 'index'])->name('issues-issuesByMonth');
Route::get('prsByMonth', [\App\Http\Controllers\PrsByMonthController::class, 'index'])->name('prs-PRsByMonth');
Route::get('labels/allLabels', [\App\Http\Controllers\LabelController::class, 'listAllLabels'])->name('labels-listAllLabels');
Route::get('labels/prsMissingComponent', [\App\Http\Controllers\LabelController::class, 'listPrWithoutComponentLabel'])->name('labels-PRsWithoutComponentLabel');
