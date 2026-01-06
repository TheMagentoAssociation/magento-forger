<?php
/**
 * Created by Qoliber
 *
 * @category    Qoliber
 * @package     Qoliber_MagentoForger
 * @author      Jakub Winkler <jwinkler@qoliber.com>
 */

declare(strict_types=1);

namespace App\Command;

use App\Service\GitHub\GitHubService;
use App\Service\Search\OpenSearchService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'sync:github:issues',
    description: 'Sync GitHub Issues using GraphQL',
)]
class SyncGitHubIssuesCommand extends Command
{
    public function __construct(
        private readonly GitHubService $github,
        private readonly OpenSearchService $openSearch,
        private readonly LoggerInterface $logger,
        private readonly string $githubRepo,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('cursor', null, InputOption::VALUE_OPTIONAL, 'Optional endCursor to resume pagination')
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'Optional date to filter issues since this date (e.g 2 days, 1 week, 1 month)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repo = $this->githubRepo;
        $cursor = $input->getOption('cursor');
        $since = $input->getOption('since');
        $cutoff = null;

        if (!$repo || !str_contains($repo, '/')) {
            $io->error('Missing or invalid repository. Set GITHUB_REPO in .env');
            return Command::FAILURE;
        }

        if ($since) {
            try {
                $cutoff = new \DateTimeImmutable($since);
                $io->info('Filtering issues updated since: ' . $cutoff->format('Y-m-d H:i:s'));
            } catch (\Exception $e) {
                $io->error("Invalid date format for --since option: $since");
                return Command::FAILURE;
            }
        } else {
            $io->info('No date filter applied');
        }

        [$owner, $name] = explode('/', $repo);

        $totalCount = null;
        try {
            $totalCounts = $this->github->fetchIssueCount($owner, $name);
            $summary = $totalCounts->summary();
            $totalCount = $totalCounts->total;
            $io->info("Syncing issues for $repo. ($summary)");
        } catch (Throwable $e) {
            $io->warning('Could not retrieve issue count');
            $this->logger->warning('GitHub issue count failed', ['exception' => $e]);
        }

        $totalPages = $totalCount ? (int) ceil($totalCount / 100) : null;

        if ($cursor) {
            $io->info("Resuming from cursor: $cursor");
        }

        $page = 1;
        do {
            $hasNextPage = false;
            try {
                $response = $this->github->fetchIssues($owner, $name, $cursor);
                $nodes = $response['nodes'] ?? [];

                foreach ($nodes as $issue) {
                    $io->text("#{$issue['number']}: {$issue['title']} ({$issue['state']})");
                }

                $this->openSearch->indexIssues($nodes);

                $cursor = $response['pageInfo']['endCursor'] ?? null;
                $hasNextPage = $response['pageInfo']['hasNextPage'] ?? false;

                $last = $nodes[array_key_last($nodes)] ?? null;
                if ($last && $cutoff) {
                    $lastUpdatedAt = new \DateTimeImmutable($last['updatedAt']);
                    if ($lastUpdatedAt < $cutoff) {
                        $io->info("Last issue is older than given cutoff ({$cutoff->format('Y-m-d H:i:s')}), stopping sync.");
                        break;
                    }
                }

                $pageInfo = "Page $page" . ($totalPages ? " of $totalPages" : '') . " done. Cursor: $cursor";
                $io->info($pageInfo);
                $page++;
            } catch (Throwable $e) {
                $io->warning("Could not fetch issues for page $page: " . $e->getMessage());
                $this->logger->warning('GitHub issue fetch failed', ['exception' => $e]);
            }
        } while ($hasNextPage);

        $io->success('Done syncing issues.');
        return Command::SUCCESS;
    }
}
