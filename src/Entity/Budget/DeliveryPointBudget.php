<?php

declare(strict_types=1);

namespace App\Entity\Budget;

use App\Entity\Budget;
use App\Entity\DeliveryPoint;
use App\Entity\HasBudgetInterface;
use App\Repository\Budget\DeliveryPointBudgetRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=DeliveryPointBudgetRepository::class)
 */
class DeliveryPointBudget implements HasBudgetInterface
{
    public const EXPAND_DATA_PREVIOUS_DELIVERY_POINT_BUDGET = 'previous_delivery_point_budget';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups("default")
     * @Groups("restricted")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=DeliveryPoint::class, inversedBy="deliveryPointBudgets")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups("delivery_point_budget_delivery_point")
     */
    private DeliveryPoint $deliveryPoint;

    /**
     * @ORM\ManyToOne(targetEntity=Budget::class, inversedBy="deliveryPointBudgets")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups("delivery_point_budget_budget")
     */
    private Budget $budget;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     *
     * @Groups("default")
     */
    private ?string $installedPower = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Divide by 10^2 to get the percentage
     * 
     * @Groups("default")
     */
    private ?int $equipmentPowerPercentage = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Divide by 10^2 to get the percentage
     * 
     * @Groups("default")
     */
    private ?int $gradation = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @Groups("default")
     */
    private ?int $gradationHours = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Divide by 10^2 to get the consumption in kWh
     * 
     * @Groups("default")
     */
    private ?int $subTotalConsumption = null;

    /**
     * @ORM\Column(type="boolean")
     * 
     * @Groups("default")
     */
    private bool $renovation;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @Groups("default")
     */
    private ?\DateTimeInterface $renovatedAt = null;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     * 
     * @Groups("default")
     */
    private ?string $newInstalledPower = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Divide by 10^2 to get the percentage
     * 
     * @Groups("default")
     */
    private ?int $newEquipmentPowerPercentage = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Divide by 10^2 to get the percentage
     * 
     * @Groups("default")
     */
    private ?int $newGradation = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @Groups("default")
     */
    private ?int $newGradationHours = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Divide by 10^2 to get the consumption in kWh
     * 
     * @Groups("default")
     */
    private ?int $newSubTotalConsumption = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Divide by 10^2 to get the consumption in kWh
     * 
     * @Groups("default")
     */
    private ?int $totalConsumption = null;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     * Divide by 10^5 to get the amount in cts€
     * 
     * @Groups("default")
     */
    private ?int $total = null;

    /**
     * @ORM\Column(type="datetime")
     * 
     * @Groups("default")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * 
     * @Groups("default")
     */
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDeliveryPoint(): DeliveryPoint
    {
        return $this->deliveryPoint;
    }

    public function setDeliveryPoint(DeliveryPoint $deliveryPoint): void
    {
        $this->deliveryPoint = $deliveryPoint;
    }

    public function getBudget(): Budget
    {
        return $this->budget;
    }

    public function setBudget(Budget $budget): void
    {
        $this->budget = $budget;
    }

    public function getInstalledPower(): ?string
    {
        return $this->installedPower;
    }

    public function setInstalledPower(?string $installedPower): void
    {
        $this->installedPower = $installedPower;
    }

    public function getEquipmentPowerPercentage(): ?int
    {
        return $this->equipmentPowerPercentage;
    }

    public function setEquipmentPowerPercentage(?int $equipmentPowerPercentage): void
    {
        $this->equipmentPowerPercentage = $equipmentPowerPercentage;
    }

    public function getGradation(): ?int
    {
        return $this->gradation;
    }

    public function setGradation(?int $gradation): void
    {
        $this->gradation = $gradation;
    }

    public function getGradationHours(): ?int
    {
        return $this->gradationHours;
    }

    public function setGradationHours(?int $gradationHours): void
    {
        $this->gradationHours = $gradationHours;
    }

    public function getSubTotalConsumption(): ?int
    {
        return $this->subTotalConsumption;
    }

    public function setSubTotalConsumption(?int $subTotalConsumption): void
    {
        $this->subTotalConsumption = $subTotalConsumption;
    }

    public function isRenovation(): bool
    {
        return $this->renovation;
    }

    public function setRenovation(bool $renovation): void
    {
        $this->renovation = $renovation;
    }

    public function getRenovatedAt(): ?\DateTimeInterface
    {
        return $this->renovatedAt;
    }

    public function setRenovatedAt(?\DateTimeInterface $renovatedAt): void
    {
        $this->renovatedAt = $renovatedAt;
    }

    public function getNewInstalledPower(): ?string
    {
        return $this->newInstalledPower;
    }

    public function setNewInstalledPower(?string $newInstalledPower): void
    {
        $this->newInstalledPower = $newInstalledPower;
    }

    public function getNewEquipmentPowerPercentage(): ?int
    {
        return $this->newEquipmentPowerPercentage;
    }

    public function setNewEquipmentPowerPercentage(?int $newEquipmentPowerPercentage): void
    {
        $this->newEquipmentPowerPercentage = $newEquipmentPowerPercentage;
    }

    public function getNewGradation(): ?int
    {
        return $this->newGradation;
    }

    public function setNewGradation(?int $newGradation): void
    {
        $this->newGradation = $newGradation;
    }

    public function getNewGradationHours(): ?int
    {
        return $this->newGradationHours;
    }

    public function setNewGradationHours(?int $newGradationHours): void
    {
        $this->newGradationHours = $newGradationHours;
    }

    public function getNewSubTotalConsumption(): ?int
    {
        return $this->newSubTotalConsumption;
    }

    public function setNewSubTotalConsumption(?int $newSubTotalConsumption): void
    {
        $this->newSubTotalConsumption = $newSubTotalConsumption;
    }

    public function getTotalConsumption(): ?int
    {
        return $this->totalConsumption;
    }

    public function setTotalConsumption(?int $totalConsumption): void
    {
        $this->totalConsumption = $totalConsumption;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }
}
