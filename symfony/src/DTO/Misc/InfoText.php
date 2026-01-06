<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\DTO\Misc;

class InfoText
{
    /**
     * @param array<int, string> $paragraphs
     */
    public function __construct(
        public readonly string $title,
        public readonly array $paragraphs,
    ) {}
}
