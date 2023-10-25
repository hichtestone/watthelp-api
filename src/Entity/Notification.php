<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="App\Repository\NotificationRepository")
 */
class Notification implements HasUserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @Groups("default")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notifications")
     * @ORM\JoinColumn(name="user_id", nullable=false, referencedColumnName="id")
     */
    private User $user;

    /**
     * @ORM\Column(type="text", nullable=false, name="message")
     *
     * @Groups("default")
     */
    private string $message;

    /**
     * @ORM\Column(type="integer", nullable=true, name="progress")
     *
     * @Groups("default")
     */
    private ?int $progress = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="url")
     *
     * @Groups("default")
     */
    private ?string $url = null;

    /**
     * @ORM\Column(type="boolean", nullable=true, name="is_read", options={"default"=0})
     *
     * @Groups("default")
     */
    private bool $isRead = false;

    /**
     * @ORM\Column(type="datetime", nullable=false, name="created_at")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true, name="updated_at")
     *
     * @Groups("default")
     */
    private ?\DateTimeInterface $updatedAt = null;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @SWG\Property(property="data", type="array", @SWG\Items(type="string"))
     *
     * @Groups("default")
     */
    private ?array $data = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
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

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function setIsRead(bool $isRead): void
    {
        $this->isRead = $isRead;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }


    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getProgress(): ?int
    {
        return $this->progress;
    }

    public function setProgress(?int $progress): void
    {
        $this->progress = $progress;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(?array $data): void
    {
        $this->data = $data;
    }   
}
