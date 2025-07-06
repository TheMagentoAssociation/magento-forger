<?php

use App\Console\Commands\SyncGitHubIssues;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\SyncGitHubPRs;

Schedule::command('model:prune')->daily();

Schedule::command(SyncGitHubPRs::class, ['--since' => '1 day ago'])
    ->everyTwoHours()
    ->withoutOverlapping()
    ->runInBackground()
    ->name('sync-github-prs')
    ->description('Sync GitHub Pull Requests using GraphQL');

Schedule::command(SyncGitHubIssues::class, ['--since' => '1 day ago'])
    ->everyTwoHours(30)
    ->withoutOverlapping()
    ->runInBackground()
    ->name('sync-github-issues --since "1 day ago"')
    ->description('Sync GitHub Issues using GraphQL');
