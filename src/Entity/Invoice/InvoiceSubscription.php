<?php

declare(strict_types=1);

namespace App\Entity\Invoice;

use App\Repository\Invoice\InvoiceSubscriptionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 */
class InvoiceSubscription
{
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
     * @ORM\Column(type="bigint", nullable=true)
     * Divide by 10^5 to get the price in cts€
     * 
     * @Groups("default")
     */
    private ?int $total = null;

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
     * @ORM\OneToOne(targetEntity=DeliveryPointInvoice::class, inversedBy="subscription")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private DeliveryPointInvoice $deliveryPointInvoice;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotal(): ?int
    {
        return $this->total;
    }

    public function setTotal(?int $total): void
    {
        $this->total = $total;
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

    public function getDeliveryPointInvoice(): ?DeliveryPointInvoice
    {
        return $this->deliveryPointInvoice;
    }

    public function setDeliveryPointInvoice(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $this->deliveryPointInvoice = $deliveryPointInvoice;
    }
}
