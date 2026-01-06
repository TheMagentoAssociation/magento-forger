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
    name: 'sync:github:interactions',
    description: 'Sync all GitHub interactions into OpenSearch',
)]
class SyncGitHubInteractionsCommand extends Command
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
            ->addOption('since', null, InputOption::VALUE_OPTIONAL, 'Only import issues/PRs updated since this relative time (e.g. "2 weeks", "5 days")');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $repo = $this->githubRepo;

        if (!str_contains($repo, '/')) {
            $io->error('Missing or invalid repository. Set GITHUB_REPO in .env');
            return Command::FAILURE;
        }

        [$owner, $name] = explode('/', $repo);
        $sinceOption = $input->getOption('since');
        $cutoff = null;

        if ($sinceOption) {
            try {
                $cutoff = new \DateTimeImmutable($sinceOption);
                $io->info('Only syncing interactions updated since: ' . $cutoff->format('Y-m-d H:i:s'));
            } catch (\Exception $e) {
                $io->error("Invalid date format for --since: $sinceOption");
                return Command::FAILURE;
            }
        } else {
            $io->info('No date cutoff applied. All interactions will be synced.');
        }

        $io->info("Starting sync of interactions for $repo...");

        $page = 1;
        $cursor = null;
        $hasNextPage = true;
        $bar = null;

        while ($hasNextPage) {
            try {
                $response = $this->github->fetchIssuesWithInteractions($owner, $name, $cursor);
                $nodes = $response['nodes'] ?? [];
                $cursor = $response['pageInfo']['endCursor'] ?? null;
                $hasNextPage = $response['pageInfo']['hasNextPage'] ?? false;

                if ($bar === null) {
                    $totalIssues = $response['totalCount'] ?? count($nodes);
                    $io->info("Fetching interactions for approximately $totalIssues issues...");
                    $bar = new ProgressBar($output, $totalIssues);
                    $bar->start();
                }

                $interactions = [];
                $reachedCutoff = false;

                foreach ($nodes as $issue) {
                    $updatedAt = new \DateTimeImmutable($issue['updatedAt']);

                    if ($cutoff && $updatedAt < $cutoff) {
                        $reachedCutoff = true;
                        break;
                    }

                    $issueId = $issue['number'];
                    $issueInteractions = $this->github->extractInteractionsFromIssue($issue);

                    foreach ($issueInteractions as $interaction) {
                        $interactions[] = [
                            'github_account_name' => $interaction['author'] ?? 'unknown',
                            'interaction_name' => $interaction['type'],
                            'issues-id' => $issueId,
                            'interaction_date' => (new \DateTimeImmutable($interaction['date']))->format(\DateTimeInterface::ATOM),
                        ];
                    }

                    $bar->advance();
                }

                if ($reachedCutoff) {
                    $io->newLine();
                    $io->info('Reached cutoff date, stopping sync.');
                    break;
                }

                if (!empty($interactions)) {
                    $this->openSearch->indexBulk(
                        $this->openSearch->getIndexWithPrefix('interactions'),
                        $interactions
                    );
                }

                $page++;
            } catch (Throwable $e) {
                $io->newLine();
                $io->warning("Error syncing page $page: " . $e->getMessage());
                $this->logger->warning('GitHub interaction sync error', ['exception' => $e]);
                break;
            }
        }

        if ($bar) {
            $bar->finish();
        }

        $io->newLine();
        $io->success('Done syncing interactions.');

        return Command::SUCCESS;
    }
}
