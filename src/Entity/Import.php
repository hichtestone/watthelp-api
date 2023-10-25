<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ImportRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ImportRepository::class)
 */
class Import
{
    public const TYPE_INVOICE = 'invoice';
    public const TYPE_SCOPE = 'scope';
    public const TYPE_BUDGET = 'budget';
    public const TYPE_PRICING = 'pricing';

    public const AVAILABLE_TYPES = [
        self::TYPE_INVOICE,
        self::TYPE_SCOPE,
        self::TYPE_BUDGET,
        self::TYPE_PRICING
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
     * @ORM\Column(type="enumTypeImportType")
     * 
     * @Groups("default")
     */
    private string $type;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Groups("default")
     */
    private ?string $provider = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups("invoice_import_user")
     */
    private User $user;

    /**
     * @ORM\ManyToOne(targetEntity=File::class)
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups("invoice_import_file")
     */
    private File $file;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\OneToOne(targetEntity=ImportReport::class, mappedBy="import", cascade={"remove"})
     *
     * @Groups("import_import_report")
     */
    private ImportReport $importReport;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): void
    {
        $this->provider = $provider;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }


    public function getFile(): File
    {
        return $this->file;
    }

    public function setFile(File $file): void
    {
        $this->file = $file;
    }

    public function getImportReport(): ImportReport
    {
        return $this->importReport;
    }

    public function setImportReport(ImportReport $importReport): void
    {
        $this->importReport = $importReport;
    }
}
