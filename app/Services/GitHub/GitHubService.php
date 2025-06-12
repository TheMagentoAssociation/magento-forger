<?php

namespace App\Services\GitHub;

use App\DataTransferObjects\GitHub\IssueCounts;
use App\DataTransferObjects\GitHub\PullRequestCounts;
use App\Exceptions\GitHubGraphQLException;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use JsonException;
use RuntimeException;

class GitHubService
{
    protected Client $client;
    protected string $token;
    private int $maxRetries;

    public function __construct()
    {
        $this->maxRetries = 3;
        $this->token = config('github.token');

        if (!$this->token) {
            throw new RuntimeException('Missing GitHub token in config.');
        }

        $this->client = new Client([
            'base_uri' => 'https://api.github.com/graphql',
            'headers' => [
                'Authorization' => "Bearer $this->token",
                'Content-Type' => 'application/json',
                'User-Agent' => 'Laravel-GitHubSync/1.0',
            ],
        ]);
    }

    /**
     * Execute a GraphQL query with variables.
     *
     * @param string $query
     * @param array $variables
     * @return array|null
     * @throws GitHubGraphQLException
     * @throws JsonException
     * @throws GuzzleException
     */
    private function executeGraphQLQuery(string $query, array $variables = []): ?array
    {

        $retryCount = 0;
        do {
            $response = $this->client->post('', [
                'json' => [
                    'query' => $query,
                    'variables' => $variables,
                ],
            ]);

            $json = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            $rate = $json['data']['rateLimit'] ?? null;
            if ($rate && $rate['remaining'] === 0 && isset($rate['resetAt'])) {
                try {
                    $resetAt = new DateTime($rate['resetAt']);
                    $waitSeconds = max($resetAt->getTimestamp() - time(), 1);
                    Log::info("Github Rate limit exceeded. Waiting for $waitSeconds seconds.");
                    sleep($waitSeconds);
                } catch (Exception $e) {
                    throw new RuntimeException("Invalid rateLimit.resetAt value: " . $rate['resetAt'] . ' ' . $e->getMessage());
                }

                $retryCount++;
                continue;
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

    /**
     * @throws GitHubGraphQLException
     * @throws GuzzleException
     * @throws JsonException
     */
    public function fetchPullRequestCount(string $owner, string $repo): PullRequestCounts
    {
        $query = file_get_contents(resource_path('graphql/github/github_pull_request_count.graphql'));

        $data = $this->executeGraphQLQuery($query, [
            'owner' => $owner,
            'repo' => $repo,
        ]);

        return PullRequestCounts::fromGraphQL($data);
    }

    /**
     * @throws GitHubGraphQLException
     * @throws GuzzleException
     * @throws JsonException
     */
    public function fetchPullRequests(string $owner, string $repo, ?string $cursor = null): array
    {
        $query = file_get_contents(resource_path('graphql/github/github_pull_requests.graphql'));

        $data = $this->executeGraphQLQuery($query, [
            'owner' => $owner,
            'repo' => $repo,
            'cursor' => $cursor,
        ]);

        return $data['repository']['pullRequests'] ?? [];
    }

    /**
     * @throws GitHubGraphQLException
     * @throws GuzzleException
     * @throws JsonException
     */
    public function fetchIssueCount(string $owner, string $repo): IssueCounts
    {
        $query = file_get_contents(resource_path('graphql/github/github_issue_count.graphql'));

        $data = $this->executeGraphQLQuery($query, [
            'owner' => $owner,
            'repo' => $repo,
        ]);

        return IssueCounts::fromGraphQL($data);
    }

    /**
     * @throws GitHubGraphQLException
     * @throws GuzzleException
     * @throws JsonException
     */
    public function fetchIssues(string $owner, string $repo, ?string $cursor = null): array
    {
        $query = file_get_contents(resource_path('graphql/github/github_issues.graphql'));

        $data = $this->executeGraphQLQuery($query, [
            'owner' => $owner,
            'repo' => $repo,
            'cursor' => $cursor,
        ]);

        return $data['repository']['issues'] ?? [];
    }

    /**
     * Fetch all repository labels using GraphQL
     *
     * @throws GitHubGraphQLException
     * @throws GuzzleException
     * @throws JsonException
     */
    public function fetchRepositoryLabels(string $owner, string $repo): array
    {
        $query = file_get_contents(resource_path('graphql/github/github_labels.graphql'));

        $labels = [];
        $cursor = null;

        do {
            $data = $this->executeGraphQLQuery($query, [
                'owner' => $owner,
                'repo' => $repo,
                'cursor' => $cursor,
            ]);

            $response = $data['repository']['labels'] ?? [];
            $nodes = $response['nodes'] ?? [];

            $labels = array_merge($labels, $nodes);

            $cursor = $response['pageInfo']['endCursor'] ?? null;
            $hasNextPage = $response['pageInfo']['hasNextPage'] ?? false;

        } while ($hasNextPage);

        return $labels;
    }
}
