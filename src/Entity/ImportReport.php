<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Invoice\Anomaly;
use App\Repository\ImportReportRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ImportReportRepository::class)
 */
class ImportReport implements HasUserInterface
{
    public const STATUS_OK = 'ok';
    public const STATUS_ERROR = 'error';
    public const STATUS_WARNING = 'warning';
    public const AVAILABLE_STATUSES = [
        self::STATUS_OK,
        self::STATUS_ERROR,
        self::STATUS_WARNING
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * 
     * @Groups("default")
     * @Groups("restricted")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="importReports")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @ORM\Column(type="enumTypeImportReportStatus")
     * @SWG\Property(property="status", type="string", description="required")
     *
     * @Groups("default")
     */
    private string $status = self::STATUS_ERROR;

    /**
     * @ORM\Column(type="array", nullable=true)
     * @SWG\Property(property="messages", type="array", @SWG\Items(type="string"))
     *
     * @Groups("default")
     */
    private array $messages = [];

    /**
     * Stores the invoices imported if status is ok
     * Or stores the invoices already imported if status is error
     * 
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="importReport")
     *
     * @Groups("import_report_invoices")
     */
    private Collection $invoices;

    /**
     * Delivery points imported, if any
     *
     * @ORM\ManyToMany(targetEntity=DeliveryPoint::class, mappedBy="importReports")
     *
     * @Groups("import_report_delivery_points")
     */
    private Collection $deliveryPoints;

    /**
     * Anomalies found, if any
     *
     * @ORM\OneToMany(targetEntity=Anomaly::class, mappedBy="importReport")
     *
     * @Groups("import_report_anomalies")
     */
    private Collection $anomalies;

    /**
     * Budgets imported, if any
     *
     * @ORM\ManyToMany(targetEntity=Budget::class, mappedBy="importReports")
     *
     * @Groups("import_report_budgets")
     */
    private Collection $budgets;


    /**
     *
     * @ORM\ManyToMany(targetEntity=Pricing::class, mappedBy="importReports")
     *
     * @Groups("import_report_pricings")
     */
    private Collection $pricings;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     * @SWG\Property(property="created_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *
     * @Groups("default")
     */
    private DateTimeInterface $createdAt;

    /**
     * @ORM\OneToOne(targetEntity=Import::class, inversedBy="importReport", cascade={"remove"})
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups("import_report_import")
     */
    private Import $import;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->invoices = new ArrayCollection();
        $this->deliveryPoints = new ArrayCollection();
        $this->anomalies = new ArrayCollection();
        $this->budgets = new ArrayCollection();
        $this->pricings = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status)
    {
        $this->status = $status;
    }

    /**
     * @return string[]
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    public function setMessages(array $messages): void
    {
        $this->messages = $messages;
    }

    public function addMessage(string $message): void
    {
        if (!in_array($message, $this->messages)) {
            $this->messages[] = $message;
        }
    }

    public function getInvoices(): ArrayCollection
    {
        return new ArrayCollection($this->invoices->getValues());
    }

    public function setInvoices(Collection $invoices): void
    {
        foreach ($this->invoices as $invoice) {
            if (!$invoices->contains($invoice)) {
                $this->invoices->removeElement($invoice);
            }
        }
        foreach ($invoices as $invoice) {
            $this->addInvoice($invoice);
        }
    }

    public function addInvoice(Invoice $invoice): void
    {
        if ($this->invoices->contains($invoice)) {
            return;
        }
        $invoice->setImportReport($this);
        $this->invoices->add($invoice);
    }

    public function getDeliveryPoints(): ArrayCollection
    {
        return new ArrayCollection($this->deliveryPoints->getValues());
    }

    public function setDeliveryPoints(Collection $deliveryPoints): void
    {
        foreach ($this->deliveryPoints as $deliveryPoint) {
            if (!$deliveryPoints->contains($deliveryPoint)) {
                $this->deliveryPoints->removeElement($deliveryPoint);
            }
        }
        foreach ($deliveryPoints as $deliveryPoint) {
            $this->addDeliveryPoint($deliveryPoint);
        }
    }

    public function addDeliveryPoint(DeliveryPoint $deliveryPoint): void
    {
        if ($this->deliveryPoints->contains($deliveryPoint)) {
            return;
        }
        $deliveryPoint->addImportReport($this);
        $this->deliveryPoints->add($deliveryPoint);
    }

    public function getPricings(): ArrayCollection
    {
        return new ArrayCollection($this->pricings->getValues());
    }

    public function setPricings(Collection $pricings): void
    {
        foreach ($this->pricings as $pricing) {
            if (!$pricings->contains($pricing)) {
                $this->pricings->removeElement($pricing);
            }
        }
        foreach ($pricings as $pricing) {
            $this->addPricing($pricing);
        }
    }

    public function addPricing(Pricing $pricing): void
    {
        if ($this->pricings->contains($pricing)) {
            return;
        }
        $pricing->addImportReport($this);
        $this->pricings->add($pricing);
    }


    public function getAnomalies(): ArrayCollection
    {
        return new ArrayCollection($this->anomalies->getValues());
    }

    public function setAnomalies(Collection $anomalies): void
    {
        foreach ($this->anomalies as $anomaly) {
            if (!$anomalies->contains($anomaly)) {
                $this->anomalies->removeElement($anomaly);
            }
        }
        foreach ($anomalies as $anomaly) {
            $this->addAnomaly($anomaly);
        }
    }

    public function addAnomaly(Anomaly $anomaly): void
    {
        if ($this->anomalies->contains($anomaly)) {
            return;
        }
        $anomaly->setImportReport($this);
        $this->anomalies->add($anomaly);
    }

    public function getBudgets(): ArrayCollection
    {
        return new ArrayCollection($this->budgets->getValues());
    }

    public function setBudgets(Collection $budgets): void
    {
        foreach ($this->budgets as $budget) {
            if (!$budgets->contains($budget)) {
                $this->budgets->removeElement($budget);
            }
        }
        foreach ($budgets as $budget) {
            $this->addBudget($budget);
        }
    }

    public function addBudget(Budget $budget): void
    {
        if ($this->budgets->contains($budget)) {
            return;
        }
        $budget->addImportReport($this);
        $this->budgets->add($budget);
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getImport(): Import
    {
        return $this->import;
    }

    public function setImport(Import $import): void
    {
        $this->import = $import;
    }
}
