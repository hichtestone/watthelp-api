<?php

declare(strict_types=1);

namespace App\Entity\Invoice\Analysis;

use App\Entity\Invoice\Analysis;
use App\Entity\Invoice\DeliveryPointInvoice;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 */
class DeliveryPointInvoiceAnalysis
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     *
     * @Groups("default")
     * @Groups("restricted")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice\Analysis", inversedBy="deliveryPointInvoiceAnalyses", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private Analysis $analysis;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Invoice\DeliveryPointInvoice", inversedBy="deliveryPointInvoiceAnalysis")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups("delivery_point_invoice_analysis_delivery_point_invoice")
     */
    private DeliveryPointInvoice $deliveryPointInvoice;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Invoice\DeliveryPointInvoice")
     * @ORM\JoinColumn(nullable=true)
     */
    private ?DeliveryPointInvoice $previousDeliveryPointInvoice = null;

    /**
     * @var ItemAnalysis[]|Collection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Invoice\Analysis\ItemAnalysis", mappedBy="deliveryPointInvoiceAnalysis", cascade={"remove", "persist"})
     *
     * @Groups("delivery_point_invoice_analysis_item_analyses")
     */
    private Collection $itemAnalyses;

    /**
     * @ORM\Column(type="enumTypeAnalysisStatus", nullable=false)
     * 
     * @Groups("default")
     */
    private string $status = Analysis::STATUS_OK;

    public function __construct()
    {
        $this->itemAnalyses = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getAnalysis(): Analysis
    {
        return $this->analysis;
    }

    public function setAnalysis(Analysis $analysis): void
    {
        $this->analysis = $analysis;
    }

    public function getDeliveryPointInvoice(): DeliveryPointInvoice
    {
        return $this->deliveryPointInvoice;
    }

    public function setDeliveryPointInvoice(DeliveryPointInvoice $deliveryPointInvoice): void
    {
        $this->deliveryPointInvoice = $deliveryPointInvoice;
    }

    public function getPreviousDeliveryPointInvoice(): ?DeliveryPointInvoice
    {
        return $this->previousDeliveryPointInvoice;
    }

    public function setPreviousDeliveryPointInvoice(?DeliveryPointInvoice $previousDeliveryPointInvoice): void
    {
        $this->previousDeliveryPointInvoice = $previousDeliveryPointInvoice;
    }

    public function getItemAnalyses(): Collection
    {
        return $this->itemAnalyses;
    }

    public function setItemAnalyses(Collection $itemAnalyses): void
    {
        foreach($this->itemAnalyses as $itemAnalysis) {
            if (!in_array($itemAnalysis, $itemAnalyses, true)) {
                $this->itemAnalyses->removeElement($itemAnalysis);
            }
        }
        foreach ($itemAnalyses as $itemAnalysis) {
            $this->addItemAnalysis($itemAnalysis);
        }
    }

    public function addItemAnalysis(ItemAnalysis $itemAnalysis): void
    {
        if ($this->itemAnalyses->contains($itemAnalysis)) {
            return;
        }
        $itemAnalysis->setDeliveryPointInvoiceAnalysis($this);
        $this->itemAnalyses->add($itemAnalysis);
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus($status): void
    {
        $this->status = $status;
    }
}