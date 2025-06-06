<?php

namespace App\Console\Commands;

use App\Services\GitHub\GitHubService;
use App\Services\Search\OpenSearchService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Sync all GitHub interactions (comments, reactions, etc.)
 * from issues and PRs into the "interactions" OpenSearch index.
 */
class SyncGitHubInteractions extends Command
{
    protected $signature = 'sync:github:interactions
                            {--since= : Only import issues/PRs updated since this relative time (e.g. "2 weeks", "5 days")}';

    protected $description = 'Sync all GitHub interactions into OpenSearch';

    public function handle(GitHubService $github, OpenSearchService $openSearch): int
    {
        $repo = config('github.repo', 'magento/magento2');

        if (!str_contains($repo, '/')) {
            $this->error('Missing or invalid repository. Set it in config/github.php');
            return 1;
        }

        [$owner, $name] = explode('/', $repo);
        $sinceOption = $this->option('since');
        $cutoff = null;

        if ($sinceOption) {
            try {
                $cutoff = Carbon::parse($sinceOption);
                $this->info("Only syncing interactions updated since: " . $cutoff->toDateTimeString());
            } catch (\Exception $e) {
                $this->error("Invalid date format for --since: $sinceOption");
                return 1;
            }
        } else {
            $this->info("No date cutoff applied. All interactions will be synced.");
        }

        $this->info("Starting sync of interactions for $repo...");

        $page = 1;
        $cursor = null;
        $hasNextPage = true;

        // Get initial count
        try {
            $issueCountObj = $github->fetchIssueCount($owner, $name);
            $totalIssues = $issueCountObj->total ?? null;
        } catch (Throwable $e) {
            $this->warn("Could not retrieve issue count.");
            Log::warning('GitHub interaction count failed', ['exception' => $e]);
            return 1;
        }

        if (!$totalIssues) {
            $this->error('No issues found or count unavailable.');
            return 1;
        }

        $this->info("Fetching interactions for approximately $totalIssues issues...");

        $bar = $this->output->createProgressBar($totalIssues);
        $bar->start();

        while ($hasNextPage && $this->checkRateLimit($github)) {
            try {
                $response = $github->fetchIssues($owner, $name, $cursor);
                $nodes = $response['nodes'] ?? [];
                $cursor = $response['pageInfo']['endCursor'] ?? null;
                $hasNextPage = $response['pageInfo']['hasNextPage'] ?? false;

                $interactions = [];

                foreach ($nodes as $issue) {
                    $updatedAt = Carbon::parse($issue['updatedAt']);

                    if ($cutoff && $updatedAt->lt($cutoff)) {
                        $bar->advance();
                        continue;
                    }

                    $issueId = $issue['number'];
                    $issueInteractions = $github->fetchInteractionsForIssue($owner, $name, $issueId);

                    foreach ($issueInteractions as $interaction) {
                        $interactions[] = [
                            'github_account_name' => $interaction['author'] ?? 'unknown',
                            'interaction_name' => $interaction['type'],
                            'issues-id' => $issueId,
                            'interaction_date' => Carbon::parse($interaction['date'])->toIso8601String(),
                        ];
                    }

                    $bar->advance();
                }

                if (!empty($interactions)) {
                    $openSearch->indexBulk('interactions', $interactions);
                }

                $page++;
            } catch (Throwable $e) {
                $this->warn("Error syncing page $page: " . $e->getMessage());
                Log::warning('GitHub interaction sync error', ['exception' => $e]);
                break;
            }
        }

        $bar->finish();
        $this->info("\nDone syncing interactions.");

        return 0;
    }

    protected function checkRateLimit(GitHubService $github): bool
    {
        $limit = $github->getRateLimit();

        if (!isset($limit['remaining']) || $limit['remaining'] < 100) {
            $this->warn("Approaching rate limit. Stopping to avoid throttling.");
            return false;
        }

        return true;
    }
}
