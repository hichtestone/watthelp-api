<?php

namespace App\Entity\Invoice;

use App\Entity\ImportReport;
use App\Entity\Invoice\Analysis\ItemAnalysis;
use App\Entity\User;
use App\Repository\Invoice\AnomalyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AnomalyRepository::class)
 * @Gedmo\TranslationEntity(class=AnomalyTranslation::class)
 */
class Anomaly
{
    public const TYPE_SUBSCRIPTION = 'subscription';
    public const TYPE_CONSUMPTION = 'consumption';
    public const TYPE_TURPE = 'turpe';
    public const TYPE_DATE = 'date';
    public const TYPE_INDEX = 'index';
    public const TYPE_UNIT_PRICE = 'unit_price';
    public const TYPE_AMOUNT = 'amount';
    public const TYPE_DELIVERY_POINT_CHANGE = 'delivery_point_change';

    public const DELIVERY_POINT_CHANGE_POWER_NULL = 'power_null';
    public const DELIVERY_POINT_CHANGE_POWER_CHANGED = 'power_changed';
    public const DELIVERY_POINT_CHANGE_ADDRESS_CHANGED = 'address_changed';

    public const AVAILABLE_TYPES = [
        self::TYPE_SUBSCRIPTION,
        self::TYPE_CONSUMPTION,
        self::TYPE_TURPE,
        self::TYPE_DATE,
        self::TYPE_INDEX,
        self::TYPE_UNIT_PRICE,
        self::TYPE_AMOUNT,
        self::TYPE_DELIVERY_POINT_CHANGE
    ];

    public const STATUS_SOLVED = 'solved';
    public const STATUS_UNSOLVED = 'unsolved';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_IGNORED = 'ignored';

    public const AVAILABLE_STATUS = [
        self::STATUS_SOLVED,
        self::STATUS_UNSOLVED,
        self::STATUS_PROCESSING,
        self::STATUS_IGNORED
    ];

    public const PROFIT_CLIENT = 'client';
    public const PROFIT_PROVIDER = 'provider';
    public const PROFIT_NONE = 'none';

    public const AVAILABLE_PROFITS = [
        self::PROFIT_CLIENT,
        self::PROFIT_PROVIDER,
        self::PROFIT_NONE
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     *
     * @Groups("default")
     * @Groups("restricted")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, unique=true)
     */
    private ?string $reference = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Invoice\Analysis\ItemAnalysis", mappedBy="anomaly")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     *
     * @Groups("anomaly_item_analysis")
     */
    private ?ItemAnalysis $itemAnalysis = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Translatable
     *
     * @Groups("default")
     */
    private ?string $appliedRules = null;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     *
     * @Groups("default")
     */
    private ?string $oldValue = null;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * 
     * @Groups("default")
     */
    private ?string $currentValue = null;

    /**
     * @ORM\Column(type="string", nullable=true, length=255)
     * @Gedmo\Translatable
     * 
     * @Groups("default")
     */
    private ?string $expectedValue = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invoice\Anomaly\Note", mappedBy="anomaly", cascade={"persist", "remove"})
     *
     * @Groups("anomaly_notes")
     */
    private Collection $notes;

    /**
     * @ORM\Column(type="enumTypeAnomalyType")
     *
     * @Groups("default")
     */
    private string $type;

    /**
     * @ORM\Column(type="enumTypeAnomalyStatus")
     *
     * @Groups("default")
     * @Groups("restricted")
     */
    private string $status = self::STATUS_UNSOLVED;

    /**
     * @ORM\Column(type="text")
     * @Gedmo\Translatable
     *
     * @Groups("default")
     */
    private string $content;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     *
     * @Groups("default")
     */
    private ?int $total = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     *
     * @Groups("default")
     */
    private ?float $totalPercentage = null;

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

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ImportReport", inversedBy="anomalies", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Groups("anomaly_import_report")
     */
    private ?ImportReport $importReport = null;

    /**
     * @ORM\Column(type="enumAnomalyProfit")
     *
     * @Groups("default")
     */
    private string $profit = self::PROFIT_NONE;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): void
    {
        $this->reference = $reference;
    }

    public function getItemAnalysis(): ?ItemAnalysis
    {
        return $this->itemAnalysis;
    }

    public function setItemAnalysis(?ItemAnalysis $itemAnalysis): void
    {
        $this->itemAnalysis = $itemAnalysis;
    }

    public function getAppliedRules(): ?string
    {
        return $this->appliedRules;
    }

    public function setAppliedRules(?string $appliedRules): void
    {
        $this->appliedRules = $appliedRules;
    }

    public function getOldValue(): ?string
    {
        return $this->oldValue;
    }

    public function setOldValue(?string $oldValue): void
    {
        $this->oldValue = $oldValue;
    }

    public function getCurrentValue(): ?string
    {
        return $this->currentValue;
    }

    public function setCurrentValue(?string $currentValue): void
    {
        $this->currentValue = $currentValue;
    }

    public function getExpectedValue(): ?string
    {
        return $this->expectedValue;
    }

    public function setExpectedValue(?string $expectedValue): void
    {
        $this->expectedValue = $expectedValue;
    }

    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function setNotes(Collection $notes): void
    {
        $this->notes = $notes;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }
    
    public function getTotalPercentage(): ?float
    {
        return $this->totalPercentage;
    }

    public function setTotalPercentage(?float $totalPercentage): void
    {
        $this->totalPercentage = $totalPercentage;
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

    public function getImportReport(): ?ImportReport
    {
        return $this->importReport;
    }

    public function setImportReport(?ImportReport $importReport): void
    {
        $this->importReport = $importReport;
    }

    public function getProfit(): string
    {
        return $this->profit;
    }

    public function setProfit(string $profit): void
    {
        $this->profit = $profit;
    }
}