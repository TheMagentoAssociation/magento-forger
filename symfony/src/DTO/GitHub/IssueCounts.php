<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\DTO\GitHub;

final readonly class IssueCounts
{
    public function __construct(
        public int $total,
        public int $open,
        public int $closed,
    ) {}

    /**
     * @param array<string, mixed> $data
     */
    public static function fromGraphQL(array $data): self
    {
        $repo = $data['repository'] ?? [];

        return new self(
            total: $repo['issues']['totalCount'] ?? 0,
            open: $repo['openIssues']['totalCount'] ?? 0,
            closed: $repo['closedIssues']['totalCount'] ?? 0,
        );
    }

    public function summary(): string
    {
        return "Total: $this->total, Open: $this->open, Closed: $this->closed";
    }
}
