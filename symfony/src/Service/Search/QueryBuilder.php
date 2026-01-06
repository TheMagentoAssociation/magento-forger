<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\Service\Search;

use App\DTO\Search\Aggregation;
use App\DTO\Search\Filter;
use App\DTO\Search\FilterType;
use App\DTO\Search\QueryConfig;

class QueryBuilder
{
    /** @var array<int, \App\DTO\Search\Filter> */
    protected array $filters = [];

    /** @var array<string, array<string, mixed>> */
    protected array $aggregations = [];

    /** @var array<int, string> */
    protected array $sourceFields = [];

    protected int $size = 0;

    /** @var array<int|string, mixed> */
    protected array $sort = [];

    /** @var array<int, string> */
    protected array $keywordFields = [
        'state',
        'author',
        'status',
    ];

    public function addFilter(Filter $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function addAggregation(Aggregation $agg): self
    {
        $this->aggregations[$agg->name] = $agg->definition;

        return $this;
    }

    public function setSize(int $size): self
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @param array<int, string> $fields
     */
    public function selectFields(array $fields): self
    {
        $this->sourceFields = $fields;

        return $this;
    }

    /**
     * @param array<int|string, mixed>|string $sort
     */
    public function addSort(array|string $sort): self
    {
        if (is_array($sort)) {
            $this->sort = array_merge($this->sort, $sort);
        } else {
            $this->sort[] = $sort;
        }

        return $this;
    }

    public function fromConfig(QueryConfig $config): self
    {
        foreach ($config->filters as $filter) {
            $this->addFilter($filter);
        }

        foreach ($config->aggregations as $agg) {
            $this->addAggregation($agg);
        }

        $this->selectFields($config->fields);
        $this->setSize($config->size);

        if (!empty($config->sort)) {
            $this->addSort($config->sort);
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $must = [];

        foreach ($this->filters as $filter) {
            $field = $filter->field;
            if ($filter->type === FilterType::TERM && in_array($field, $this->keywordFields, true)) {
                $field .= '.keyword';
            }

            $must[] = match ($filter->type) {
                FilterType::RANGE => ['range' => [$field => $filter->value]],
                FilterType::TERMS => ['terms' => [$field => $filter->value]],
                default => ['term' => [$field => $filter->value]],
            };
        }

        $query = [
            'size' => $this->size,
            'query' => ['bool' => ['must' => $must]],
        ];

        if (!empty($this->aggregations)) {
            $query['aggs'] = $this->aggregations;
        }

        if (!empty($this->sourceFields)) {
            $query['_source'] = $this->sourceFields;
        }

        if (!empty($this->sort)) {
            $query['sort'] = $this->sort;
        }

        return $query;
    }
}
