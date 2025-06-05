<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use OpenSearch\Client;

class ProcessGitHubInteractions extends Command
{
    protected $signature = 'opensearch:process-interactions';
    protected $description = 'Assign points to GitHub interactions and store results in a new OpenSearch index.';

    /**
     * @var Client
     */
    protected $client;

    protected $index = 'interactions';
    protected $newIndex = 'points';

    public function __construct()
    {
        parent::__construct();
        $this->client = app(Client::class);
    }

    public function handle()
    {
        $scrollTimeout = '1m';
        $pageSize = 500;

        $params = [
            'index' => $this->index,
            'scroll' => $scrollTimeout,
            'size' => $pageSize,
            '_source' => ['github_account_name', 'interaction_date', 'interaction_name', 'issues-id'],
            'body' => [
                'query' => [
                    'match_all' => (object)[]
                ]
            ]
        ];

        $response = $this->client->search($params);
        $scrollId = $response['_scroll_id'];
        $documents = $response['hits']['hits'];
        $total = $response['hits']['total']['value'] ?? count($documents);

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        while (!empty($documents)) {
            foreach ($documents as $doc) {
                $source = $doc['_source'];
                $source['points'] = $this->assignPoints($source['interaction_name'] ?? '');

                $this->client->index([
                    'index' => $this->newIndex,
                    'body' => $source
                ]);

                $bar->advance();
            }

            $scrollParams = [
                'scroll_id' => $scrollId,
                'scroll' => $scrollTimeout
            ];

            $response = $this->client->scroll($scrollParams);
            $scrollId = $response['_scroll_id'];
            $documents = $response['hits']['hits'];
        }

        $bar->finish();
        $this->info("\nFinished processing all GitHub interactions.");
    }

    private function assignPoints($interaction)
    {
        return match ($interaction) {
            'commented' => 5,
            'mentioned' => 3,
            'subscribed' => 1,
            'labeled' => 2,
            'unlabeled' => 2,
            'assigned' => 4,
            'unassigned' => 3,
            'closed' => 6,
            'renamed' => 2,
            'referenced' => 4,
            'unsubscribed' => 1,
            'reopened' => 5,
            'milestoned' => 4,
            'comment_deleted' => -2,
            'transferred' => 3,
            'connected' => 3,
            'demilestoned' => 2,
            'parent_issue_added' => 2,
            'pinned' => 1,
            'unpinned' => 1,
            'sub_issue_added' => 2,
            default => 0
        };
    }
}
