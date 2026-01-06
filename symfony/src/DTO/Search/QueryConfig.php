<?php
/**
 * Created by Qoliber
 *
 * @category    Qoliber
 * @package     Qoliber_MagentoForger
 * @author      Jakub Winkler <jwinkler@qoliber.com>
 */

declare(strict_types=1);

namespace App\DTO\Search;

class QueryConfig
{
    /**
     * @param array<int, \App\DTO\Search\Filter> $filters
     * @param array<int, \App\DTO\Search\Aggregation> $aggregations
     * @param array<int, string> $fields
     * @param array<string, string>|null $sort
     */
    public function __construct(
        public array $filters = [],
        public array $aggregations = [],
        public array $fields = [],
        public int $size = 0,
        public ?array $sort = null,
    ) {}
}
