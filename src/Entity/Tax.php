<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaxRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass=TaxRepository::class)
 */
class Tax implements HasClientInterface
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
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="taxes")
     * @ORM\JoinColumn(nullable=false)
     */
    private Client $client;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="cspe", type="integer", description="required - divide by 10^5 to get the price in cts€/kWh")
     *
     * @Groups("default")
     */
    private int $cspe;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="tdcfe", type="integer", description="required - divide by 10^5 to get the price in cts€/kWh")
     *
     * @Groups("default")
     */
    private int $tdcfe;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="tccfe", type="integer", description="required - divide by 10^5 to get the price in cts€/kWh")
     *
     * @Groups("default")
     */
    private int $tccfe;

    /**
     * @ORM\Column(type="integer")
     * @SWG\Property(property="cta", type="integer", description="required - percentage - divide by 100 to get the percentage")
     *
     * @Groups("default")
     */
    private int $cta;

    /**
     * @ORM\Column(type="datetime")
     * @SWG\Property(property="started_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     *
     * @Groups("default")
     */
    private \DateTimeInterface $startedAt;

    /**
     * @ORM\Column(type="datetime")
     * @SWG\Property(property="finished_at", type="string", description="required - ISO 8601 - ex: 2020-07-16T19:20:05+01:00")
     * 
     * @Groups("default")
     */
    private \DateTimeInterface $finishedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function getCspe(): int
    {
        return $this->cspe;
    }

    public function setCspe(int $cspe): void
    {
        $this->cspe = $cspe;
    }

    public function getTdcfe(): int
    {
        return $this->tdcfe;
    }

    public function setTdcfe(int $tdcfe): void
    {
        $this->tdcfe = $tdcfe;
    }

    public function getTccfe(): int
    {
        return $this->tccfe;
    }

    public function setTccfe(int $tccfe): void
    {
        $this->tccfe = $tccfe;
    }

    public function getCta(): int
    {
        return $this->cta;
    }

    public function setCta(int $cta): void
    {
        $this->cta = $cta;
    }

    public function getStartedAt(): \DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): void
    {
        $this->startedAt = $startedAt;
    }

    public function getFinishedAt(): \DateTimeInterface
    {
        return $this->finishedAt;
    }

    public function setFinishedAt(\DateTimeInterface $finishedAt): void
    {
        $this->finishedAt = $finishedAt;
    }
}