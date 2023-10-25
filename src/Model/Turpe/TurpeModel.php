<?php

declare(strict_types=1);

namespace App\Model\Turpe;

class TurpeModel
{
    private int $cg;
    private int $cc;
    private int $csFixed;
    private int $csVariable;
    private int $total;

    public function __construct(int $cg, int $cc, int $csFixed, int $csVariable)
    {
        $this->cg = $cg;
        $this->cc = $cc;
        $this->csFixed = $csFixed;
        $this->csVariable = $csVariable;
        $this->total = $cg + $cc + $csFixed + $csVariable;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getCg(): int
    {
        return $this->cg;
    }

    public function getCc(): int
    {
        return $this->cc;
    }

    public function getCsFixed(): int
    {
        return $this->csFixed;
    }

    public function getCsVariable(): int
    {
        return $this->csVariable;
    }

    public function getCs(): int
    {
        return $this->csFixed + $this->csVariable;
    }

    /**
     * Returns the sum of the CG, CC and the fixed part of the CS
     */
    public function getFixedTotal(): int
    {
        return $this->cg + $this->cc + $this->csFixed;
    }
}