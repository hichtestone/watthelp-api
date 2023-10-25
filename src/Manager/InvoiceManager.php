<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Client;
use App\Entity\Invoice;
use App\OptionResolver\Invoice\DeleteOptions;
use App\OptionResolver\Invoice\SearchOptions;
use App\Repository\InvoiceRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;

class InvoiceManager
{
    private InvoiceRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Invoice::class);
    }

    public function insert(Invoice $invoice): void
    {
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
    }

    public function update(Invoice $invoice): void
    {
        $this->entityManager->persist($invoice);
        $this->entityManager->flush();
    }

    public function delete(Invoice $invoice): void
    {
        $this->entityManager->remove($invoice);
        $this->entityManager->flush();
    }

    /**
     * @throws NonUniqueResultException
     * @throws \InvalidArgumentException
     * @throws \LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?Invoice
    {
        return $this->repository->getByCriteria($client, $criteria);
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
        if (isset($filters['has_analysis'])) {
            $filters['has_analysis'] = boolval($filters['has_analysis']);
        }

        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($client, $filters, $pagination);
    }

    public function getCountInvoice(Client $client): int
    {
        $results = $this->repository->getCountInvoice($client);
        return (int)$results[0]['num'];
    }

    public function count(array $criteria): int
    {
        return $this->repository->count($criteria);
    }

    public function deleteByFilters(Client $client, array $filters): void
    {
        $resolver = new DeleteOptions();
        $filters = $resolver->resolve($filters);

        $this->repository->deleteByFilters($client, $filters);
    }

    public function hasInvoicesEmittedAfter(Client $client, \DateTimeInterface $threshold): bool
    {
        return $this->repository->hasInvoicesEmittedAfter($client, $threshold);
    }
}