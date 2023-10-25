<?php

declare(strict_types=1);

namespace App\Model;

class Period
{
    private \DateTimeInterface $start;
    private \DateTimeInterface $end;

    public function __construct(\DateTimeInterface $start, \DateTimeInterface $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function getStart(): \DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }

    /**
     * This assumes that the period doesn't span over multiple years
     */
    public function getYear(): int
    {
        return intval($this->start->format('Y'));
    }
}