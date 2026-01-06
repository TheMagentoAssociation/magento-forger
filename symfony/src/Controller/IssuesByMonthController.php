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

class IssuesByMonthController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly OpenSearchService $searchService,
    ) {}

    #[Route('/issuesByMonth', name: 'issues-issuesByMonth')]
    public function index(): Response
    {
        $dataToDisplay = [];
        $params = [
            'index' => $this->searchService->getIndexWithPrefix(OpenSearchService::OPENSEARCH_GITHUB_ISSUES_INDEX),
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

        return $this->render('issues_by_month/index.html.twig', [
            'infoText' => $this->getInfoText(),
            'issues' => $dataToDisplay,
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
            title: 'Why Group Open Issues by Month?',
            paragraphs: [
                'A long list of open issues can be daunting and discouraging. To make things more manageable, we group issues by the month they were last updated. This breaks the backlog into smaller, more approachable segments.',
                'Developers can focus on a specific month—like issues from March—and make visible progress. Each update or resolution shortens the list, providing a clear sense of momentum and accomplishment.',
                'This view also makes it easier to spot older issues that may have fallen through the cracks, giving the community an opportunity to reassess and take action where needed.'
            ]
        );
    }
}
