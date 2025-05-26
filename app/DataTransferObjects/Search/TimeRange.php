<?php
namespace App\DataTransferObjects\Search;

use Carbon\Carbon;

class TimeRange
{
    public function __construct(
        public ?Carbon $from = null,
        public ?Carbon $to = null,
        public string $field = 'created_at',
    ) {}

    public function toFilter(): Filter
    {
        $range = [];
        if ($this->from) $range['gte'] = $this->from->toIso8601String();
        if ($this->to)   $range['lte'] = $this->to->toIso8601String();

        return new Filter(field: $this->field, value: $range, type: 'range');
    }
}
