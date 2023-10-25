<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User implements UserInterface, HasClientInterface
{
    public const EXPAND_DATA_USER_PERMISSIONS      = 'user_permissions';
    public const EXPAND_DATA_USER_PERMISSION_CODES = 'user_permission_codes';

    public const LANGUAGE_FR = 'fr';
    public const LANGUAGE_EN = 'en';

    public const AVAILABLE_LANGUAGES = [
        self::LANGUAGE_FR,
        self::LANGUAGE_EN
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
     * @ORM\Column(type="string", length=180, unique=true)
     * @SWG\Property(property="email", type="string", description="required")
     *
     * @Groups("default")
     */
    private string $email;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private string $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @SWG\Property(property="first_name", type="string", description="required")
     *
     * @Groups("default")
     */
    private string $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @SWG\Property(property="last_name", type="string", description="required")
     *
     * @Groups("default")
     */
    private string $lastName;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @SWG\Property(property="mobile")
     *
     * @Groups("default")
     */
    private ?string $mobile = null;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @SWG\Property(property="phone")
     *
     * @Groups("default")
     */
    private ?string $phone = null;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Groups("default")
     */
    private bool $superAdmin = false;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\File", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     * @SWG\Property(description="The user avatar.")
     *
     * @Groups("user_avatar")
     */
    private ?File $avatar = null;

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
     * @ORM\Column(type="datetime", nullable=true)
     * @SWG\Property(property="connected_at", type="string", description="ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *
     */
    private \DateTimeInterface $connectedAt;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups("user_client")
     */
    private Client $client;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @SWG\Property(property="dashboard", type="object")
     *
     * @Groups("default")
     */
    private ?array $dashboard = null;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Notification", mappedBy="user", cascade={"remove"})
     *
     * @Groups("user_notifications")
     */
    private Collection $notifications;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ImportReport", mappedBy="user", cascade={"remove"})
     *
     * @Groups("user_import_reports")
     */
    private Collection $importReports;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\File", mappedBy="user", cascade={"remove"})
     * 
     * @Groups("user_files")
     */
    private Collection $files;

    /**
     * @ORM\ManyToMany(targetEntity=Role::class, mappedBy="users")
     * @SerializedName("roles")
     *
     * @Groups("user_roles")
     */
    private Collection $userRoles;

    /**
     * @ORM\Column(type="enumTypeLanguage")
     *
     * @Groups("default")
     */
    private string $language;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->notifications = new ArrayCollection();
        $this->importReports = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->userRoles = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    public function setMobile(?string $mobile): void
    {
        $this->mobile = $mobile;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function isSuperAdmin(): bool
    {
        return $this->superAdmin;
    }

    public function setSuperAdmin(bool $superAdmin): void
    {
        $this->superAdmin = $superAdmin;
    }

    public function getAvatar(): ?File
    {
        return $this->avatar;
    }

    public function setAvatar(?File $avatar): void
    {
        $this->avatar = $avatar;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getConnectedAt(): ?\DateTimeInterface
    {
        return $this->connectedAt;
    }

    public function setConnectedAt(\DateTimeInterface $connectedAt): void
    {
        $this->connectedAt = $connectedAt;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getDashboard(): ?array
    {
        return $this->dashboard;
    }

    public function setDashboard(?array $dashboard): void
    {
        $this->dashboard = $dashboard;
    }

    public function getNotifications(): ArrayCollection
    {
        return new ArrayCollection($this->notifications->getValues());
    }

    public function getImportReports(): ArrayCollection
    {
        return new ArrayCollection($this->importReports->getValues());
    }

    public function getFiles(): ArrayCollection
    {
        return new ArrayCollection($this->files->getValues());
    }

    public function addFile(File $file): void
    {
        if ($this->files->contains($file)) {
            return;
        }
        $file->setUser($this);
        $this->files->add($file);
    }

    public function setFiles(Collection $files): void
    {
        foreach ($this->files as $file) {
            if (!$files->contains($file)) {
                $this->files->removeElement($file);
            }
        }
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    public function getRoles(): array
    {
        return [];
    }

    public function getUserRoles(): ArrayCollection
    {
        return new ArrayCollection($this->userRoles->getValues());
    }

    public function addUserRole(Role $userRole): void
    {
        if ($this->userRoles->contains($userRole)) {
            return;
        }
        $userRole->addUser($this);
        $this->userRoles->add($userRole);
    }

    public function setUserRoles(Collection $userRoles): void
    {
        foreach ($this->userRoles as $userRole) {
            if (!$userRoles->contains($userRole)) {
                $userRole->removeUser($this);
                $this->userRoles->removeElement($userRole);
            }
        }
        foreach ($userRoles as $userRole) {
            $this->addUserRole($userRole);
        }
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }
}