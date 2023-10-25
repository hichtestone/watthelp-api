<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\ImportReport;
use App\OptionResolver\ImportReport\SearchOptions;
use App\Repository\ImportReportRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class ImportReportManager
{
    private ImportReportRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(ImportReport::class);
    }

    public function insert(ImportReport $importReport): void
    {
        $this->entityManager->persist($importReport);
        $this->entityManager->flush();
    }

    /**
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \Exception
     */
    public function findByFilters(array $filters, ?Pagination $pagination = null): Paginator
    {
        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($filters, $pagination);
    }
}