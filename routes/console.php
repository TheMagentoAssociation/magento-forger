<?php

use App\Console\Commands\SyncGitHubInteractions;
use App\Console\Commands\SyncGitHubIssues;
use Illuminate\Support\Facades\Schedule;
use App\Console\Commands\SyncGitHubPRs;

Schedule::command('model:prune')->daily();

Schedule::command(SyncGitHubPRs::class, ['--since' => '1 hour ago'])
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->name('sync-github-prs')
    ->description('Sync GitHub Pull Requests using GraphQL');

Schedule::command(SyncGitHubIssues::class, ['--since' => '1 hour ago'])
    ->everyFifteenMinutes()
    ->withoutOverlapping()
    ->runInBackground()
    ->name('sync-github-issues --since "1 day ago"')
    ->description('Sync GitHub Issues using GraphQL');

## schedule for SyncGitHubInteractions & SyncGithubEvents
Schedule::command(SyncGitHubInteractions::class, ['--since' => '1 day ago'])
    ->daily()
    ->withoutOverlapping()
    ->runInBackground()
    ->name('sync-github-interactions --since "1 day ago"')
    ->description('Sync GitHub Interactions using REST API');
