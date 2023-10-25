<?php

declare(strict_types=1);

namespace App\Entity\Invoice;

use App\Entity\Invoice;
use App\Entity\Invoice\Analysis\DeliveryPointInvoiceAnalysis;
use App\Entity\Invoice\Analysis\ItemAnalysis;
use App\Repository\Invoice\AnalysisRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AnalysisRepository::class)
 */
class Analysis
{
    public const STATUS_ERROR = 'error';
    public const STATUS_OK = 'ok';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_WARNING = 'warning';

    public const AVAILABLE_STATUSES = [
        self::STATUS_ERROR,
        self::STATUS_OK,
        self::STATUS_PROCESSING,
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
     * @ORM\Column(type="enumTypeAnalysisStatus")
     *
     * @Groups("default")
     */
    private string $status = self::STATUS_OK;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\OneToOne(targetEntity=Invoice::class, inversedBy="analysis")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups("analysis_invoice")
     */
    private Invoice $invoice;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invoice\Analysis\DeliveryPointInvoiceAnalysis", mappedBy="analysis", cascade={"remove", "persist"})
     *
     * @Groups("analysis_delivery_point_invoice_analyses")
     */
    private Collection $deliveryPointInvoiceAnalyses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Invoice\Analysis\ItemAnalysis", mappedBy="analysis", cascade={"remove", "persist"})
     *
     * @Groups("analysis_item_analyses")
     */
    private Collection $itemAnalyses;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->deliveryPointInvoiceAnalyses = new ArrayCollection();
        $this->itemAnalyses = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    public function getDeliveryPointInvoiceAnalyses(): ArrayCollection
    {
        return new ArrayCollection($this->deliveryPointInvoiceAnalyses->getValues());
    }

    public function addDeliveryPointInvoiceAnalysis(DeliveryPointInvoiceAnalysis $deliveryPointInvoiceAnalysis): void
    {
        if ($this->deliveryPointInvoiceAnalyses->contains($deliveryPointInvoiceAnalysis)) {
            return;
        }
        $deliveryPointInvoiceAnalysis->setAnalysis($this);
        $this->deliveryPointInvoiceAnalyses->add($deliveryPointInvoiceAnalysis);
    }

    public function setDeliveryPointInvoiceAnalyses(Collection $deliveryPointInvoiceAnalyses): void
    {
        foreach ($this->deliveryPointInvoiceAnalyses as $deliveryPointInvoiceAnalysis) {
            if (!$deliveryPointInvoiceAnalyses->contains($deliveryPointInvoiceAnalysis)) {
                $this->deliveryPointInvoiceAnalyses->removeElement($deliveryPointInvoiceAnalysis);
            }
        }
        foreach ($deliveryPointInvoiceAnalyses as $deliveryPointInvoiceAnalysis) {
            $this->addDeliveryPoint($deliveryPointInvoiceAnalysis);
        }
    }

    public function getItemAnalyses(): ArrayCollection
    {
        return new ArrayCollection($this->itemAnalyses->getValues());
    }

    public function addItemAnalysis(ItemAnalysis $itemAnalysis): void
    {
        if ($this->itemAnalyses->contains($itemAnalysis)) {
            return;
        }
        $itemAnalysis->setAnalysis($this);
        $this->itemAnalyses->add($itemAnalysis);
    }

    public function setItemAnalyses(Collection $itemAnalyses): void
    {
        foreach ($this->itemAnalyses as $itemAnalysis) {
            if (!$itemAnalyses->contains($itemAnalysis)) {
                $this->itemAnalyses->removeElement($itemAnalysis);
            }
        }
        foreach ($itemAnalyses as $itemAnalysis) {
            $this->addDeliveryPoint($itemAnalysis);
        }
    }
}