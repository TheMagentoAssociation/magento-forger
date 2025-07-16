<?php

namespace App\DataTransferObjects\Misc;

class InfoText
{
    /**
     * @param string $title
     * @param string[] $paragraphs
     */
    public function __construct(
        public readonly string $title,
        public readonly array $paragraphs,
    ) {}
}
