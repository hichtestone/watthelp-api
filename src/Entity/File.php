<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FileRepository")
 * @ORM\Table(name="file", options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"})
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer", name="id")
     *
     * @Groups("default")
     * @Groups("restricted")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="files")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Groups("file_user")
     */
    protected User $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="name")
     *
     * @Groups("default")
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, name="raw")
     *
     * @Groups("default")
     */
    private string $raw;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, name="thumb")
     *
     * @Groups("default")
     */
    private string $thumb;

    /**
     * @ORM\Column(type="string", length=255, nullable=false, name="mime", options={"default":"text/plain"})
     *
     * @Groups("default")
     */
    private string $mime;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="created_at")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="updated_at")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getRaw(): string
    {
        return $this->raw;
    }

    public function setRaw(string $raw): void
    {
        $this->raw = $raw;
    }

    public function getThumb(): string
    {
        return $this->thumb;
    }

    public function setThumb(string $thumb): void
    {
        $this->thumb = $thumb;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function setMime(string $mime): void
    {
        $this->mime = $mime;
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
}