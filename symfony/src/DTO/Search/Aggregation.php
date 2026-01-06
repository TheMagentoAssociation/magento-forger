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
