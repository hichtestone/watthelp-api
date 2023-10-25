<?php

namespace App\Tests\Logger\Profiler;

use Doctrine\DBAL\Logging\SQLLogger;

class Db
{
    private SQLLogger $logger;

    public function __construct(SQLLogger $logger)
    {
        $this->logger = $logger;
    }

    public function enable(bool $enabled): void
    {
        $this->logger->enabled = $enabled;
    }

    public function getQueries(): array
    {
        return $this->logger->queries;
    }

    public function hasTheDbBeenUpdated(): bool
    {
        $queries = $this->logger->queries;
        foreach ($queries as $query) {
            if (preg_match('#^(INSERT|UPDATE|DELETE)#', $query['sql'])) {
                return true;
            }
        }

        return false;
    }
}