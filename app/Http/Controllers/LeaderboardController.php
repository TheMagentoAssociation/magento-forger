<?php

namespace App\Http\Controllers;

use App\Services\Search\OpenSearchService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OpenSearch\Client;

class LeaderboardController extends Controller
{
    public function index(Client $client): view
    {
        $params = [
            'index' => OpenSearchService::getIndexWithPrefix('points'),
            'body'  => [
                'size' => 0,
                'aggs' => [
                    'by_year' => [
                        'terms' => [
                            'script' => [
                                'source' => "doc['interaction_date'].value.getYear()",
                                'lang'   => 'painless'
                            ],
                            'size'  => 100,
                            'order' => [
                                '_key' => 'asc'
                            ]
                        ],
                        'aggs' => [
                            'by_company' => [
                                'terms' => [
                                    'field' => 'company_name.keyword',
                                    'size'  => 1000
                                ],
                                'aggs' => [
                                    'total_points' => [
                                        'sum' => [
                                            'field' => 'points'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $result = $client->search($params);
        $dataToDisplay = [];
        $buckets = $result['aggregations']['by_year']['buckets'];

        foreach ($buckets as $bucket) {
            $yearlyData = [];
            foreach ($bucket['by_company']['buckets'] as $companyBucket) {
                $yearlyData[] = [
                    'name' => $companyBucket['key'],
                    'points' => (int)$companyBucket['total_points']['value'],
                ];
            }
            $dataToDisplay[$bucket['key']] = $yearlyData;
        }
        krsort($dataToDisplay);
        return view('leaderboard/leaderboard', ['data' => $dataToDisplay]);
    }
}
