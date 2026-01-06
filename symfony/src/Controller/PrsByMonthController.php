<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\Controller;

use App\DTO\Misc\InfoText;
use App\Service\Search\OpenSearchService;
use OpenSearch\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PrsByMonthController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly OpenSearchService $searchService,
    ) {}

    #[Route('/prsByMonth', name: 'prs-PRsByMonth')]
    public function index(): Response
    {
        $dataToDisplay = [];
        $params = [
            'index' => $this->searchService->getIndexWithPrefix(OpenSearchService::OPENSEARCH_GITHUB_PULL_REQUESTS_INDEX),
            'body' => [
                'size' => 0,
                'query' => [
                    'term' => [
                        'is_open' => true
                    ]
                ],
                'aggs' => [
                    'by_year' => [
                        'date_histogram' => [
                            'field' => 'updated_at',
                            'calendar_interval' => 'year',
                            'format' => 'yyyy',
                            'order' => ['_key' => 'desc'],
                            'min_doc_count' => 1
                        ],
                        'aggs' => [
                            'by_month' => [
                                'date_histogram' => [
                                    'field' => 'updated_at',
                                    'calendar_interval' => 'month',
                                    'format' => 'MM',
                                    'order' => ['_key' => 'asc'],
                                    'min_doc_count' => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $result = $this->client->search($params);

            foreach ($result['aggregations']['by_year']['buckets'] as $yearBucket) {
                $dataToDisplay[$yearBucket['key_as_string']] = [
                    'year' => $yearBucket['key_as_string'],
                    'total' => $yearBucket['doc_count'],
                    'months' => $this->initializeMonths(),
                ];

                foreach ($yearBucket['by_month']['buckets'] as $monthBucket) {
                    $dataToDisplay[$yearBucket['key_as_string']]['months'][$monthBucket['key_as_string']]['total'] = $monthBucket['doc_count'];
                    $monthDate = \DateTime::createFromFormat('Y-m-d', $yearBucket['key_as_string'] . '-' . $monthBucket['key_as_string'] . '-01');
                    $firstOfMonth = (clone $monthDate)->modify('first day of this month')->setTime(0, 0, 0);
                    $lastOfMonth = (clone $monthDate)->modify('last day of this month')->setTime(23, 59, 59);
                    $dataToDisplay[$yearBucket['key_as_string']]['months'][$monthBucket['key_as_string']]['start'] = $firstOfMonth->format('Y-m-d\TH:i:s\Z');
                    $dataToDisplay[$yearBucket['key_as_string']]['months'][$monthBucket['key_as_string']]['end'] = $lastOfMonth->format('Y-m-d\TH:i:s\Z');
                }
            }
        } catch (\Exception $e) {
            // OpenSearch not available - return empty data
        }

        return $this->render('prs_by_month/index.html.twig', [
            'infoText' => $this->getInfoText(),
            'prs' => $dataToDisplay,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function initializeMonths(): array
    {
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $key = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $months[$key] = ['month_number' => $key, 'total' => 0, 'start' => null, 'end' => null];
        }

        return $months;
    }

    private function getInfoText(): InfoText
    {
        return new InfoText(
            title: 'Why Group Open Pull Requests by Month?',
            paragraphs: [
                'Instead of facing an overwhelming list of hundreds or even thousands of open pull requests, we group them by the month they were last updated. This makes the backlog more digestible and gives developers a clearer, more motivating way to engage with open PRs.',
                'By focusing on one chunk at a time—say, all PRs from last December—progress becomes visible. Every update or closure shrinks the list in real time, creating a satisfying sense of achievement.',
                'As an added bonus, this view also helps highlight older PRs that may have been forgotten, giving the community a chance to review, revive, or close them with intention.'
            ]
        );
    }
}
