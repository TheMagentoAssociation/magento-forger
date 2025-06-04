<?php

namespace App\Services\Search;

use OpenSearch\Client;
use Illuminate\Support\Facades\Log;

class OpenSearchService
{
    protected Client $client;
    protected string $indexPrefix;

    public function __construct()
    {
        $this->client = app(Client::class);
        $this->indexPrefix = config('opensearch.index_prefix', '');
    }

    public function search(string $index, array $body): array
    {
        return $this->client->search([
            'index' => $this->indexPrefix . $index,
            'body' => $body,
        ]);
    }

    public function searchPRs(QueryBuilder $builder): array
    {
        return $this->searchIndex('github-pull-requests', $builder);
    }

    public function searchIssues(QueryBuilder $builder): array
    {
        return $this->searchIndex('github-issues', $builder);
    }

    public function searchIndex(string $index, QueryBuilder $builder): array
    {
        return $this->client->search([
            'index' => $this->indexPrefix . $index,
            'body' => $builder->build(),
        ]);
    }

    public function indexPullRequests(array $pullRequests): void
    {
        if (empty($pullRequests)) {
            return;
        }

        $indexName = $this->indexPrefix . 'github-pull-requests';

        $body = [];
        foreach ($pullRequests as $pr) {
            $body[] = [
                'index' => [
                    '_index' => $indexName,
                    '_id' => $pr['number'],
                ],
            ];
            $body[] = $this->toPullRequestDocument($pr);
        }

        $this->client->bulk(['body' => $body]);
    }

    protected function toPullRequestDocument(array $pr): array
    {
        return [
            'id' => $pr['number'],
            'graphql_id' => $pr['id'],
            'title' => $pr['title'],
            'url' => $pr['url'],
            'state' => $pr['state'],
            'is_open' => $pr['state'] === 'OPEN',
            'is_draft' => $pr['isDraft'],
            'labels' => array_column($pr['labels']['nodes'] ?? [], 'name'),
            'created_at' => $pr['createdAt'],
            'updated_at' => $pr['updatedAt'],
            'merged_at' => $pr['mergedAt'] ?? null,
            'closed_at' => $pr['closedAt'] ?? null,
            'author' => $pr['author']['login'] ?? null,
            'comments_count' => $pr['comments']['totalCount'] ?? 0,
            'reviews_count' => $pr['reviews']['totalCount'] ?? 0,
        ];
    }

    public function indexIssues(array $issues): void
    {
        if (empty($issues)) {
            return;
        }

        $indexName = $this->indexPrefix . 'github-issues';

        $body = [];
        foreach ($issues as $issue) {
            $body[] = [
                'index' => [
                    '_index' => $indexName,
                    '_id' => $issue['number'],
                ],
            ];
            $body[] = $this->toIssueDocument($issue);
        }

        $this->client->bulk(['body' => $body]);
    }

    protected function toIssueDocument(array $issue): array
    {
        return [
            'id' => $issue['number'],
            'graphql_id' => $issue['id'],
            'title' => $issue['title'],
            'url' => $issue['url'],
            'state' => $issue['state'],
            'is_open' => $issue['state'] === 'OPEN',
            'labels' => array_column($issue['labels']['nodes'] ?? [], 'name'),
            'created_at' => $issue['createdAt'],
            'updated_at' => $issue['updatedAt'],
            'closed_at' => $issue['closedAt'] ?? null,
            'author' => $issue['author']['login'] ?? null,
            'comments_count' => $issue['comments']['totalCount'] ?? 0,
        ];
    }

    /**
     * Bulk index any documents with a SHA1 hash of the document as its ID.
     *
     * @param string $index
     * @param array $documents
     */
    public function indexBulk(string $index, array $documents): void
    {
        if (empty($documents)) {
            return;
        }

        $indexName = $this->indexPrefix . $index;
        $body = [];

        foreach ($documents as $doc) {
            try {
                $docJson = json_encode($doc, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
                $docId = sha1($docJson);

                $body[] = [
                    'index' => [
                        '_index' => $indexName,
                        '_id' => $docId,
                    ],
                ];
                $body[] = $doc;
            } catch (\Throwable $e) {
                Log::warning('Skipping document due to encoding/hash error', [
                    'exception' => $e,
                    'document' => $doc,
                ]);
            }
        }

        try {
            $this->client->bulk(['body' => $body]);
        } catch (\Throwable $e) {
            Log::error('Failed to bulk index documents to OpenSearch', [
                'index' => $indexName,
                'exception' => $e,
            ]);
        }
    }
}
