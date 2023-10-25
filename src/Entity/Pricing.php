<?php

namespace App\Entity;

use App\Repository\PricingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PricingRepository::class)
 */
class Pricing implements HasClientInterface
{
    public const TYPE_REGULATED  = 'regulated';
    public const TYPE_NEGOTIATED = 'negotiated';

    public const AVAILABLE_TYPES = [
        self::TYPE_NEGOTIATED,
        self::TYPE_REGULATED
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
     * @ORM\Column(type="string", length=255)
     * @SWG\Property(property="name", type="string", description="required")
     * 
     * @Groups("default")
     * @Groups("restricted")
     */
    private string $name;

    /**
     * @ORM\Column(type="enumTypeType")
     * @SWG\Property(property="type", type="string", description="required - enum(negotiated|regulated)")
     * 
     * @Groups("default")
     */
    private string $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @SWG\Property(property="subscription_price", type="integer", description="only available if type is regulated - divide by 10^5 to get the price in €/kW/month - 2 decimals maximum")
     * 
     * @Groups("default")
     */
    private ?int $subscriptionPrice = null;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="consumption_base_price", type="integer", description="required - divide by 10^5 to get the price in cts€/kWh - 3 decimals maximum")
     * 
     * @Groups("default")
     */
    private int $consumptionBasePrice;

    /**
     * @ORM\Column(type="datetime")
     * @SWG\Property(property="started_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @SWG\Property(property="finished_at", type="string", description="ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *
     * @Groups("default")
     */
    private ?\DateTimeInterface $finishedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="pricings")
     * @ORM\JoinColumn(nullable=false)
     */
    private Client $client;

    /**
     * @ORM\ManyToMany(targetEntity=Contract::class, mappedBy="pricings")
     */
    private Collection $contracts;

    /**
     * @ORM\ManyToMany(targetEntity=ImportReport::class, inversedBy="pricings", cascade={"persist"})
     * @ORM\JoinColumn()
     *
     * @Groups("pricing_import_reports")
     */
    private Collection $importReports;

    public function __construct()
    {
        $this->contracts = new ArrayCollection();
        $this->importReports = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType($type): void
    {
        if ($type !== self::TYPE_REGULATED) {
            $this->subscriptionPrice = null;
        }
        $this->type = $type;
    }

    public function getSubscriptionPrice(): ?int
    {
        return $this->subscriptionPrice;
    }

    public function setSubscriptionPrice(?int $subscriptionPrice): void
    {
        $this->subscriptionPrice = $subscriptionPrice;
    }

    public function getConsumptionBasePrice(): int
    {
        return $this->consumptionBasePrice;
    }

    public function setConsumptionBasePrice(int $consumptionBasePrice): void
    {
        $this->consumptionBasePrice = $consumptionBasePrice;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getContracts(): ArrayCollection
    {
        return new ArrayCollection($this->contracts->getValues());
    }

    public function setContracts(Collection $contracts): void
    {
        foreach ($this->contracts as $contract) {
            if (!$contracts->contains($contract)) {
                $this->contracts->removeElement($contract);
            }
        }
        foreach ($contracts as $contract) {
            $this->addContract($contract);
        }
    }

    public function addContract(Contract $contract): void
    {
        if ($this->contracts->contains($contract)) {
            return;
        }
        $contract->addPricing($this);
        $this->contracts->add($contract);
    }

    public function doDatesOverlap(Pricing $target): bool
    {
        if ($this->finishedAt && $this->finishedAt < $target->getStartedAt()) {
            return false;
        }
        if ($target->getFinishedAt() && $target->getFinishedAt() < $this->startedAt) {
            return false;
        }
        return true;
    }

    public function getStartedAt(): \DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function getFinishedAt(): ?\DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(?\DateTimeInterface $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }

    public function getImportReports(): ArrayCollection
    {
        return new ArrayCollection($this->importReports->getValues());
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

    public function addImportReport(ImportReport $importReport): void
    {
        if ($this->importReports->contains($importReport)) {
            return;
        }
        $this->importReports->add($importReport);
    }
}
