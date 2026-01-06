<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\Controller;

use App\Service\Search\OpenSearchService;
use OpenSearch\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ChartController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly OpenSearchService $searchService,
    ) {}

    #[Route('/api/charts/{method}', name: 'api_charts')]
    public function dispatch(string $method): JsonResponse
    {
        if (method_exists($this, $method) && is_callable([$this, $method])) {
            return $this->$method();
        }

        throw new NotFoundHttpException("Chart method '{$method}' not found.");
    }

    public function prAgeOverTime(): JsonResponse
    {
        $dataForChart = [];

        $params = [
            'index' => $this->searchService->getIndexWithPrefix(OpenSearchService::OPENSEARCH_GITHUB_PULL_REQUESTS_INDEX),
            'body' => [
                'size' => 0,
                'query' => [
                    'term' => [
                        'is_open' => false
                    ]
                ],
                'aggs' => [
                    'monthly_closures' => [
                        'date_histogram' => [
                            'field' => 'closed_at',
                            'calendar_interval' => 'month',
                            'format' => 'yyyy-MM'
                        ],
                        'aggs' => [
                            'avg_days_open' => [
                                'avg' => [
                                    'script' => [
                                        'lang' => 'painless',
                                        'source' => <<<'EOT'
if (doc.containsKey('created_at') && doc.containsKey('closed_at') &&
    !doc['created_at'].empty && !doc['closed_at'].empty) {
  return ChronoUnit.DAYS.between(
    doc['created_at'].value.toInstant(),
    doc['closed_at'].value.toInstant()
  );
}
return null;
EOT
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
            foreach ($response['aggregations']['monthly_closures']['buckets'] as $bucket) {
                $dataForChart[$bucket['key_as_string']] = (int) ($bucket['avg_days_open']['value'] ?? 0);
            }
        } catch (\Exception $e) {
            // OpenSearch not available - return empty data
        }

        return $this->json([
            'type' => 'bar',
            'data' => [
                'datasets' => [[
                    'label' => 'Avg PR Age (days)',
                    'data' => $dataForChart,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                ]]
            ],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => ['beginAtZero' => true]
                ]
            ]
        ]);
    }

    public function issueAgeOverTime(): JsonResponse
    {
        $dataForChart = [];

        $params = [
            'index' => $this->searchService->getIndexWithPrefix(OpenSearchService::OPENSEARCH_GITHUB_ISSUES_INDEX),
            'body' => [
                'size' => 0,
                'query' => [
                    'term' => [
                        'is_open' => false
                    ]
                ],
                'aggs' => [
                    'monthly_closures' => [
                        'date_histogram' => [
                            'field' => 'closed_at',
                            'calendar_interval' => 'month',
                            'format' => 'yyyy-MM'
                        ],
                        'aggs' => [
                            'avg_days_open' => [
                                'avg' => [
                                    'script' => [
                                        'lang' => 'painless',
                                        'source' => <<<'EOT'
if (doc.containsKey('created_at') && doc.containsKey('closed_at') &&
    !doc['created_at'].empty && !doc['closed_at'].empty) {
  return ChronoUnit.DAYS.between(
    doc['created_at'].value.toInstant(),
    doc['closed_at'].value.toInstant()
  );
}
return null;
EOT
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = $this->client->search($params);
            foreach ($response['aggregations']['monthly_closures']['buckets'] as $bucket) {
                $dataForChart[$bucket['key_as_string']] = (int) ($bucket['avg_days_open']['value'] ?? 0);
            }
        } catch (\Exception $e) {
            // OpenSearch not available - return empty data
        }

        return $this->json([
            'type' => 'bar',
            'data' => [
                'datasets' => [[
                    'label' => 'Avg Issue Age (days)',
                    'data' => $dataForChart,
                    'backgroundColor' => 'rgba(54, 162, 235, 0.6)',
                ]]
            ],
            'options' => [
                'responsive' => true,
                'scales' => [
                    'y' => ['beginAtZero' => true]
                ]
            ]
        ]);
    }
}
