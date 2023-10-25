<?php

declare(strict_types=1);

namespace App\Service;

class AmountConversionService
{
    public function intToHumanReadable(?int $amount, int $decimals = 2, int $conversionPower = 7, string $unit = '€'): string
    {
        if (is_null($amount)) {
            return '';
        }
        $amount = number_format($this->convertAndRound($amount, $decimals, $conversionPower), $decimals, ',', ' ');
        $amount .= $unit;
        return $amount;
    }

    public function intToHumanReadableInCents(?int $amount, int $decimals = 2): string
    {
        return $this->intToHumanReadable($amount, $decimals, 5, 'c€');
    }

    public function percentageToHumanReadable(?int $percentage, int $decimals = 2): string
    {
        return $this->intToHumanReadable($percentage, $decimals, 2, '%');
    }

    public function convertAndRound(int $amount, int $decimals = 2, int $conversionPower = 7): float
    {
        return round(floatval($amount)/(10**$conversionPower), $decimals);
    }

    public function convertInCentsAndRound(int $amount, int $decimals = 2): float
    {
        return $this->convertAndRound($amount, $decimals, 5);
    }
}