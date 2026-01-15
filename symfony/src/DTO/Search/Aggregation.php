<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\DTO\Search;

class Aggregation
{
    /**
     * @param array<string, mixed> $definition
     */
    public function __construct(
        public string $name,
        public array $definition,
    ) {}
}
