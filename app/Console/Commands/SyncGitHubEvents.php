<?php

namespace App\Console\Commands;

use App\Services\GitHub\GitHubService;
use App\Services\Search\OpenSearchService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncGitHubEvents extends Command
{
    protected $signature = 'sync:github:events
                            {--start-page=1 : Which page of issues to start from}
                            {--max-pages=1 : How many pages of issues to import}';

    protected $description = 'Sync GitHub issue/PR events into OpenSearch (paged with resume support)';

    public function handle(GitHubService $github, OpenSearchService $openSearch): int
    {
        $repo = config('github.repo', 'magento/magento2');

        if (!str_contains($repo, '/')) {
            $this->error('Invalid repository. Expected format: owner/repo');
            return 1;
        }

        [$owner, $name] = explode('/', $repo);
        $startPage = (int) $this->option('start-page');
        $maxPages = (int) $this->option('max-pages');

        $cursor = null;
        $currentPage = 1;
        $pagesProcessed = 0;

        while ($pagesProcessed < $maxPages) {
            if ($currentPage < $startPage) {
                // Skip to starting page
                $pageResult = $github->fetchIssuesPaged($owner, $name, $cursor);
                $cursor = $pageResult['endCursor'] ?? null;
                $currentPage++;
                continue;
            }

            $this->info("📄 Processing page $currentPage...");
            $pageResult = $github->fetchIssuesPaged($owner, $name, $cursor);
            $issues = $pageResult['issues'] ?? [];
            $cursor = $pageResult['endCursor'] ?? null;
            $hasNext = $pageResult['hasNextPage'] ?? false;

            $bar = $this->output->createProgressBar(count($issues));
            $bar->start();

            foreach ($issues as $issue) {
                $issueNumber = $issue['number'];

                try {
                    $events = $github->fetchEventsForIssue($owner, $name, $issueNumber);

                    foreach ($events as $event) {
                        $document = [
                            'github_account_name' => $event['actor'],
                            'interaction_name' => $event['type'],
                            'issues-id' => $issueNumber,
                            'interaction_date' => Carbon::parse($event['created_at'])->toIso8601String(),
                        ];

                        #echo "#$issueNumber - {$event['actor']} - {$event['type']}\n";

                        $openSearch->indexDocument(
                            OpenSearchService::getIndexWithPrefix('interactions'),
                            $document
                        );
                    }
                } catch (Throwable $e) {
                    $this->warn("⚠️ Error on issue #$issueNumber: " . $e->getMessage());
                    Log::error("Failed to process issue #$issueNumber", ['exception' => $e]);
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();

            if (!$hasNext) {
                break;
            }

            $pagesProcessed++;
            $currentPage++;
        }

        $this->info("✅ Done syncing GitHub events.");
        return 0;
    }
}
