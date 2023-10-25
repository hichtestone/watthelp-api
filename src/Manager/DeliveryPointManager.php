<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Client;
use App\Entity\Contract;
use App\Entity\DeliveryPoint;
use App\OptionResolver\DeliveryPoint\SearchOptions;
use App\Repository\DeliveryPointRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DeliveryPointManager
{
    private DeliveryPointRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(DeliveryPoint::class);
    }

    public function insert(DeliveryPoint $deliveryPoint): void
    {
        $this->entityManager->persist($deliveryPoint);
        $this->entityManager->flush();
    }

    public function update(DeliveryPoint $deliveryPoint): void
    {
        $deliveryPoint->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($deliveryPoint);
        $this->entityManager->flush();
    }

    public function delete(DeliveryPoint $deliveryPoint): void
    {
        $this->entityManager->remove($deliveryPoint);
        $this->entityManager->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?DeliveryPoint
    {
        return $this->repository->getByCriteria($client, $criteria);
    }

    public function getMapInfo(Client $client): array
    {
        return $this->repository->getMapInfo($client);
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Exception
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        if (isset($filters['contract']) && !$filters['contract'] instanceof Contract) {
            $filters['contract'] = $this->entityManager->getReference(Contract::class, $filters['contract']);
        }

        if (isset($filters['is_in_scope'])) {
            $filters['is_in_scope'] = boolval($filters['is_in_scope']);
        }

        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($client, $filters, $pagination);
    }

    public function getCountDeliveryPoints(Client $client): array
    {
        return $this->repository->getCountDeliveryPoints($client);
    }

    public function count(array $criteria): int
    {
        return $this->repository->count($criteria);
    }
}
