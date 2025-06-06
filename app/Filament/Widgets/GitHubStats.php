<?php

namespace App\Filament\Widgets;

use App\Models\Company;
use App\Models\User;
use App\Services\Search\OpenSearchService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GitHubStats extends BaseWidget
{
    protected function getStats(): array
    {
        $openSearchStats = $this->getTotalUserNamesFromInteractions();
        return [
            Stat::make('Total Users claimed', number_format($openSearchStats['claimed_users']))->color('success')->description('Total Users Claimed'),
            Stat::make('Total Users unclaimed', number_format($openSearchStats['unclaimed_users']))->color('warning')->description('Total Users Unclaimed'),
            Stat::make('Percent claimed', number_format($openSearchStats['percentClaimed'], 5) .'%'),
            Stat::make('Companies', (new Company)->count())->description('Companies registered on Forger'),
            Stat::make('Users', (new User)->count())->description('Users registered on Forger')
        ];
    }

    protected function getTotalUserNamesFromInteractions(): array
    {
        $client = new OpenSearchService();
        $params = [
                'size' => 0,
                'aggs' => [
                    'unclaimed_users' => [
                        'filter' => [
                            'term' => [
                                'real_name.keyword' => 'unclaimed by user'
                            ]
                        ],
                        'aggs' => [
                            'unique_github_accounts' => [
                                'cardinality' => [
                                    'field' => 'github_account_name.keyword'
                                ]
                            ]
                        ]
                    ],
                    'claimed_users' => [
                        'filter' => [
                            'bool' => [
                                'must_not' => [
                                    'term' => [
                                        'real_name.keyword' => 'unclaimed by user'
                                    ]
                                ]
                            ]
                        ],
                        'aggs' => [
                            'unique_real_names' => [
                                'cardinality' => [
                                    'field' => 'real_name.keyword'
                                ]
                            ]
                        ]
                    ]
                ]
        ];
        $data = $client->search('points', $params);
        $returnData = [
            'unclaimed_users' => $data['aggregations']['unclaimed_users']['unique_github_accounts']['value'],
            'claimed_users' => $data['aggregations']['claimed_users']['unique_real_names']['value'],
            'percentClaimed' => ($data['aggregations']['claimed_users']['unique_real_names']['value'] / ($data['aggregations']['claimed_users']['unique_real_names']['value'] + $data['aggregations']['unclaimed_users']['unique_github_accounts']['value'])) * 100
        ];
        return $returnData;
    }
}
