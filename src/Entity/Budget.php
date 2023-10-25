<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Budget\DeliveryPointBudget;
use App\Entity\ImportReport;
use App\Repository\BudgetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=BudgetRepository::class)
 */
class Budget implements HasClientInterface
{
    public const EXPAND_DATA_CALCULATED_INFO = 'calculated_info';
    public const EXPAND_DATA_PREVIOUS_BUDGET = 'previous_budget';

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
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="budgets")
     * @ORM\JoinColumn(nullable=false)
     */
    private Client $client;

    /**
     * @ORM\Column(type="integer")
     * 
     * @Groups("default")
     * @Groups("restricted")
     */
    private int $year;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * 
     * @Groups("default")
     */
    private ?int $totalHours = null;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     * Divide by 10^5 to get the price in cts€/kWh
     * 
     * @Groups("default")
     */
    private ?int $averagePrice = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Divide by 100 to get the consumption in kWh
     * 
     * @Groups("default")
     */
    private ?int $totalConsumption = null;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     * Divide by 10^5 to get the price in cts€
     * 
     * @Groups("default")
     */
    private ?int $totalAmount = null;

    /**
     * @ORM\Column(type="datetime")
     * 
     * @Groups("default")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("default")
     */
    private \DateTimeInterface $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=DeliveryPointBudget::class, mappedBy="budget", cascade={"remove", "persist"}, orphanRemoval=true)
     *
     * @Groups("budget_delivery_point_budgets")
     */
    private Collection $deliveryPointBudgets;

    /**
     * @ORM\ManyToMany(targetEntity=ImportReport::class, inversedBy="budgets", cascade={"persist"})
     * @ORM\JoinColumn()
     *
     * @Groups("budget_import_reports")
     */
    private Collection $importReports;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->deliveryPointBudgets = new ArrayCollection();
        $this->importReports = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): void
    {
        $this->year = $year;
    }

    public function getTotalHours(): ?int
    {
        return $this->totalHours;
    }

    public function setTotalHours(?int $totalHours): void
    {
        $this->totalHours = $totalHours;
    }

    public function getAveragePrice(): ?int
    {
        return $this->averagePrice;
    }

    public function setAveragePrice(?int $averagePrice): void
    {
        $this->averagePrice = $averagePrice;
    }

    public function getTotalConsumption(): ?int
    {
        return $this->totalConsumption;
    }

    public function setTotalConsumption(?int $totalConsumption): void
    {
        $this->totalConsumption = $totalConsumption;
    }

    public function getTotalAmount(): ?int
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(?int $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
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

    public function getDeliveryPointBudgets(): ArrayCollection
    {
        return new ArrayCollection($this->deliveryPointBudgets->getValues());
    }

    public function addDeliveryPointBudget(DeliveryPointBudget $deliveryPointBudget): void
    {
        if ($this->deliveryPointBudgets->contains($deliveryPointBudget)) {
            return;
        }
        $deliveryPointBudget->setBudget($this);
        $this->deliveryPointBudgets->add($deliveryPointBudget);
    }

    public function setDeliveryPointBudgets(Collection $deliveryPointBudgets): void
    {
        foreach ($this->deliveryPointBudgets as $deliveryPointBudget) {
            if (!$deliveryPointBudgets->contains($deliveryPointBudget)) {
                $this->deliveryPointBudgets->removeElement($deliveryPointBudget);
            }
        }
        foreach ($deliveryPointBudgets as $deliveryPointBudget) {
            $this->addDeliveryPointBudget($deliveryPointBudget);
        }
    }

    public function getImportReports(): ArrayCollection
    {
        return new ArrayCollection($this->importReport->getValues());
    }

    public function addImportReport(ImportReport $importReport): void
    {
        if ($this->importReports->contains($importReport)) {
            return;
        }
        $this->importReports->add($importReport);
    }

    public function setImportReports(Collection $importReports): void
    {
        foreach ($this->importReports as $importReport) {
            if (!$importReports->contains($importReport)) {
                $this->importReports->removeElement($importReport);
            }
        }
        foreach ($importReports as $importReport) {
            $this->addImportReport($importReport);
        }
    }
}
