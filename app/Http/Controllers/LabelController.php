<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenSearch\Client;

class LabelController extends Controller
{
    public function listAllLabels(Client $client): view
    {
        $params = [
            'index' => 'github-issues',
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
        $nestedLabels = [];
        $buckets = $result['aggregations']['by_label']['buckets'];

        foreach ($buckets as $bucket) {
            $label = $bucket['key'];
            $count = $bucket['doc_count'];

            // Split label into prefix and remainder
            $parts = explode(':', $label, 2);
            $prefix = count($parts) > 1 ? trim($parts[0]) : 'no_prefix';

            // Initialize the prefix group if it doesn't exist
            if (!isset($nestedLabels[$prefix])) {
                $nestedLabels[$prefix] = [];
            }

            // Append the label and count under the prefix
            $nestedLabels[$prefix][] = [
                'label' => $label,
                'count' => $count
            ];
        }
        ksort($nestedLabels);
        return view('labels/allLabels', ['labels' => $nestedLabels]);
    }
}
