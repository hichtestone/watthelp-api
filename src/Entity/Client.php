<?php declare(strict_types=1);

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 */
class Client
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
     * @ORM\OneToMany(targetEntity=User::class, mappedBy="client", cascade={"remove"})
     */
    private Collection $users;

    /**
     * @ORM\Column(type="string", length=255)
     * @SWG\Property(property="name", type="string", description="required")
     *
     * @Groups("default")
     * @Groups("restricted")
     */
    private string $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @SWG\Property(property="description", type="string")
     *
     * @Groups("default")
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @SWG\Property(property="address", type="string")
     * 
     * @Groups("default")
     */
    private ?string $address = null;

    /**
     * @ORM\Column(type="string", length=6, nullable=true)
     * @SWG\Property(property="zipCode", type="string")
     * 
     * @Groups("default")
     */
    private ?string $zipCode = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @SWG\Property(property="city", type="string")
     * 
     * @Groups("default")
     */
    private ?string $city = null;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\File")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @Groups("default")
     */
    private ?File $logo = null;

    /**
     * @ORM\Column(type="boolean")
     * @SWG\Property(property="enabled", type="boolean", description="required")
     *
     * @Groups("default")
     */
    private bool $enabled = true;

    /**
     * @ORM\Column(type="datetime")
     * @SWG\Property(property="created_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime")
     * @SWG\Property(property="updated_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=DeliveryPoint::class, mappedBy="client", cascade={"remove"})
     */
    private Collection $deliveryPoints;

    /**
     * @ORM\OneToMany(targetEntity=Tax::class, mappedBy="client", cascade={"remove"})
     */
    private Collection $taxes;

    /**
     * @ORM\OneToMany(targetEntity=Pricing::class, mappedBy="client", cascade={"remove"})
     */
    private Collection $pricings;

    /**
     * @ORM\OneToMany(targetEntity=Contract::class, mappedBy="client", cascade={"remove"})
     */
    private Collection $contracts;

    /**
     * @ORM\OneToMany(targetEntity=Invoice::class, mappedBy="client", cascade={"remove"})
     */
    private Collection $invoices;

    /**
     * @ORM\Column(type="string", length=3, nullable=true)
     * 
     * @Groups("default")
     */
    private ?string $department = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 
     * @Groups("default")
     */
    private ?string $insee = null;

    /**
     * @ORM\OneToMany(targetEntity=Budget::class, mappedBy="client", cascade={"remove"})
     */
    private Collection $budgets;

    /**
     * @ORM\OneToMany(targetEntity=Role::class, mappedBy="client", cascade={"remove"})
     */
    private Collection $roles;

    /**
     * @ORM\Column(type="enumTypeLanguage")
     *
     * @Groups("default")
     */
    private string $defaultLanguage;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->users = new ArrayCollection();
        $this->deliveryPoints = new ArrayCollection();
        $this->taxes = new ArrayCollection();
        $this->pricings = new ArrayCollection();
        $this->contracts = new ArrayCollection();
        $this->invoices = new ArrayCollection();
        $this->budgets = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getUsers(): ArrayCollection
    {
        return new ArrayCollection($this->users->getValues());
    }

    public function addUser(User $user): void
    {
        if ($this->users->contains($user)) {
            return;
        }
        $user->setClient($this);
        $this->users->add($user);
    }

    public function setUsers(Collection $users): void
    {
        foreach ($this->users as $user) {
            if (!$users->contains($user)) {
                $this->users->removeElement($user);
            }
        }
        foreach ($users as $user) {
            $this->addUser($user);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): void
    {
        $this->zipCode = $zipCode;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    public function getLogo(): ?File
    {
        return $this->logo;
    }

    public function setLogo(?File $logo): void
    {
        $this->logo = $logo;
    }

    public function getEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getDeliveryPoints(): ArrayCollection
    {
        return new ArrayCollection($this->deliveryPoints->getValues());
    }

    public function getTaxes(): ArrayCollection
    {
        return new ArrayCollection($this->taxes->getValues());
    }

    public function getPricings(): ArrayCollection
    {
        return new ArrayCollection($this->pricings->getValues());
    }

    public function getContracts(): ArrayCollection
    {
        return new ArrayCollection($this->contracts->getValues());
    }

    public function getInvoices(): ArrayCollection
    {
        return new ArrayCollection($this->invoices->getValues());
    }

    public function getBudgets(): ArrayCollection
    {
        return new ArrayCollection($this->budgets->getValues());
    }

    public function getRoles(): ArrayCollection
    {
        return new ArrayCollection($this->roles->getValues());
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): void
    {
        $this->department = $department;
    }

    public function getInsee(): ?string
    {
        return $this->insee;
    }

    public function setInsee(?string $insee): void
    {
        $this->insee = $insee;
    }

    public function getDefaultLanguage(): string
    {
        return $this->defaultLanguage;
    }

    public function setDefaultLanguage(string $defaultLanguage): void
    {
        $this->defaultLanguage = $defaultLanguage;
    }
}