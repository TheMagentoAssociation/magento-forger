<?php

namespace App\DataTransferObjects\Search;

class QueryConfig
{
    public function __construct(
        public array $filters = [],
        public array $aggregations = [],
        public array $fields = [],
        public int $size = 0,
        public ?array $sort = null,
    ) {}
}
