<?php

declare(strict_types=1);

namespace App\Entity\Invoice;

use App\Entity\DeliveryPoint;
use App\Entity\Invoice;
use App\Entity\Invoice\Analysis\DeliveryPointInvoiceAnalysis;
use App\Repository\Invoice\DeliveryPointInvoiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass=DeliveryPointInvoiceRepository::class)
 */
class DeliveryPointInvoice
{
    public const TYPE_ESTIMATED = 'estimated';
    public const TYPE_REAL = 'real';

    public const AVAILABLE_TYPES = [
        self::TYPE_ESTIMATED,
        self::TYPE_REAL
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
     * @ORM\Column(type="bigint")
     * @SerializedName("amount_ht")
     * @SWG\Property(property="amount_ht", type="integer", description="required - divide by 10^5 to get the price in cents")
     * 
     * @Groups("default")
     */
    private int $amountHT;

    /**
     * @ORM\Column(type="bigint")
     * @SerializedName("amount_tva")
     * @SWG\Property(property="amount_tva", type="integer", description="required - divide by 10^5 to get the price in cents")
     * 
     * @Groups("default")
     */
    private int $amountTVA;

    /**
     * @ORM\Column(type="bigint")
     * @SerializedName("amount_ttc")
     * @SWG\Property(property="amount_ttc", type="integer", description="required - divide by 10^5 to get the price in cents")
     * 
     * @Groups("default")
     */
    private int $amountTTC;

    /**
     * @ORM\Column(type="decimal", nullable=true, precision=5, scale=1)
     * @SWG\Property(property="power_subscribed", type="string", description="required")
     * 
     * @Groups("default")
     */
    private ?string $powerSubscribed = null;

    /**
     * @ORM\Column(type="enumTypeDeliveryPointInvoiceType")
     * @SWG\Property(property="type", type="string", description="required - enum(estimated|real)")
     * 
     * @Groups("default")
     */
    private string $type;

    /**
     * @ORM\ManyToOne(targetEntity=DeliveryPoint::class, inversedBy="deliveryPointInvoices", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups("delivery_point_invoice_delivery_point")
     */
    private DeliveryPoint $deliveryPoint;

    /**
     * @ORM\ManyToOne(targetEntity=Invoice::class, inversedBy="deliveryPointInvoices", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups("delivery_point_invoice_invoice")
     */
    private Invoice $invoice;

    /**
     * @ORM\OneToOne(targetEntity=InvoiceConsumption::class, mappedBy="deliveryPointInvoice", cascade={"persist", "remove"})
     *
     * @Groups("delivery_point_invoice_invoice_consumption")
     */
    private InvoiceConsumption $consumption;

    /**
     * @ORM\OneToOne(targetEntity=InvoiceSubscription::class, mappedBy="deliveryPointInvoice", cascade={"persist", "remove"})
     *
     * @Groups("delivery_point_invoice_invoice_subscription")
     */
    private ?InvoiceSubscription $subscription = null;

    /**
     * @ORM\ManyToMany(targetEntity=InvoiceTax::class, mappedBy="deliveryPointInvoices", cascade={"persist"})
     *
     * @Groups("delivery_point_invoice_invoice_taxes")
     */
    private Collection $taxes;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Invoice\Analysis\DeliveryPointInvoiceAnalysis", mappedBy="deliveryPointInvoice", cascade={"persist", "remove"})
     *
     * @Groups("delivery_point_invoice_delivery_point_invoice_analysis")
     */
    protected ?DeliveryPointInvoiceAnalysis $deliveryPointInvoiceAnalysis = null;

    public function __construct()
    {
        $this->taxes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getPowerSubscribed(): ?string
    {
        return $this->powerSubscribed;
    }

    public function setPowerSubscribed(?string $powerSubscribed): void
    {
        $this->powerSubscribed = $powerSubscribed;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getDeliveryPoint(): DeliveryPoint
    {
        return $this->deliveryPoint;
    }

    public function setDeliveryPoint(DeliveryPoint $deliveryPoint): void
    {
        $this->deliveryPoint = $deliveryPoint;
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    public function getConsumption(): InvoiceConsumption
    {
        return $this->consumption;
    }

    /**
     * Used when the DeliveryPointInvoice has just been created and can have an uninitialized 'consumption' property
     */
    public function checkAndGetConsumption(): ?InvoiceConsumption
    {
        return isset($this->consumption) ? $this->consumption : null;
    }

    public function setConsumption(InvoiceConsumption $consumption): void
    {
        $this->consumption = $consumption;
    }

    public function getSubscription(): ?InvoiceSubscription
    {
        return $this->subscription;
    }

    public function setSubscription(?InvoiceSubscription $subscription): void
    {
        $this->subscription = $subscription;
    }

    public function getTaxes(): ArrayCollection
    {
        return new ArrayCollection($this->taxes->getValues());
    }

    public function addTax(InvoiceTax $tax): void
    {
        if ($this->taxes->contains($tax)) {
            return;
        }
        $this->taxes->add($tax);
    }

    public function setTaxes(Collection $taxes): void
    {
        foreach ($this->taxes as $tax) {
            if (!$taxes->contains($tax)) {
                $this->taxes->removeElement($tax);
            }
        }
        foreach ($taxes as $tax) {
            $this->addTax($tax);
        }
    }

    public function getDeliveryPointInvoiceAnalysis(): ?DeliveryPointInvoiceAnalysis
    {
        return $this->deliveryPointInvoiceAnalysis;
    }

    public function setDeliveryPointInvoiceAnalysis(?DeliveryPointInvoiceAnalysis $deliveryPointInvoiceAnalysis): void
    {
        $this->deliveryPointInvoiceAnalysis = $deliveryPointInvoiceAnalysis;
    }
}