<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index'])->name('home');
Route::get('issuesByMonth', [\App\Http\Controllers\IssuesByMonthController::class, 'index'])->name('issues-issuesByMonth');
Route::get('labels/allLabels', [\App\Http\Controllers\LabelController::class, 'listAllLabels'])->name('labels-listAllLabels');
