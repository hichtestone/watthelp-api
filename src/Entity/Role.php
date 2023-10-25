<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\RoleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=RoleRepository::class)
 */
class Role implements HasClientInterface
{
    public const EXPAND_DATA_PERMISSION_CODES = 'role_permission_codes';

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *
     * @Groups("default")
     * @Groups("restricted")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="roles")
     * @ORM\JoinColumn(nullable=false)
     */
    private Client $client;

    /**
     * @var Collection<User>
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="userRoles")
     *
     * @Groups("role_users")
     */
    private Collection $users;

    /**
     * @var Collection<Permission>
     * @ORM\ManyToMany(targetEntity=Permission::class, mappedBy="roles")
     *
     * @Groups("role_permissions")
     */
    private Collection $permissions;

    /**
     * @ORM\Column(type="string", length=255)
     *
     * @Groups("default")
     * @Groups("restricted")
     */
    private string $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Groups("default")
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->permissions = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    /**
     * @return ArrayCollection<User>
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
        $this->users->add($user);
    }

    public function removeUser(User $user): void
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
        }
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

    /**
     * @return ArrayCollection<Permission>
     */
    public function getPermissions(): ArrayCollection
    {
        return new ArrayCollection($this->permissions->getValues());
    }

    public function addPermission(Permission $permission): void
    {
        if ($this->permissions->contains($permission)) {
            return;
        }
        $permission->addRole($this);
        $this->permissions->add($permission);
    }

    public function setPermissions(Collection $permissions): void
    {
        foreach ($this->permissions as $permission) {
            if (!$permissions->contains($permission)) {
                $permission->removeRole($this);
                $this->permissions->removeElement($permission);
            }
        }
        foreach ($permissions as $permission) {
            $this->addPermission($permission);
        }
    }
}