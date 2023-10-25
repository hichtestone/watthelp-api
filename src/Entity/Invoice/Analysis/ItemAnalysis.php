<?php

declare(strict_types=1);

namespace App\Entity\Invoice\Analysis;

use App\Entity\Invoice\Analysis;
use App\Entity\Invoice\Anomaly;
use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="WattHelp\Bundle\CoreBundle\Repository\Invoice\Analysis\ItemAnalysisRepository")
 * @Gedmo\TranslationEntity(class=ItemAnalysisTranslation::class)
 */
class ItemAnalysis
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     *
     * @Groups("default")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice\Analysis", inversedBy="itemAnalyses")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     *
     * @Groups("item_analysis_analysis")
     */
    private ?Analysis $analysis = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice\Analysis\DeliveryPointInvoiceAnalysis", inversedBy="itemAnalyses")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     *
     * @Groups("item_analysis_delivery_point_invoice_analysis")
     */
    private ?DeliveryPointInvoiceAnalysis $deliveryPointInvoiceAnalysis = null;

    /**
     * @ORM\Column(type="string", nullable=true, name="analyzer")
     *
     * @Groups("default")
     */
    private ?string $analyzer = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Invoice\Anomaly", inversedBy="itemAnalysis", cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     * @Groups("item_analysis_anomaly")
     */
    private ?Anomaly $anomaly = null;

    /**
     * @ORM\Column(type="string", nullable=true, length=50, name="group_name")
     *
     * @Groups("default")
     */
    private ?string $group = null;

    /**
     * @ORM\Column(type="enumTypeAnalysisStatus", nullable=false, name="status")
     *
     * @Groups("default")
     */
    private string $status = Analysis::STATUS_OK;

    /**
     * @var string[]|null
     *
     * @ORM\Column(type="array", nullable=true)
     * @Gedmo\Translatable
     *
     * @Groups("default")
     */
    private ?array $messages = null;

    /**
     * Path from the DeliveryPointInvoice to the field in error/warning
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups("default")
     */
    private ?string $field = null;

    public function __construct()
    {
        $this->messages = [];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getAnalysis(): ?Analysis
    {
        return $this->analysis;
    }

    public function setAnalysis(?Analysis $analysis): void
    {
        $this->analysis = $analysis;
    }

    public function getDeliveryPointInvoiceAnalysis(): ?DeliveryPointInvoiceAnalysis
    {
        return $this->deliveryPointInvoiceAnalysis;
    }

    public function setDeliveryPointInvoiceAnalysis(?DeliveryPointInvoiceAnalysis $deliveryPointInvoiceAnalysis)
    {
        $this->deliveryPointInvoiceAnalysis = $deliveryPointInvoiceAnalysis;
    }

    public function getAnomaly(): ?Anomaly
    {
        return $this->anomaly;
    }

    public function setAnomaly(Anomaly $anomaly): void
    {
        $anomaly->setItemAnalysis($this);
        $this->anomaly = $anomaly;
    }

    public function getAnalyzer(): ?string
    {
        return $this->analyzer;
    }

    public function setAnalyzer(?string $analyzer): void
    {
        $this->analyzer = $analyzer;
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function setGroup(?string $group): void
    {
        $this->group = $group;
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
     * @return null|string[]
     */
    public function getMessages(): ?array
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

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(?string $field): void
    {
        $this->field = $field;
    }
}
