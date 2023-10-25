<?php

declare(strict_types=1);

namespace App\Model;

class Year implements \Iterator
{
    public const JANUARY   = 1;
    public const FEBRUARY  = 2;
    public const MARCH     = 3;
    public const APRIL     = 4;
    public const MAY       = 5;
    public const JUNE      = 6;
    public const JULY      = 7;
    public const AUGUST    = 8;
    public const SEPTEMBER = 9;
    public const OCTOBER   = 10;
    public const NOVEMBER  = 11;
    public const DECEMBER  = 12;

    private array $months;
    private int $currentMonth = self::JANUARY;

    public function __construct(?int $initialValue = 0)
    {
        $this->months = [
            self::JANUARY => $initialValue,
            self::FEBRUARY => $initialValue,
            self::MARCH => $initialValue,
            self::APRIL => $initialValue,
            self::MAY => $initialValue,
            self::JUNE => $initialValue,
            self::JULY => $initialValue,
            self::AUGUST => $initialValue,
            self::SEPTEMBER => $initialValue,
            self::OCTOBER => $initialValue,
            self::NOVEMBER => $initialValue,
            self::DECEMBER => $initialValue
        ];
    }

    public function getValues(): array
    {
        return array_values($this->months);
    }

    public function getMonths(): array
    {
        return $this->months;
    }

    public function getMonthValue(int $month)
    {
        return $this->months[$month] ?? null;
    }

    public function getPreviousMonthValue()
    {
        return $this->getMonthValue($this->currentMonth-1);
    }

    public function setCurrentMonth(int $currentMonth): void
    {
        $this->currentMonth = $currentMonth;
    }

    public function setMonthValue(int $month, $value): void
    {
        if (array_key_exists($month, $this->months)) {
            $this->months[$month] = $value;
        }
    }

    public function setCurrentMonthValue($value): void
    {
        $this->setMonthValue($this->currentMonth, $value);
    }

    public function setPreviousMonthValue($value): void
    {
        $this->setMonthValue($this->currentMonth-1, $value);
    }

    public function current()
    {
        return $this->months[$this->currentMonth];
    }

    public function next(): void
    {
        ++$this->currentMonth;
    }

    public function key(): int
    {
        return $this->currentMonth;
    }

    public function valid(): bool
    {
        return $this->currentMonth >= self::JANUARY && $this->currentMonth <= self::DECEMBER;
    }

    public function rewind(): void
    {
        $this->currentMonth = self::JANUARY;
    }
}