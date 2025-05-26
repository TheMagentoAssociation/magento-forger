<?php

namespace App\DataTransferObjects\GitHub;

final readonly class IssueCounts
{
    public function __construct(
        public int $total,
        public int $open,
        public int $closed,
    ) {}

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
