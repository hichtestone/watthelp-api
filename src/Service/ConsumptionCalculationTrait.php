<?php

declare(strict_types=1);

namespace App\Service;

trait ConsumptionCalculationTrait
{
    private function calculateMonthRatio(\DateTimeInterface $start, \DateTimeInterface $end): float
    {
        $numberOfDays = intval($end->diff($start)->format('%a')); // number of days consumed for this month
        $daysInMonth = intval($start->format('t')); // how many days this month has
        $ratio = floatval($numberOfDays / $daysInMonth);

        return $ratio;
    }

    /**
     * Returns a ratio between 0 and 1
     * It uses the number of days that aren't included in the interval to determine what the ratio should be
     * If the consumption is entirely included in the interval, the function returns 1
     * If the consumption doesn't have startedAt/finishedAt and an interval was specified, then we should ignore the consumption by setting its ratio to 0
     */
    private function calculateDayRatio(?\DateTimeInterface $consumptionStartedAt, ?\DateTimeInterface $consumptionFinishedAt, ?\DateTimeInterface $intervalStart, ?\DateTimeInterface $intervalEnd): float
    {
        if (!$consumptionStartedAt && !$consumptionFinishedAt && ($intervalStart || $intervalEnd)) {
            return 0;
        }
        
        $ratio = 1;
        $daysOffInterval = 0; // number of days of the consumption that aren't in the interval
        if ($intervalStart && $consumptionStartedAt < $intervalStart) {
            $daysOffInterval += $consumptionStartedAt->diff($intervalStart)->days;
        }
        if ($intervalEnd && $consumptionFinishedAt > $intervalEnd) {
            $daysOffInterval += $consumptionFinishedAt->diff($intervalEnd)->days;
        }

        if ($daysOffInterval) {
            $consumptionDurationInDays = $consumptionStartedAt->diff($consumptionFinishedAt)->days;
            $ratio = ($consumptionDurationInDays - $daysOffInterval) / $consumptionDurationInDays;
        }

        return $ratio;
    }
}