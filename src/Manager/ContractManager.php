<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Client;
use App\Entity\Contract;
use App\OptionResolver\Contract\SearchOptions;
use App\Repository\ContractRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use InvalidArgumentException;
use LogicException;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class ContractManager
{
    private ContractRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Contract::class);
    }

    public function insert(Contract $contract): void
    {
        $this->entityManager->persist($contract);
        $this->entityManager->flush();
    }

    public function update(Contract $contract): void
    {
        $this->entityManager->persist($contract);
        $this->entityManager->flush();
    }

    public function delete(Contract $contract): void
    {
        $this->entityManager->remove($contract);
        $this->entityManager->flush();
    }

    /**
     * @param array $criteria
     * @return Contract|null
     * @throws NonUniqueResultException
     * @throws InvalidArgumentException
     * @throws LogicException
     */
    public function getByCriteria(Client $client, array $criteria): ?Contract
    {
        return $this->repository->getByCriteria($client, $criteria);
    }

    /**
     * @param array $filters
     * @param Pagination | null $pagination
     * @return Paginator
     * @throws UndefinedOptionsException
     * @throws Exception
     * @throws AccessException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     * @throws OptionDefinitionException
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($client, $filters, $pagination);
    }

    /**
     * @throws AccessException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     * @throws OptionDefinitionException
     * @throws UndefinedOptionsException
     */
    public function updateContractsFromContract(Contract $contract): void
    {
        $contractsToUpdate = $this->findByFilters($contract->getClient(), [
            'exclude_ids' => [
                $contract->getId()
            ]
        ]);

        foreach ($contractsToUpdate as $contractToUpdate) {
            $contractToUpdate->setInvoicePeriod($contract->getInvoicePeriod());
            $contractToUpdate->setPricings($contract->getPricings());
            $contractToUpdate->setStartedAt($contract->getStartedAt());
            $contractToUpdate->setFinishedAt($contract->getFinishedAt());
            $this->update($contractToUpdate);
        }
    }

    public function count(array $criteria): int
    {
        return $this->repository->count($criteria);
    }

    public function getMinimumInvoicePeriod(Client $client): ?string
    {
        return $this->repository->getMinimumInvoicePeriod($client);
    }
}