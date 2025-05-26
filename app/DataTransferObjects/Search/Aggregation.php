<?php


namespace App\DataTransferObjects\Search;

class Aggregation
{
    public function __construct(
        public string $name,
        public array $definition
    ) {}
}
