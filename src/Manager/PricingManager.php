<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Client;
use App\Entity\DeliveryPoint;
use App\Entity\Pricing;
use App\OptionResolver\Pricing\SearchOptions;
use App\Repository\PricingRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PricingManager
{
    private PricingRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Pricing::class);
    }

    public function insert(Pricing $pricing): void
    {
        $this->entityManager->persist($pricing);
        $this->entityManager->flush();
    }

    public function update(Pricing $pricing): void
    {
        $this->entityManager->persist($pricing);
        $this->entityManager->flush();
    }

    public function delete(Pricing $pricing): void
    {
        $this->entityManager->remove($pricing);
        $this->entityManager->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?Pricing
    {
        return $this->repository->getByCriteria($client, $criteria);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException|\InvalidArgumentException
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($client, $filters, $pagination);
    }

    /**
     * @return Pricing[]
     * @throws \InvalidArgumentException
     */
    public function getPricingsBetweenInterval(DeliveryPoint $deliveryPoint, \DateTimeInterface $startedAt, \DateTimeInterface $finishedAt): array
    {
        return $this->repository->getPricingsBetweenInterval($deliveryPoint, $startedAt, $finishedAt);
    }
}