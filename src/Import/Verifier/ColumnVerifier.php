<?php

declare(strict_types=1);

namespace App\Import\Verifier;

class ColumnVerifier
{
    private string $cell;
    private string $expectedValue;

    public function __construct(string $cell, string $expectedValue)
    {
        $this->cell = $cell;
        $this->expectedValue = $expectedValue;
    }

    public function getCell(): string
    {
        return $this->cell;
    }

    public function getExpectedValue(): string
    {
        return $this->expectedValue;
    }
}