<?php
// Symfony migrated app - by Jakub Winkler <jwinkler@qoliber.com>

declare(strict_types=1);

namespace App\Factory;

use OpenSearch\Client;
use OpenSearch\ClientBuilder;

class OpenSearchClientFactory
{
    public function __construct(
        private readonly string $host,
        private readonly int $port,
        private readonly ?string $username,
        private readonly ?string $password,
        private readonly bool $useTls,
        private readonly bool $verifyTls,
    ) {}

    public function create(): Client
    {
        $scheme = $this->useTls ? 'https' : 'http';
        $host = preg_replace('#^https?://#', '', $this->host);

        $builder = ClientBuilder::create()
            ->setHosts(["{$scheme}://{$host}:{$this->port}"])
            ->setSSLVerification($this->verifyTls);

        if ($this->username && $this->password) {
            $builder->setBasicAuthentication($this->username, $this->password);
        }

        return $builder->build();
    }
}
