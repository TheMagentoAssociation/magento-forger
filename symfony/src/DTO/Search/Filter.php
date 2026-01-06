<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\DTO\Search;

class Filter
{
    public function __construct(
        public string $field,
        public FilterType $type = FilterType::TERM,
        public mixed $value = null,
    ) {}
}
