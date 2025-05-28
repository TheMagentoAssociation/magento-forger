<?php

use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WelcomeController::class, 'index']);
Route::get('issuesByMonth', [\App\Http\Controllers\IssuesByMonthController::class, 'index']);
