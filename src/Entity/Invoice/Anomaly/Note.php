<?php

declare(strict_types=1);

namespace App\Entity\Invoice\Anomaly;

use App\Entity\Invoice\Anomaly;
use App\Entity\User;
use App\Repository\Invoice\Anomaly\NoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=NoteRepository::class)
 */
class Note
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Invoice\Anomaly", inversedBy="notes")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups("note_anomaly")
     */
    private Anomaly $anomaly;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Groups("note_user")
     */
    private User $user;

    /**
     * @ORM\Column(type="string", nullable=false, length=255)
     *
     * @Groups("default")
     */
    private string $content;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @Groups("default")
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     *
     * @Groups("default")
     */
    private \DateTimeInterface $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getAnomaly(): Anomaly
    {
        return $this->anomaly;
    }

    public function setAnomaly(Anomaly $anomaly): void
    {
        $this->anomaly = $anomaly;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
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