<?php

declare(strict_types=1);

namespace App\Entity\Invoice;

use App\Repository\Invoice\InvoiceTaxRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=InvoiceTaxRepository::class)
 */
class InvoiceTax
{
    public const TYPE_TAX_CSPE = 'cspe';
    public const TYPE_TAX_TCFE = 'tcfe';
    public const TYPE_TAX_TDCFE = 'tdcfe';
    public const TYPE_TAX_TCCFE = 'tccfe';
    public const TYPE_TAX_CTA = 'cta';

    public const AVAILABLE_TYPES = [
        self::TYPE_TAX_CSPE,
        self::TYPE_TAX_TDCFE,
        self::TYPE_TAX_TCCFE,
        self::TYPE_TAX_CTA,
        self::TYPE_TAX_TCFE
    ];

    /**
     * 27,04% of the fixed part of the turpe
     */
    public const CTA_UNIT_PRICE = 2704;

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
     * @ORM\Column(type="enumTypeInvoiceTaxType")
     * 
     * @Groups("default")
     */
    private string $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * In kWh
     * 
     * @Groups("default")
     */
    private ?int $quantity = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Divide by 10^5 to get the price in cts€/kWh
     * 
     * @Groups("default")
     */
    private ?int $unitPrice = null;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     * Divide by 10^5 to get the amount in cts€
     * 
     * @Groups("default")
     */
    private ?int $total = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @Groups("default")
     */
    private ?\DateTimeInterface $startedAt = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * 
     * @Groups("default")
     */
    private ?\DateTimeInterface $finishedAt = null;

    /**
     * @ORM\ManyToMany(targetEntity=DeliveryPointInvoice::class, inversedBy="taxes")
     */
    private Collection $deliveryPointInvoices;

    public function __construct()
    {
        $this->deliveryPointInvoices = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getUnitPrice(): ?int
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(?int $unitPrice): void
    {
        $this->unitPrice = $unitPrice;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(?\DateTimeInterface $startedAt): void
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

    public function getDeliveryPointInvoices(): ArrayCollection
    {
        return new ArrayCollection($this->deliveryPointInvoices->getValues());
    }

    public function addDeliveryPointInvoice(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        if ($this->deliveryPointInvoices->contains($deliveryPointInvoice)) {
            return;
        }
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
}
