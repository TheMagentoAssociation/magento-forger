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
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(
    name: 'sync:github:events',
    description: 'Sync GitHub issue/PR events into OpenSearch',
)]
class SyncGitHubEventsCommand extends Command
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
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'Only import issues updated since this relative time (e.g. "2 weeks", "5 days")')
            ->addOption('max-pages', null, InputOption::VALUE_OPTIONAL, 'Maximum number of pages to process (default: all)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repo = $this->githubRepo;

        if (!str_contains($repo, '/')) {
            $io->error('Invalid repository. Expected format: owner/repo');
            return Command::FAILURE;
        }

        [$owner, $name] = explode('/', $repo);
        $sinceOption = $input->getOption('since');
        $maxPages = $input->getOption('max-pages') ? (int) $input->getOption('max-pages') : null;
        $cutoff = null;

        if ($sinceOption) {
            try {
                $cutoff = new \DateTimeImmutable($sinceOption);
                $io->info('Only syncing events for issues updated since: ' . $cutoff->format('Y-m-d H:i:s'));
            } catch (\Exception $e) {
                $io->error("Invalid date format for --since: $sinceOption");
                return Command::FAILURE;
            }
        }

        $io->info("Starting sync of events for $repo...");

        $cursor = null;
        $hasNextPage = true;
        $page = 1;
        $bar = null;

        while ($hasNextPage) {
            if ($maxPages !== null && $page > $maxPages) {
                $io->info("Reached maximum pages limit ($maxPages).");
                break;
            }

            try {
                $response = $this->github->fetchIssuesWithEvents($owner, $name, $cursor);
                $nodes = $response['nodes'] ?? [];
                $cursor = $response['pageInfo']['endCursor'] ?? null;
                $hasNextPage = $response['pageInfo']['hasNextPage'] ?? false;

                if ($bar === null) {
                    $totalIssues = $response['totalCount'] ?? count($nodes);
                    $io->info("Fetching events for approximately $totalIssues issues...");
                    $bar = new ProgressBar($output, $totalIssues);
                    $bar->start();
                }

                $documents = [];
                $reachedCutoff = false;

                foreach ($nodes as $issue) {
                    $issueNumber = $issue['number'];

                    $events = $this->github->extractEventsFromIssue($issue);

                    if ($cutoff && !empty($events)) {
                        usort($events, fn($a, $b) => strtotime($b['created_at']) - strtotime($a['created_at']));
                        $mostRecentEvent = $events[0] ?? null;
                        if ($mostRecentEvent) {
                            $mostRecentDate = new \DateTimeImmutable($mostRecentEvent['created_at']);
                            if ($mostRecentDate < $cutoff) {
                                $reachedCutoff = true;
                                break;
                            }
                        }
                    }

                    foreach ($events as $event) {
                        if ($cutoff) {
                            $eventDate = new \DateTimeImmutable($event['created_at']);
                            if ($eventDate < $cutoff) {
                                continue;
                            }
                        }

                        $documents[] = [
                            'github_account_name' => $event['actor'],
                            'interaction_name' => $event['type'],
                            'issues-id' => $issueNumber,
                            'interaction_date' => (new \DateTimeImmutable($event['created_at']))->format(\DateTimeInterface::ATOM),
                        ];
                    }

                    $bar->advance();
                }

                if ($reachedCutoff) {
                    $io->newLine();
                    $io->info('Reached cutoff date, stopping sync.');
                    break;
                }

                if (!empty($documents)) {
                    $this->openSearch->indexBulk(
                        $this->openSearch->getIndexWithPrefix('interactions'),
                        $documents
                    );
                }

                $page++;
            } catch (Throwable $e) {
                $io->newLine();
                $io->warning("Error syncing page $page: " . $e->getMessage());
                $this->logger->error("Failed to process events page $page", ['exception' => $e]);
                break;
            }
        }

        if ($bar) {
            $bar->finish();
        }

        $io->newLine();
        $io->success('Done syncing GitHub events.');

        return Command::SUCCESS;
    }
}
