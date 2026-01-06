<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\Command;

use App\DTO\Search\Filter;
use App\DTO\Search\FilterType;
use App\Service\Search\OpenSearchService;
use App\Service\Search\QueryBuilder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'search:pull-requests',
    description: 'Search OpenSearch for open GitHub Pull Requests',
)]
class SearchPullRequestsCommand extends Command
{
    public function __construct(
        private readonly OpenSearchService $searchService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Building query...');

        $queryBuilder = new QueryBuilder();

        // Example filter: only open PRs
        $queryBuilder->addFilter(new Filter('state', FilterType::TERM, 'OPEN'));

        $queryBuilder->addSort([
            ['created_at' => ['order' => 'desc']]
        ]);

        $queryBuilder->setSize(5);

        // Optional: select only specific fields
        $queryBuilder->selectFields(['id', 'title', 'state', 'created_at', 'author']);

        $io->info('Executing search...');
        $results = $this->searchService->searchPRs($queryBuilder);

        $hits = $results['hits']['hits'] ?? [];

        if (empty($hits)) {
            $io->info('No pull requests found.');
            return Command::SUCCESS;
        }

        $io->info('Results:');

        foreach ($hits as $hit) {
            $source = $hit['_source'] ?? [];
            $io->text(sprintf(
                '#%s: %s (%s) by %s - created %s',
                $source['id'] ?? 'N/A',
                $source['title'] ?? 'No Title',
                $source['state'] ?? 'unknown',
                $source['author'] ?? 'unknown',
                $source['created_at'] ?? 'unknown'
            ));
        }

        return Command::SUCCESS;
    }
}
