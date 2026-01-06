<?php
/**
 * Created by Qoliber
 *
 * @category    Qoliber
 * @package     Qoliber_MagentoForger
 * @author      Jakub Winkler <jwinkler@qoliber.com>
 */

declare(strict_types=1);

namespace App\Service\Search;

use OpenSearch\Client;
use Psr\Log\LoggerInterface;

class OpenSearchService
{
    public const OPENSEARCH_GITHUB_PULL_REQUESTS_INDEX = 'github-pull-requests';
    public const OPENSEARCH_GITHUB_ISSUES_INDEX = 'github-issues';

    public function __construct(
        private readonly Client $client,
        private readonly string $indexPrefix,
        private readonly LoggerInterface $logger,
    ) {}

    /**
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public function search(string $index, array $body): array
    {
        return $this->client->search([
            'index' => $index,
            'body' => $body,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function searchPRs(QueryBuilder $builder): array
    {
        $prIndex = $this->getIndexWithPrefix(self::OPENSEARCH_GITHUB_PULL_REQUESTS_INDEX);

        return $this->searchIndex($prIndex, $builder);
    }

    /**
     * @return array<string, mixed>
     */
    public function searchIssues(QueryBuilder $builder): array
    {
        $issueIndex = $this->getIndexWithPrefix(self::OPENSEARCH_GITHUB_ISSUES_INDEX);

        return $this->searchIndex($issueIndex, $builder);
    }

    /**
     * @return array<string, mixed>
     */
    public function searchIndex(string $index, QueryBuilder $builder): array
    {
        return $this->client->search([
            'index' => $index,
            'body' => $builder->build(),
        ]);
    }

    /**
     * @param array<int, array<string, mixed>> $pullRequests
     */
    public function indexPullRequests(array $pullRequests): void
    {
        if (empty($pullRequests)) {
            return;
        }

        $indexName = $this->getIndexWithPrefix(self::OPENSEARCH_GITHUB_PULL_REQUESTS_INDEX);

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

    /**
     * @param array<string, mixed> $pr
     * @return array<string, mixed>
     */
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

    /**
     * @param array<int, array<string, mixed>> $issues
     */
    public function indexIssues(array $issues): void
    {
        if (empty($issues)) {
            return;
        }

        $indexName = $this->getIndexWithPrefix(self::OPENSEARCH_GITHUB_ISSUES_INDEX);

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

    /**
     * @param array<string, mixed> $issue
     * @return array<string, mixed>
     */
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
     * @param array<int, array<string, mixed>> $documents
     */
    public function indexBulk(string $index, array $documents): void
    {
        if (empty($documents)) {
            return;
        }

        $indexName = $this->getIndexWithPrefix($index);
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
                $this->logger->warning('Skipping document due to encoding/hash error', [
                    'exception' => $e,
                    'document' => $doc,
                ]);
            }
        }

        try {
            $this->client->bulk(['body' => $body]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to bulk index documents to OpenSearch', [
                'index' => $indexName,
                'exception' => $e,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $document
     */
    public function indexDocument(string $index, array $document): void
    {
        try {
            $json = json_encode($document, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
            $id = sha1($json);

            $this->client->index([
                'index' => $this->getIndexWithPrefix($index),
                'id' => $id,
                'body' => $document,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('OpenSearch indexing failed', [
                'index' => $index,
                'document' => $document,
                'exception' => $e,
            ]);
        }
    }

    public function getIndexPrefix(): string
    {
        return $this->indexPrefix;
    }

    public function getIndexWithPrefix(string $index): string
    {
        if ($this->indexPrefix && str_starts_with($index, $this->indexPrefix)) {
            return $index;
        }

        return $this->indexPrefix . $index;
    }
}
