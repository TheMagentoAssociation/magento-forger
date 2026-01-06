<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\Service\GitHub;

use App\DTO\GitHub\IssueCounts;
use App\DTO\GitHub\PullRequestCounts;
use App\Exception\GitHubGraphQLException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Psr\Log\LoggerInterface;

class GitHubService
{
    private Client $client;
    private int $maxRetries = 3;

    public function __construct(
        private readonly string $token,
        private readonly string $projectDir,
        private readonly LoggerInterface $logger,
    ) {
        if (!$this->token) {
            throw new \RuntimeException('Missing GitHub token in config.');
        }

        $this->client = new Client([
            'base_uri' => 'https://api.github.com/graphql',
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Content-Type' => 'application/json',
                'User-Agent' => 'Symfony-GitHubSync/1.0',
            ],
        ]);
    }

    private function getGraphQLPath(string $filename): string
    {
        return $this->projectDir . '/resources/graphql/github/' . $filename;
    }

    /**
     * @param array<string, mixed> $variables
     * @return array<string, mixed>|null
     */
    private function executeGraphQLQuery(string $query, array $variables = []): ?array
    {
        $retryCount = 0;

        do {
            try {
                $response = $this->client->post('', [
                    'json' => [
                        'query' => $query,
                        'variables' => $variables,
                    ],
                ]);
            } catch (ServerException $e) {
                $statusCode = $e->getResponse()->getStatusCode();
                if (in_array($statusCode, [502, 503, 504]) && $retryCount < $this->maxRetries) {
                    $waitSeconds = (int) pow(2, $retryCount) * 5;
                    $this->logger->warning("GitHub API returned {$statusCode}. Retrying in {$waitSeconds}s...");
                    sleep($waitSeconds);
                    $retryCount++;
                    continue;
                }
                throw $e;
            }

            /** @var array<string, mixed> $json */
            $json = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $rate = $json['data']['rateLimit'] ?? null;
            if ($rate && isset($rate['remaining'])) {
                if ($rate['remaining'] === 0 && isset($rate['resetAt'])) {
                    try {
                        $resetAt = new \DateTime($rate['resetAt']);
                        $waitSeconds = max($resetAt->getTimestamp() - time(), 1);
                        $this->logger->info("GitHub rate limit exceeded. Waiting for {$waitSeconds} seconds.");
                        sleep($waitSeconds);
                    } catch (\Exception $e) {
                        throw new \RuntimeException("Invalid rateLimit.resetAt value: " . $rate['resetAt'] . ' ' . $e->getMessage());
                    }

                    $retryCount++;
                    continue;
                } elseif ($rate['remaining'] < 100) {
                    $this->logger->info("GitHub rate limit very low ({$rate['remaining']} remaining). Adding 10s delay.");
                    sleep(10);
                } elseif ($rate['remaining'] < 500) {
                    $this->logger->info("GitHub rate limit getting low ({$rate['remaining']} remaining). Adding 3s delay.");
                    sleep(3);
                }
            }

            if (isset($json['errors'])) {
                throw new GitHubGraphQLException(
                    'GitHub GraphQL API error',
                    [
                        'status' => $response->getStatusCode(),
                        'errors' => $json['errors'],
                        'query' => $query,
                        'variables' => $variables,
                    ]
                );
            }

            return $json['data'] ?? null;
        } while (++$retryCount < $this->maxRetries);

        return null;
    }

    public function fetchIssueCount(string $owner, string $repo): IssueCounts
    {
        $query = file_get_contents($this->getGraphQLPath('github_issue_count.graphql'));

        $data = $this->executeGraphQLQuery($query, [
            'owner' => $owner,
            'repo' => $repo,
        ]);

        return IssueCounts::fromGraphQL($data ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchIssues(string $owner, string $repo, ?string $cursor = null): array
    {
        $query = file_get_contents($this->getGraphQLPath('github_issues.graphql'));

        $data = $this->executeGraphQLQuery($query, [
            'owner' => $owner,
            'repo' => $repo,
            'cursor' => $cursor,
        ]);

        $issues = $data['repository']['issues'] ?? [];
        $issues['rateLimit'] = $data['rateLimit'] ?? null;

        return $issues;
    }

    public function fetchPullRequestCount(string $owner, string $repo): PullRequestCounts
    {
        $query = file_get_contents($this->getGraphQLPath('github_pull_request_count.graphql'));

        $data = $this->executeGraphQLQuery($query, [
            'owner' => $owner,
            'repo' => $repo,
        ]);

        return PullRequestCounts::fromGraphQL($data ?? []);
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchPullRequests(string $owner, string $repo, ?string $cursor = null): array
    {
        $query = file_get_contents($this->getGraphQLPath('github_pull_requests.graphql'));

        $data = $this->executeGraphQLQuery($query, [
            'owner' => $owner,
            'repo' => $repo,
            'cursor' => $cursor,
        ]);

        $pullRequests = $data['repository']['pullRequests'] ?? [];
        $pullRequests['rateLimit'] = $data['rateLimit'] ?? null;

        return $pullRequests;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchInteractionsForIssue(string $owner, string $repo, int $issueNumber): array
    {
        $query = file_get_contents($this->getGraphQLPath('github_issue_interactions.graphql'));

        $variables = [
            'owner' => $owner,
            'name' => $repo,
            'number' => $issueNumber,
        ];

        $data = $this->executeGraphQLQuery($query, $variables);
        $node = $data['repository']['issueOrPullRequest'] ?? null;
        $interactions = [];

        if (!$node) {
            return [];
        }

        $isPR = $node['__typename'] === 'PullRequest';
        $author = $node['author']['login'] ?? 'unknown';

        if (isset($node['createdAt'])) {
            $interactions[] = [
                'author' => $author,
                'type' => $isPR ? 'created_pr' : 'created_issue',
                'date' => $node['createdAt'],
            ];
        }

        if ($isPR && isset($node['updatedAt']) && $node['updatedAt'] !== $node['createdAt']) {
            $interactions[] = [
                'author' => $author,
                'type' => 'updated_pr',
                'date' => $node['updatedAt'],
            ];
        }

        if ($isPR && isset($node['mergedAt']) && $node['mergedAt'] !== null) {
            $interactions[] = [
                'author' => $author,
                'type' => 'merged_pr',
                'date' => $node['mergedAt'],
            ];
        }

        foreach ($node['comments']['nodes'] ?? [] as $comment) {
            if (!isset($comment['createdAt'])) {
                continue;
            }
            $interactions[] = [
                'author' => $comment['author']['login'] ?? 'unknown',
                'type' => 'comment',
                'date' => $comment['createdAt'],
            ];
        }

        foreach ($node['timelineItems']['nodes'] ?? [] as $event) {
            if (!isset($event['createdAt'])) {
                continue;
            }

            $interactions[] = [
                'author' => $event['actor']['login'] ?? 'unknown',
                'type' => strtolower(str_replace('Event', '', $event['__typename'])),
                'date' => $event['createdAt'],
            ];
        }

        return $interactions;
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchIssuesPaged(string $owner, string $repo, ?string $cursor = null): array
    {
        $query = file_get_contents($this->getGraphQLPath('github_issues_paged.graphql'));

        $variables = [
            'owner' => $owner,
            'name' => $repo,
            'cursor' => $cursor,
        ];

        $data = $this->executeGraphQLQuery($query, $variables);

        $issues = $data['repository']['issues']['nodes'] ?? [];
        $pageInfo = $data['repository']['issues']['pageInfo'] ?? [];

        return [
            'issues' => $issues,
            'endCursor' => $pageInfo['endCursor'] ?? null,
            'hasNextPage' => $pageInfo['hasNextPage'] ?? false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchIssuesWithInteractions(string $owner, string $repo, ?string $cursor = null): array
    {
        $query = file_get_contents($this->getGraphQLPath('github_issues_with_interactions.graphql'));

        $data = $this->executeGraphQLQuery($query, [
            'owner' => $owner,
            'repo' => $repo,
            'cursor' => $cursor,
        ]);

        $issues = $data['repository']['issues'] ?? [];
        $rateLimit = $data['rateLimit'] ?? null;

        return [
            'nodes' => $issues['nodes'] ?? [],
            'pageInfo' => $issues['pageInfo'] ?? [],
            'totalCount' => $issues['totalCount'] ?? 0,
            'rateLimit' => $rateLimit,
        ];
    }

    /**
     * @param array<string, mixed> $issue
     * @return array<int, array<string, mixed>>
     */
    public function extractInteractionsFromIssue(array $issue): array
    {
        $interactions = [];
        $author = $issue['author']['login'] ?? 'unknown';

        if (isset($issue['createdAt'])) {
            $interactions[] = [
                'author' => $author,
                'type' => 'created_issue',
                'date' => $issue['createdAt'],
            ];
        }

        foreach ($issue['comments']['nodes'] ?? [] as $comment) {
            if (!isset($comment['createdAt'])) {
                continue;
            }
            $interactions[] = [
                'author' => $comment['author']['login'] ?? 'unknown',
                'type' => 'comment',
                'date' => $comment['createdAt'],
            ];
        }

        foreach ($issue['timelineItems']['nodes'] ?? [] as $event) {
            if (!isset($event['createdAt'])) {
                continue;
            }

            $interactions[] = [
                'author' => $event['actor']['login'] ?? 'unknown',
                'type' => strtolower(str_replace('Event', '', $event['__typename'])),
                'date' => $event['createdAt'],
            ];
        }

        return $interactions;
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchIssuesWithEvents(string $owner, string $repo, ?string $cursor = null): array
    {
        $query = file_get_contents($this->getGraphQLPath('github_issues_with_events.graphql'));

        $data = $this->executeGraphQLQuery($query, [
            'owner' => $owner,
            'repo' => $repo,
            'cursor' => $cursor,
        ]);

        $issues = $data['repository']['issues'] ?? [];
        $rateLimit = $data['rateLimit'] ?? null;

        return [
            'nodes' => $issues['nodes'] ?? [],
            'pageInfo' => $issues['pageInfo'] ?? [],
            'totalCount' => $issues['totalCount'] ?? 0,
            'rateLimit' => $rateLimit,
        ];
    }

    /**
     * @param array<string, mixed> $issue
     * @return array<int, array<string, mixed>>
     */
    public function extractEventsFromIssue(array $issue): array
    {
        $events = [];

        foreach ($issue['timelineItems']['nodes'] ?? [] as $event) {
            if (!isset($event['createdAt'])) {
                continue;
            }

            $events[] = [
                'type' => strtolower(str_replace('Event', '', $event['__typename'])),
                'actor' => $event['actor']['login'] ?? 'unknown',
                'created_at' => $event['createdAt'],
            ];
        }

        return $events;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function fetchEventsForIssue(string $owner, string $repo, int $number): array
    {
        $restClient = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Symfony-GitHubSync/1.0',
            ],
        ]);

        $events = [];
        $url = "repos/{$owner}/{$repo}/issues/{$number}/timeline";

        try {
            $response = $restClient->get($url);
            /** @var array<int, array<string, mixed>> $raw */
            $raw = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            foreach ($raw as $event) {
                if (!isset($event['event'], $event['created_at'])) {
                    continue;
                }

                $events[] = [
                    'type' => $event['event'],
                    'actor' => $event['actor']['login'] ?? 'unknown',
                    'created_at' => $event['created_at'],
                ];
            }
        } catch (\Throwable $e) {
            $this->logger->error("Failed to fetch events for issue #{$number}", ['exception' => $e]);
        }

        return $events;
    }

    /**
     * @return array<string, mixed>
     */
    public function getRateLimit(): array
    {
        $restClient = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => "Bearer {$this->token}",
                'Accept' => 'application/vnd.github+json',
                'User-Agent' => 'Symfony-GitHubSync/1.0',
            ],
        ]);

        try {
            $response = $restClient->get('rate_limit');
            /** @var array<string, mixed> $json */
            $json = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            return $json['rate'] ?? [];
        } catch (\Exception $e) {
            $this->logger->warning('Failed to fetch GitHub rate limit', ['exception' => $e]);

            return ['remaining' => 0];
        }
    }
}
