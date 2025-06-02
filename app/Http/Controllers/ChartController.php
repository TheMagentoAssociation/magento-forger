<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenSearch\Client;
use OpenSearch\ClientBuilder;

class ChartController extends Controller
{
    public function dispatch(Request $request, $method, Client $client)
    {
        // Check if method exists and is public
        if (method_exists($this, $method) && is_callable([$this, $method])) {
            return $this->$method($request, $client);
        }

        // Optionally, throw a 404 if the method does not exist
        throw new NotFoundHttpException("Chart method '{$method}' not found.");
    }

    public function prAgeOverTime(Request $request, Client $client)
    {
        $params = [
            'index' => 'github-pull-requests',
            'body'  => [
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
                                        'source' => <<<EOT
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

        $response = $client->search($params);
        $dataForChart = [];
        foreach ($response['aggregations']['monthly_closures']['buckets'] as $bucket) {
            $dataForChart[$bucket['key_as_string']] = (int)$bucket['avg_days_open']['value'];
        }
        $foo = '';
        // Example chart config
        return response()->json([
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
}
