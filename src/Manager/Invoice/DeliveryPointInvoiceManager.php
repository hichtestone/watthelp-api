<?php

declare(strict_types=1);

namespace App\Manager\Invoice;

use App\Entity\Client;
use App\Entity\DeliveryPoint;
use App\Entity\Invoice\DeliveryPointInvoice;
use App\OptionResolver\Invoice\DeliveryPointInvoice\SearchOptions;
use App\Repository\Invoice\DeliveryPointInvoiceRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class DeliveryPointInvoiceManager
{
    private DeliveryPointInvoiceRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(DeliveryPointInvoice::class);
    }

    public function insert(DeliveryPointInvoice $deliveryPointInvoice)
    {
        $this->entityManager->persist($deliveryPointInvoice);
        $this->entityManager->flush();
    }

    public function update(DeliveryPointInvoice $deliveryPointInvoice)
    {
        $this->entityManager->persist($deliveryPointInvoice);
        $this->entityManager->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?DeliveryPointInvoice
    {
        return $this->repository->getByCriteria($client, $criteria);
    }

    /**
     * Try to get previous deliveryPointInvoice base on deliveryPoint reference and invoice emittedAt value.
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function getPrevious(DeliveryPointInvoice $deliveryPointInvoice, \DateTimeInterface $emittedAt = null): ?DeliveryPointInvoice
    {
        return $this->repository->getPrevious($deliveryPointInvoice, $emittedAt);
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function hasRealInvoiceBetweenInterval(DeliveryPoint $deliveryPoint, \DateTimeInterface $from, \DateTimeInterface $to): bool
    {
        $count = $this->repository->hasRealInvoiceBetweenInterval($deliveryPoint, $from, $to);
        return $count > 0;
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function hasBefore(DeliveryPoint $deliveryPoint, \DateTimeInterface $date): bool
    {
        $count = $this->repository->hasBefore($deliveryPoint, $date);
        return $count > 0;
    }

    /**
     * @return DeliveryPointInvoice[]
     */
    public function findBy(array $criteria): array
    {
        return $this->repository->findBy($criteria);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        if (isset($filters['delivery_point']) && !$filters['delivery_point'] instanceof DeliveryPoint) {
            $filters['delivery_point'] = $this->entityManager->getReference(DeliveryPoint::class, $filters['delivery_point']);
        }

        if (isset($filters['is_credit_note']) && !is_bool($filters['is_credit_note'])) {
            $filters['is_credit_note'] = boolval($filters['is_credit_note']);
        }

        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($client, $filters, $pagination);
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \InvalidArgumentException
     */
    public function getSumConsumptionBetweenInterval(DeliveryPoint $deliveryPoint, \DateTimeInterface $from, \DateTimeInterface $to): int
    {
        return $this->repository->getSumConsumptionBetweenInterval($deliveryPoint, $from, $to);
    }

    public function getAmountsBetweenInterval(Client $client, ?\DateTimeInterface $start = null, ?\DateTimeInterface $end = null): array
    {
        return $this->repository->getAmountsBetweenInterval($client, $start, $end);
    }

}