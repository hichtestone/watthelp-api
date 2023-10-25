<?php

declare(strict_types=1);

namespace App\Message;

class ExportMessage
{
    public const FORMAT_EXCEL = 'excel';
    public const FORMAT_PDF = 'pdf';
    public const AVAILABLE_FORMATS = [
        self::FORMAT_EXCEL,
        self::FORMAT_PDF,
    ];

    public const TYPE_BUDGET = 'budget';
    public const TYPE_ANOMALY = 'anomaly';
    public const TYPE_DELIVERY_POINT = 'delivery_point';
    public const TYPE_DELIVERY_POINT_INVOICE = 'delivery_point_invoice';
    public const TYPE_PRICING = 'pricing';
    public const AVAILABLE_TYPES = [
        self::TYPE_BUDGET,
        self::TYPE_ANOMALY,
        self::TYPE_DELIVERY_POINT,
        self::TYPE_DELIVERY_POINT_INVOICE,
        self::TYPE_PRICING
    ];

    private int $userId;
    private string $format;
    private array $filters;
    private string $type;

    public function __construct(int $userId, string $type, array $filters, string $format)
    {
        $this->userId = $userId;
        $this->format = $format;
        $this->filters = $filters;
        $this->type = $type;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
