<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Budget\DeliveryPointBudget;
use App\Entity\ImportReport;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Repository\DeliveryPointRepository;
use Bazinga\GeocoderBundle\Mapping\Annotations as Geocoder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=DeliveryPointRepository::class)
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(columns={"client_id", "reference"}),
 *         @ORM\UniqueConstraint(columns={"client_id", "code"})
 *     }
 * )
 * @Gedmo\Loggable
 * @Geocoder\Geocodeable
 */
class DeliveryPoint implements HasClientInterface
{
    public const EXPAND_DATA_POWER_HISTORY = 'power_history';

    public const CREATION_MODE_SCOPE_IMPORT = 'scope_import';
    public const CREATION_MODE_INVOICE_IMPORT = 'invoice_import';
    public const CREATION_MODE_MANUAL = 'manual';

    public const AVAILABLE_CREATION_MODES = [
        self::CREATION_MODE_SCOPE_IMPORT,
        self::CREATION_MODE_INVOICE_IMPORT,
        self::CREATION_MODE_MANUAL
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
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="deliveryPoints")
     * @ORM\JoinColumn(nullable=false)
     * 
     */
    private Client $client;

    /**
     * @ORM\Column(type="string", length=255)
     * @SWG\Property(property="name", type="string", description="required")
     * 
     * @Groups("default")
     * @Groups("restricted")
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     * @SWG\Property(property="reference", type="string", description="required")
     * 
     * @Groups("default")
     */
    private string $reference;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @SWG\Property(property="code", type="string")
     * 
     * @Groups("default")
     */
    private ?string $code = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @SWG\Property(property="address", type="string", description="required")
     * @Geocoder\Address
     *
     * @Groups("default")
     */
    private string $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @SWG\Property(property="latitude", type="string")
     * @Geocoder\Latitude
     *
     * @Groups("default")
     */
    private ?string $latitude = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @SWG\Property(property="longitude", type="string")
     * @Geocoder\Longitude
     *
     * @Groups("default")
     */
    private ?string $longitude = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @SWG\Property(property="meter_reference", type="string", description="required")
     * 
     * @Groups("default")
     */
    private string $meterReference;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=1)
     * @Gedmo\Versioned
     * @SWG\Property(property="power", type="string", description="required")
     * 
     * @Groups("default")
     */
    private string $power;

    /**
     * @ORM\Column(type="enumTypeDeliveryPointCreationMode")
     * @SWG\Property(property="creationMode", type="string", description="required - enum(scope_import|invoice_import|manual)")
     *
     * @Groups("default")
     */
    private string $creationMode = self::CREATION_MODE_MANUAL;

    /**
     * @ORM\Column(type="datetime")
     * @SWG\Property(property="created_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @SWG\Property(property="updated_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Contract::class, inversedBy="deliveryPoints")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups("delivery_point_contract")
     */
    private ?Contract $contract = null;

    /**
     * @ORM\ManyToOne(targetEntity=File::class)
     * @ORM\JoinColumn(nullable=true)
     *
     * @SWG\Property(description="The delivery point photo.")
     *
     * @Groups("delivery_point_photo")
     */
    private ?File $photo = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @SWG\Property(property="description", type="string", description="")
     *
     * @Groups("default")
     */
    private ?string $description = null;

    /**
     * @ORM\OneToMany(targetEntity=DeliveryPointInvoice::class, mappedBy="deliveryPoint", cascade={"remove","persist"})
     *
     * @Groups("delivery_point_delivery_point_invoices")
     */
    private Collection $deliveryPointInvoices;

    /**
     * @ORM\Column(type="boolean")
     * @SWG\Property(property="is_in_scope", type="boolean", description="required")
     *
     * @Groups("default")
     * @Groups("restricted")
     */
    private bool $isInScope = false;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @SWG\Property(property="scope_date", type="string", description="ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *
     * @Groups("default")
     */
    private ?\DateTimeInterface $scopeDate = null;

    /**
     * @ORM\ManyToMany(targetEntity=ImportReport::class, inversedBy="deliveryPoints", cascade={"persist"})
     * @ORM\JoinColumn()
     *
     * @Groups("delivery_point_import_reports")
     */
    private Collection $importReports;

    /**
     * @ORM\OneToMany(targetEntity=DeliveryPointBudget::class, mappedBy="deliveryPoint", cascade={"remove"})
     *
     * @Groups("delivery_point_delivery_point_budgets")
     */
    private Collection $deliveryPointBudgets;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->deliveryPointInvoices = new ArrayCollection();
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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): void
    {
        $this->code = $code;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getCreationMode(): string
    {
        return $this->creationMode;
    }

    public function setCreationMode(string $creationMode): void
    {
        $this->creationMode = $creationMode;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): void
    {
        $this->latitude = $latitude;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): void
    {
        $this->longitude = $longitude;
    }

    public function getMeterReference(): string
    {
        return $this->meterReference;
    }

    public function setMeterReference(string $meterReference): void
    {
        $this->meterReference = $meterReference;
    }

    public function getPower(): string
    {
        return $this->power;
    }

    public function setPower(string $power): void
    {
        $this->power = $power;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): void
    {
        $this->contract = $contract;
    }

    public function getPhoto(): ?File
    {
        return $this->photo;
    }

    public function setPhoto(?File $photo): void
    {
        $this->photo = $photo;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getDeliveryPointInvoices(): ArrayCollection
    {
        return new ArrayCollection($this->deliveryPointInvoices->getValues());
    }

    public function sortDeliveryPointInvoices(): void
    {
        $this->deliveryPointInvoices = $this->getDeliveryPointInvoicesSorted();
    }

    /**
     * Sorted by invoice.emittedAt ASC
     */
    public function getDeliveryPointInvoicesSorted(): ArrayCollection
    {
        $dpi = $this->deliveryPointInvoices->toArray();
        usort($dpi, fn(DeliveryPointInvoice $a, DeliveryPointInvoice $b) =>
            $a->getInvoice()->getEmittedAt() <=> $b->getInvoice()->getEmittedAt()
        );
        return new ArrayCollection($dpi);
    }

    public function addDeliveryPointInvoice(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        if ($this->deliveryPointInvoices->contains($deliveryPointInvoice)) {
            return;
        }
        $deliveryPointInvoice->setDeliveryPoint($this);
        $this->deliveryPointInvoices->add($deliveryPointInvoice);
    }

    public function setDeliveryPointInvoices(Collection $deliveryPointInvoices): void
    {
        foreach ($this->deliveryPointInvoices as $deliveryPointInvoice) {
            if (!$deliveryPointInvoices->contains($deliveryPointInvoice)) {
                $this->deliveryPointInvoices->removeElement($deliveryPointInvoice);
            }
        }
        foreach ($deliveryPointInvoices as $deliveryPointInvoice) {
            $this->addDeliveryPointInvoice($deliveryPointInvoice);
        }
    }

    public function getIsInScope(): bool
    {
        return $this->isInScope;
    }

    public function setIsInScope(bool $isInScope): void
    {
        $this->isInScope = $isInScope;
    }

    public function getScopeDate(): ?\DateTimeInterface
    {
        return $this->scopeDate;
    }

    public function setScopeDate(?\DateTimeInterface $scopeDate): void
    {
        $this->scopeDate = $scopeDate;
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
        $deliveryPointBudget->setDeliveryPoint($this);
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
        return new ArrayCollection($this->importReports->getValues());
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