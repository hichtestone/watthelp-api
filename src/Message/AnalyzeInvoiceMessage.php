<?php

declare(strict_types=1);

namespace App\Message;

class AnalyzeInvoiceMessage
{
    private array $filters;
    private int $userId;

    public function __construct(array $filters, int $userId)
    {
        $this->filters = $filters;
        $this->userId = $userId;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}