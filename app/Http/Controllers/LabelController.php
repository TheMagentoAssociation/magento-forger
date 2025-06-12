<?php

namespace App\Http\Controllers;

use App\Services\GitHub\GitHubService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenSearch\Client;

class LabelController extends Controller
{
    public function listAllLabels(Client $client, GitHubService $github): view
    {
        // Get repository configuration
        $repo = config('github.repo');
        if (!$repo || !str_contains($repo, '/')) {
            throw new \RuntimeException('Missing or invalid repository configuration');
        }
        [$owner, $name] = explode('/', $repo);

        // Fetch all repository labels from GitHub REST API (includes unassigned labels)
        $allRepositoryLabels = $github->fetchRepositoryLabels($owner, $name);

        // Query both issues and pull requests indices for label usage counts
        $params = [
            'index' => 'github-issues,github-pull-requests',
            'body'  => [
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
                                '_key' => 'asc'  // sort alphabetically
                            ],
                            'size' => 1000  // adjust based on number of unique labels
                        ]
                    ]
                ]
            ]
        ];

        $result = $client->search($params);
        $buckets = $result['aggregations']['by_label']['buckets'];

        // Create a map of label usage counts from OpenSearch
        $labelCounts = [];
        foreach ($buckets as $bucket) {
            $labelCounts[$bucket['key']] = $bucket['doc_count'];
        }

        // Combine all repository labels with their usage counts
        $nestedLabels = [];
        foreach ($allRepositoryLabels as $repoLabel) {
            $labelName = $repoLabel['name'];
            $count = $labelCounts[$labelName] ?? 0; // 0 for unassigned labels

            // Split label into prefix and remainder
            $parts = explode(':', $labelName, 2);
            $prefix = count($parts) > 1 ? trim($parts[0]) : 'no_prefix';

            // Initialize the prefix group if it doesn't exist
            if (!isset($nestedLabels[$prefix])) {
                $nestedLabels[$prefix] = [];
            }

            // Append the label and count under the prefix
            $nestedLabels[$prefix][] = [
                'label' => $labelName,
                'count' => $count
            ];
        }

        // Sort each prefix group by label name
        foreach ($nestedLabels as $prefix => $labelGroup) {
            usort($nestedLabels[$prefix], function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
        }

        ksort($nestedLabels);
        return view('labels/allLabels', ['labels' => $nestedLabels]);
    }

    public function listPrWithoutComponentLabel(Client $client): view
    {
        $params = [
            'index' => 'github-pull-requests',
            'body'  => [
                'size' => 0,
                'query' => [
                    'bool' => [
                        'must' => [
                            [ 'term' => [ 'is_open' => true ] ]
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
                            'order' => [ '_key' => 'asc' ],
                            'min_doc_count' => 1
                        ],
                        'aggs' => [
                            'by_month' => [
                                'date_histogram' => [
                                    'field' => 'created_at',
                                    'calendar_interval' => 'month',
                                    'format' => 'MM',
                                    'order' => [ '_key' => 'asc' ],
                                    'min_doc_count' => 1
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $result = $client->search($params);
        $dataToDisplay = [];
        foreach ($result['aggregations']['by_year']['buckets'] as $yearBucket) {
            $dataToDisplay[$yearBucket['key_as_string']] = [
                'year' => $yearBucket['key_as_string'],
                'total' => $yearBucket['doc_count'],
                'months' => [
                    '01' => ['month_number' => '01', 'total' => 0, 'start' => null, 'end' => null],
                    '02' => ['month_number' => '02', 'total' => 0, 'start' => null, 'end' => null],
                    '03' => ['month_number' => '03', 'total' => 0, 'start' => null, 'end' => null],
                    '04' => ['month_number' => '04', 'total' => 0, 'start' => null, 'end' => null],
                    '05' => ['month_number' => '05', 'total' => 0, 'start' => null, 'end' => null],
                    '06' => ['month_number' => '06', 'total' => 0, 'start' => null, 'end' => null],
                    '07' => ['month_number' => '07', 'total' => 0, 'start' => null, 'end' => null],
                    '08' => ['month_number' => '08', 'total' => 0, 'start' => null, 'end' => null],
                    '09' => ['month_number' => '09', 'total' => 0, 'start' => null, 'end' => null],
                    '10' => ['month_number' => '10', 'total' => 0, 'start' => null, 'end' => null],
                    '11' => ['month_number' => '11', 'total' => 0, 'start' => null, 'end' => null],
                    '12' => ['month_number' => '12', 'total' => 0, 'start' => null, 'end' => null],
                ]
            ];
            foreach ($yearBucket['by_month']['buckets'] as $monthBucket) {
                $dataToDisplay[$yearBucket['key_as_string']]['months'][$monthBucket['key_as_string']]['total'] = $monthBucket['doc_count'];
                $monthDate = Datetime::createFromFormat('Y-m-d', $yearBucket['key_as_string'] . '-' . $monthBucket['key_as_string'] . '-01');
                $firstOfMonth = (clone $monthDate)->modify('first day of this month')->setTime(0, 0, 0);
                $lastOfMonth = (clone $monthDate)->modify('last day of this month')->setTime(23, 59, 59);
                $dataToDisplay[$yearBucket['key_as_string']]['months'][$monthBucket['key_as_string']]['start'] = $firstOfMonth->format('Y-m-d\TH:i:s\Z');
                $dataToDisplay[$yearBucket['key_as_string']]['months'][$monthBucket['key_as_string']]['end'] = $lastOfMonth->format('Y-m-d\TH:i:s\Z');
            }
        }
        return view('labels/prsWithoutComponentLabel', ['prs' => $dataToDisplay]);
    }
}
