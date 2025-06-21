<?php

namespace App\Services\Search;

use OpenSearch\Client;

class OpenSearchService
{
    public const OPENSEARCH_GITHUB_PULL_REQUESTS_INDEX = 'github-pull-requests';
    public const OPENSEARCH_GITHUB_ISSUES_INDEX = 'github-issues';

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
            'index' => $index,
            'body' => $body,
        ]);
    }

    public function searchPRs(QueryBuilder $builder): array
    {
        $prIndex = self::getIndexWithPrefix(self::OPENSEARCH_GITHUB_PULL_REQUESTS_INDEX);
        return $this->searchIndex($prIndex, $builder);
    }

    public function searchIssues(QueryBuilder $builder): array
    {
        $issueIndex = self::getIndexWithPrefix(self::OPENSEARCH_GITHUB_ISSUES_INDEX);

        return $this->searchIndex($issueIndex, $builder);
    }

    public function searchIndex(string $index, QueryBuilder $builder): array
    {
        return $this->client->search([
            'index' => $index,
            'body' => $builder->build(),
        ]);
    }

    public function indexPullRequests(array $pullRequests): void
    {
        if (empty($pullRequests)) {
            return;
        }
        $indexName = self::getIndexWithPrefix(self::OPENSEARCH_GITHUB_PULL_REQUESTS_INDEX);

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

        $indexName = self::getIndexWithPrefix(self::OPENSEARCH_GITHUB_ISSUES_INDEX);

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
    public static function getIndexPrefix(): string
    {
        return config('opensearch.index_prefix', '');
    }

    public static function getIndexWithPrefix(string $index): string
    {
        return self::getIndexPrefix() . $index;
    }
}
