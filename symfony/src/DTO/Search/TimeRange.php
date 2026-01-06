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

class TimeRange
{
    public function __construct(
        public ?\DateTimeInterface $from = null,
        public ?\DateTimeInterface $to = null,
        public string $field = 'created_at',
    ) {}

    public function toFilter(): Filter
    {
        $range = [];
        if ($this->from !== null) {
            $range['gte'] = $this->from->format(\DateTimeInterface::ATOM);
        }
        if ($this->to !== null) {
            $range['lte'] = $this->to->format(\DateTimeInterface::ATOM);
        }

        return new Filter(field: $this->field, value: $range, type: FilterType::RANGE);
    }
}
