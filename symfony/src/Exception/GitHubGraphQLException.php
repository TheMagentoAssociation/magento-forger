<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\Exception;

class GitHubGraphQLException extends \Exception
{
    /** @var array<string, mixed> */
    protected array $context;

    /**
     * @param array<string, mixed> $context
     */
    public function __construct(string $message, array $context = [], int $code = 0, ?\Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function __toString(): string
    {
        return sprintf(
            "%s: %s\nContext: %s",
            static::class,
            $this->getMessage(),
            json_encode($this->context, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }
}
