<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\Search\OpenSearchService;
use OpenSearch\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'opensearch:process-interactions',
    description: 'Assign points to GitHub interactions and store results in a new OpenSearch index',
)]
class ProcessGitHubInteractionsCommand extends Command
{
    private const INDEX_INTERACTIONS = 'interactions';
    private const INDEX_POINTS = 'points';

    public function __construct(
        private readonly Client $client,
        private readonly UserRepository $userRepository,
        private readonly OpenSearchService $openSearch,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $scrollTimeout = '1m';
        $pageSize = 500;

        // Load all users with affiliations and companies once
        $users = $this->userRepository->findAllWithAffiliations();

        /** @var array<string, User> $userMap */
        $userMap = [];
        foreach ($users as $user) {
            if ($user->getGithubUsername()) {
                $userMap[$user->getGithubUsername()] = $user;
            }
        }

        $missingUsers = 0;
        $missingAffiliations = 0;

        $params = [
            'index' => $this->openSearch->getIndexWithPrefix(self::INDEX_INTERACTIONS),
            'scroll' => $scrollTimeout,
            'size' => $pageSize,
            '_source' => ['github_account_name', 'interaction_date', 'interaction_name', 'issues-id'],
            'body' => [
                'query' => [
                    'match_all' => (object) []
                ]
            ]
        ];

        $response = $this->client->search($params);
        $scrollId = $response['_scroll_id'];
        $documents = $response['hits']['hits'];
        $total = $response['hits']['total']['value'] ?? count($documents);

        $bar = new ProgressBar($output, $total);
        $bar->start();

        while (!empty($documents)) {
            foreach ($documents as $doc) {
                $source = $doc['_source'];
                $githubUsername = $source['github_account_name'] ?? null;

                $realName = 'unclaimed by user';
                $companyName = 'unclaimed by company';

                $user = $userMap[$githubUsername] ?? null;

                if ($user) {
                    $realName = $user->getName() ?? 'unclaimed by user';
                    $date = new \DateTimeImmutable($source['interaction_date']);

                    $affiliation = null;
                    foreach ($user->getAffiliations() as $aff) {
                        $startDate = $aff->getStartDate();
                        $endDate = $aff->getEndDate();

                        if ($startDate <= $date && ($endDate === null || $endDate >= $date)) {
                            $affiliation = $aff;
                            break;
                        }
                    }

                    if ($affiliation && $affiliation->getCompany()) {
                        $companyName = $affiliation->getCompany()->getName();
                    } else {
                        $companyName = 'not working for a company at this time';
                        $missingAffiliations++;
                    }
                } else {
                    $missingUsers++;
                }

                if (str_starts_with($source['github_account_name'] ?? '', 'engcom-')) {
                    $realName = 'Adobe';
                    $companyName = 'Adobe';
                }

                $source['points'] = $this->assignPoints($source['interaction_name'] ?? '');
                $source['real_name'] = $realName;
                $source['company_name'] = $companyName;

                $docId = sha1(json_encode($source, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

                $this->client->index([
                    'index' => $this->openSearch->getIndexWithPrefix(self::INDEX_POINTS),
                    'id' => $docId,
                    'body' => $source
                ]);

                $bar->advance();
            }

            $scrollParams = [
                'scroll_id' => $scrollId,
                'scroll' => $scrollTimeout
            ];

            $response = $this->client->scroll($scrollParams);
            $scrollId = $response['_scroll_id'];
            $documents = $response['hits']['hits'];
        }

        $bar->finish();

        $io->newLine(2);
        $io->success('Finished processing all GitHub interactions.');
        $io->info("Missing users: $missingUsers");
        $io->info("Missing company affiliations: $missingAffiliations");

        return Command::SUCCESS;
    }

    private function assignPoints(string $interaction): int
    {
        return match ($interaction) {
            'commented' => 5,
            'mentioned' => 3,
            'subscribed' => 1,
            'labeled' => 5,
            'unlabeled' => 5,
            'assigned' => 8,
            'unassigned' => 1,
            'closed' => 10,
            'renamed' => 2,
            'referenced' => 4,
            'unsubscribed' => 1,
            'reopened' => 5,
            'milestoned' => 10,
            'comment_deleted' => -2,
            'transferred' => 0,
            'connected' => 5,
            'demilestoned' => 10,
            'parent_issue_added' => 0,
            'pinned' => 0,
            'unpinned' => 0,
            'sub_issue_added' => 0,
            default => 0
        };
    }
}
