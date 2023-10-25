<?php

namespace App\Entity;

use App\Repository\ContractRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ContractRepository::class)
 */
class Contract implements HasClientInterface
{
    public const PROVIDER_EDF = 'EDF';
    public const PROVIDER_DIRECT_ENERGIE = 'DIRECT_ENERGIE';
    public const PROVIDER_ENGIE = 'ENGIE';
    public const PROVIDER_OTHER = 'OTHER';

    public const AVAILABLE_PROVIDERS = [
        self::PROVIDER_DIRECT_ENERGIE,
        self::PROVIDER_EDF,
        self::PROVIDER_ENGIE,
        self::PROVIDER_OTHER
    ];

    public const INVOICE_PERIOD_1 = '1';
    public const INVOICE_PERIOD_2 = '2';
    public const INVOICE_PERIOD_6 = '6';
    public const INVOICE_PERIOD_12 = '12';

    public const AVAILABLE_INVOICE_PERIODS = [
        self::INVOICE_PERIOD_1,
        self::INVOICE_PERIOD_2,
        self::INVOICE_PERIOD_6,
        self::INVOICE_PERIOD_12,
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
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private Client $client;

    /**
     * @ORM\Column(type="string", length=255)
     * @SWG\Property(property="reference", type="string", description="required")
     *
     * @Groups("default")
     */
    private string $reference;

    /**
     * @ORM\Column(type="enumTypeProvider")
     * @SWG\Property(property="provider", type="string", description="required - enum(EDF|DIRECT_ENERGIE|ENGIE|OTHER)")
     *
     * @Groups("default")
     */
    private string $provider;

    /**
     * @ORM\Column(type="enumTypeType")
     * @SWG\Property(property="type", type="string", description="required - enum(negotiated|regulated)")
     *
     * @Groups("default")
     */
    private string $type;

    /**
     * @ORM\Column(type="enumInvoicePeriod", nullable=true)
     * @SWG\Property(property="invoice_period", type="string", description="enum(1|2|6|12)")
     *
     * @Groups("default")
     */
    private ?string $invoice_period = null;

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
    private ?\DateTimeInterface $finishedAt = null;

    /**
     * @ORM\ManyToMany(targetEntity=Pricing::class, inversedBy="contracts")
     *
     * @Groups("contract_pricings")
     */
    private Collection $pricings;

    /**
     * @ORM\OneToMany(targetEntity=DeliveryPoint::class, mappedBy="contract")
     */
    private Collection $deliveryPoints;

    public function __construct()
    {
        $this->pricings = new ArrayCollection();
        $this->deliveryPoints = new ArrayCollection();
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

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
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
        $this->pricings->add($pricing);
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
        $deliveryPoint->setContract($this);
        $this->deliveryPoints->add($deliveryPoint);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }


    public function getInvoicePeriod(): ?string
    {
        return $this->invoice_period;
    }

    public function setInvoicePeriod(?string $invoice_period): void
    {
        $this->invoice_period = $invoice_period;
    }
}
