<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use App\Services\Search\QueryBuilder;
use App\Services\Search\OpenSearchService;
use App\DataTransferObjects\Search\Aggregation;

class WelcomeController extends Controller
{
    public function index(OpenSearchService $search): View
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

        $prResponse = $search->searchPRs($prBuilder);

        // ---- ISSUE AGGREGATIONS ----
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

        $issueResponse = $search->searchIssues($issueBuilder);

        $prsOpened     = $prResponse['aggregations']['prs_opened_per_month']['buckets'] ?? [];
        $prsClosed     = $prResponse['aggregations']['prs_closed_per_month']['buckets'] ?? [];
        $issuesOpened  = $issueResponse['aggregations']['issues_opened_per_month']['buckets'] ?? [];
        $issuesClosed  = $issueResponse['aggregations']['issues_closed_per_month']['buckets'] ?? [];

        $allMonths = collect(array_merge(
            array_column($prsOpened, 'key_as_string'),
            array_column($prsClosed, 'key_as_string'),
            array_column($issuesOpened, 'key_as_string'),
            array_column($issuesClosed, 'key_as_string')
        ))->unique()->sort()->values();

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

        return view('welcome', [
            'monthlyStats' => $monthlyStats,
        ]);
    }
}
