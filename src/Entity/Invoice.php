<?php

namespace App\Entity;

use App\Entity\File;
use App\Entity\Invoice\Analysis;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\Model\Invoice\AmountByType;
use App\Repository\InvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass=InvoiceRepository::class)
 */
class Invoice implements HasClientInterface
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
     * @ORM\Column(type="string", length=255, unique=true)
     * @SWG\Property(property="reference", type="string", description="required")
     * 
     * @Groups("default")
     */
    private string $reference;

    /**
     * @ORM\Column(type="bigint")
     * @SerializedName("amount_ht")
     * @SWG\Property(property="amount_ht", type="integer", description="required")
     * 
     * @Groups("default")
     */
    private int $amountHT;

    /**
     * @ORM\Column(type="bigint")
     * @SerializedName("amount_tva")
     * @SWG\Property(property="amount_tva", type="integer", description="required")
     * 
     * @Groups("default")
     */
    private int $amountTVA;

    /**
     * @ORM\Column(type="bigint")
     * @SerializedName("amount_ttc")
     * @SWG\Property(property="amount_ttc", type="integer", description="required")
     * 
     * @Groups("default")
     */
    private int $amountTTC;

    /**
     * @ORM\Column(type="datetime")
     * @SWG\Property(property="emitted_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     * 
     * @Groups("default")
     */
    private \DateTimeInterface $emittedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     */
    private Client $client;

    /**
     * @ORM\OneToMany(targetEntity=DeliveryPointInvoice::class, mappedBy="invoice", cascade={"persist", "remove"})
     *
     * @Groups("invoice_delivery_point_invoices")
     */
    private Collection $deliveryPointInvoices;

    /**
     * @ORM\OneToOne(targetEntity=Analysis::class, mappedBy="invoice", cascade={"persist", "remove"})
     *
     * @Groups("invoice_analysis")
     */
    private ?Analysis $analysis = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ImportReport", inversedBy="invoices", cascade={"persist"})
     * @ORM\JoinColumn()
     *
     * @Groups("invoice_import_report")
     */
    private ?ImportReport $importReport = null;

    /**
     * @ORM\ManyToOne(targetEntity=File::class)
     * @ORM\JoinColumn()
     *
     * @Groups("invoice_pdf")
     */
    private ?File $pdf = null;

    public function __construct()
    {
        $this->deliveryPointInvoices = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    public function getAmountHT(): int
    {
        return $this->amountHT;
    }

    public function setAmountHT(int $amountHT): void
    {
        $this->amountHT = $amountHT;
    }

    public function getAmountTVA(): int
    {
        return $this->amountTVA;
    }

    public function setAmountTVA(int $amountTVA): void
    {
        $this->amountTVA = $amountTVA;
    }

    public function getAmountTTC(): int
    {
        return $this->amountTTC;
    }

    public function setAmountTTC(int $amountTTC): void
    {
        $this->amountTTC = $amountTTC;
    }

    public function getEmittedAt(): \DateTimeInterface
    {
        return $this->emittedAt;
    }

    public function setEmittedAt(\DateTimeInterface $emittedAt): void
    {
        $this->emittedAt = $emittedAt;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
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
        $deliveryPointInvoice->setInvoice($this);
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

    public function getAnalysis(): ?Analysis
    {
        return $this->analysis;
    }

    public function setAnalysis(?Analysis $analysis): void
    {
        $this->analysis = $analysis;
    }

    public function getImportReport(): ?ImportReport
    {
        return $this->importReport;
    }

    public function setImportReport(?ImportReport $importReport): void
    {
        $this->importReport = $importReport;
    }

    public function getPdf(): ?File
    {
        return $this->pdf;
    }

    public function setPdf(?File $pdf): void
    {
        $this->pdf = $pdf;
    }

    /**
     * @Groups("invoice_amount_by_type")
     */
    public function getAmountByType(): AmountByType
    {
        return new AmountByType($this);
    }
}