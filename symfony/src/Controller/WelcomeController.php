<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Search\Aggregation;
use App\Service\Search\OpenSearchService;
use App\Service\Search\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class WelcomeController extends AbstractController
{
    public function __construct(
        private readonly OpenSearchService $searchService,
    ) {}

    #[Route('/', name: 'home')]
    public function index(): Response
    {
        $prBuilder = new QueryBuilder();
        $prBuilder
            ->addAggregation(new Aggregation(
                'prs_opened_per_month',
                [
                    'date_histogram' => [
                        'field' => 'created_at',
                        'calendar_interval' => 'month',
                        'format' => 'yyyy-MM',
                        'min_doc_count' => 0,
                    ]
                ]
            ))
            ->addAggregation(new Aggregation(
                'prs_closed_per_month',
                [
                    'date_histogram' => [
                        'field' => 'closed_at',
                        'calendar_interval' => 'month',
                        'format' => 'yyyy-MM',
                        'min_doc_count' => 0,
                    ]
                ]
            ))
            ->setSize(0);

        $prResponse = null;
        $issueResponse = null;

        try {
            $prResponse = $this->searchService->searchPRs($prBuilder);

            $issueBuilder = new QueryBuilder();
            $issueBuilder
                ->addAggregation(new Aggregation(
                    'issues_opened_per_month',
                    [
                        'date_histogram' => [
                            'field' => 'created_at',
                            'calendar_interval' => 'month',
                            'format' => 'yyyy-MM',
                            'min_doc_count' => 0,
                        ]
                    ]
                ))
                ->addAggregation(new Aggregation(
                    'issues_closed_per_month',
                    [
                        'date_histogram' => [
                            'field' => 'closed_at',
                            'calendar_interval' => 'month',
                            'format' => 'yyyy-MM',
                            'min_doc_count' => 0,
                        ]
                    ]
                ))
                ->setSize(0);

            $issueResponse = $this->searchService->searchIssues($issueBuilder);
        } catch (\Exception $e) {
            // OpenSearch not available - return empty stats
        }

        $prsOpened = $prResponse['aggregations']['prs_opened_per_month']['buckets'] ?? [];
        $prsClosed = $prResponse['aggregations']['prs_closed_per_month']['buckets'] ?? [];
        $issuesOpened = $issueResponse['aggregations']['issues_opened_per_month']['buckets'] ?? [];
        $issuesClosed = $issueResponse['aggregations']['issues_closed_per_month']['buckets'] ?? [];

        $allMonths = array_unique(array_merge(
            array_column($prsOpened, 'key_as_string'),
            array_column($prsClosed, 'key_as_string'),
            array_column($issuesOpened, 'key_as_string'),
            array_column($issuesClosed, 'key_as_string')
        ));
        sort($allMonths);

        $monthlyStats = [];
        foreach ($allMonths as $month) {
            $monthlyStats[$month] = [
                'pr_opened' => 0,
                'pr_closed' => 0,
                'issue_opened' => 0,
                'issue_closed' => 0,
            ];
        }

        foreach ($prsOpened as $bucket) {
            $monthlyStats[$bucket['key_as_string']]['pr_opened'] = $bucket['doc_count'];
        }

        foreach ($prsClosed as $bucket) {
            $monthlyStats[$bucket['key_as_string']]['pr_closed'] = $bucket['doc_count'];
        }

        foreach ($issuesOpened as $bucket) {
            $monthlyStats[$bucket['key_as_string']]['issue_opened'] = $bucket['doc_count'];
        }

        foreach ($issuesClosed as $bucket) {
            $monthlyStats[$bucket['key_as_string']]['issue_closed'] = $bucket['doc_count'];
        }

        return $this->render('welcome/index.html.twig', [
            'monthlyStats' => $monthlyStats,
        ]);
    }
}
