<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\Controller;

use App\Service\Search\OpenSearchService;
use OpenSearch\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LabelController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
        private readonly OpenSearchService $searchService,
    ) {}

    #[Route('/labels/allLabels', name: 'labels-listAllLabels')]
    public function listAllLabels(): Response
    {
        $nestedLabels = [];

        $params = [
            'index' => $this->searchService->getIndexWithPrefix('github-issues'),
            'body' => [
                'size' => 0,
                'query' => [
                    'term' => [
                        'is_open' => true
                    ]
                ],
                'aggs' => [
                    'by_label' => [
                        'terms' => [
                            'field' => 'labels.keyword',
                            'order' => [
                                '_key' => 'asc'
                            ],
                            'size' => 1000
                        ]
                    ]
                ]
            ]
        ];

        try {
            $result = $this->client->search($params);
            $buckets = $result['aggregations']['by_label']['buckets'];

            foreach ($buckets as $bucket) {
                $label = $bucket['key'];
                $count = $bucket['doc_count'];

                $parts = explode(':', $label, 2);
                $prefix = count($parts) > 1 ? trim($parts[0]) : 'no_prefix';

                if (!isset($nestedLabels[$prefix])) {
                    $nestedLabels[$prefix] = [];
                }

                $nestedLabels[$prefix][] = [
                    'label' => $label,
                    'count' => $count
                ];
            }

            ksort($nestedLabels);
        } catch (\Exception $e) {
            // OpenSearch not available - return empty data
        }

        return $this->render('labels/all_labels.html.twig', ['labels' => $nestedLabels]);
    }

    #[Route('/labels/prsMissingComponent', name: 'labels-PRsWithoutComponentLabel')]
    public function listPrWithoutComponentLabel(): Response
    {
        $dataToDisplay = [];

        $params = [
            'index' => $this->searchService->getIndexWithPrefix('github-pull-requests'),
            'body' => [
                'size' => 0,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['term' => ['is_open' => true]]
                        ],
                        'must_not' => [
                            [
                                'regexp' => [
                                    'labels.keyword' => 'Component:.*'
                                ]
                            ]
                        ]
                    ]
                ],
                'aggs' => [
                    'by_year' => [
                        'date_histogram' => [
                            'field' => 'created_at',
                            'calendar_interval' => 'year',
                            'format' => 'yyyy',
                            'order' => ['_key' => 'asc'],
                            'min_doc_count' => 1
                        ],
                        'aggs' => [
                            'by_month' => [
                                'date_histogram' => [
                                    'field' => 'created_at',
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

        return $this->render('labels/prs_without_component.html.twig', ['prs' => $dataToDisplay]);
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
}
