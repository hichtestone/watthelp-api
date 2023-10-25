<?php

declare(strict_types=1);

namespace App\Model\Turpe;

/**
 * CS = b*P + c*E
 * b: Coefficient pondérateur de la puissance en c€/kVA == $csCoeffPower
 * P: puissance souscrite
 * c: Coefficient pondérateur de l'énergie en c€/kWh == $csCoeffEnergy
 * E: Quantité consommée en kWh
 */
class TurpeData
{
    private int $cg; // divide by 10^5 to get in c€/year
    private int $cc; // divide by 10^5 to get in c€/year
    
    /**
     * For now, it can only be LU (Longue Utilisation)
     * Divide by 10^5 to get in c€/kW
     */
    private int $csCoeffPower;

    /**
     * For now, it can only be LU (Longue Utilisation)
     * Divide by 10^5 to get in c€/kWh
     */
    private int $csCoeffEnergy;

    private \DateTimeInterface $startedAt;
    private ?\DateTimeInterface $finishedAt = null;

    /**
     * Amounts are expected in cents
     */
    public function __construct(int $cg, int $cc, int $csCoeffPower, float $csCoeffEnergy, \DateTimeInterface $startedAt, ?\DateTimeInterface $finishedAt = null)
    {
        $this->cg = $cg*10**5;
        $this->cc = $cc*10**5;
        $this->csCoeffPower = $csCoeffPower*10**5;
        $this->csCoeffEnergy = intval($csCoeffEnergy*10**5);
        $this->startedAt = $startedAt;
        $this->finishedAt = $finishedAt;
    }

    public function getCg(): int
    {
        return $this->cg;
    }

    public function getCc(): int
    {
        return $this->cc;
    }

    public function getCsCoeffPower(): int
    {
        return $this->csCoeffPower;
    }

    public function getCsCoeffEnergy(): int
    {
        return $this->csCoeffEnergy;
    }

    public function getStartedAt(): \DateTimeInterface
    {
        return $this->startedAt;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }
}