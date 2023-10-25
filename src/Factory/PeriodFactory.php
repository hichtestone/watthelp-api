<?php

declare(strict_types=1);

namespace App\Factory;

use App\Model\Period;

class PeriodFactory
{
    public static function createFromYear(int $year): Period
    {
        $start = \DateTime::createFromFormat('Y-m-d', "$year-01-01");
        $end   = \DateTime::createFromFormat('Y-m-d', "$year-12-31");
       
        return new Period($start, $end);
    }

    public static function createFromSplitUpDates(int $year, int $periodStartDay, int $periodStartMonth, int $periodEndDay, int $periodEndMonth): Period
    {
        $start = \DateTime::createFromFormat('Y-n-j', "$year-$periodStartMonth-$periodStartDay");
        $end   = \DateTime::createFromFormat('Y-n-j', "$year-$periodEndMonth-$periodEndDay");
        
        return new Period($start, $end);
    }

    public static function createFromStrings(string $start, string $end, string $format = 'Y-m-d'): Period
    {
        $start = \DateTime::createFromFormat($format, $start);
        $end   = \DateTime::createFromFormat($format, $end);

        return new Period($start, $end);
    }
}