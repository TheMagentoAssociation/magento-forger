<?php

namespace App\DataTransferObjects\Search;

namespace App\DataTransferObjects\Search;

class Filter
{
    public function __construct(
        public string $field,
        public FilterType $type = FilterType::TERM,
        public mixed $value = null,
    ) {}
}
