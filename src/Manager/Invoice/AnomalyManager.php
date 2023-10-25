<?php

declare(strict_types=1);

namespace App\Manager\Invoice;

use App\Entity\Client;
use App\Entity\Invoice;
use App\Entity\Invoice\Anomaly;
use App\OptionResolver\Invoice\Anomaly\DeleteOptions;
use App\OptionResolver\Invoice\Anomaly\SearchOptions;
use App\Repository\Invoice\AnomalyRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AnomalyManager
{
    private AnomalyRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Anomaly::class);
    }

    public function insert(Anomaly $anomaly): void
    {
        $this->entityManager->persist($anomaly);
        $this->entityManager->flush();
    }

    public function update(Anomaly $anomaly): void
    {
        $anomaly->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->persist($anomaly);
        $this->entityManager->flush();
    }

    public function delete(Anomaly $anomaly): void
    {
        $this->entityManager->remove($anomaly);
        $this->entityManager->flush();
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     *
     */
    private function prepareFilters(Client $client, array $filters): array
    {
        if (isset($filters['created']) && isset($filters['created']['from']) && !$filters['created']['from'] instanceof \DateTimeInterface) {
            $filters['created']['from'] = (new \DateTime($filters['created']['from'], new \DateTimeZone('Europe/Paris')))->setTime(0, 0, 0);
            $filters['created']['from']->setTimezone(new \DateTimeZone('UTC'));
        }

        if (isset($filters['total']) && !is_integer($filters['total'])) {
            $filters['total'] = intval($filters['total']);
        }

        if (isset($filters['total_percentage']) && !is_float($filters['total_percentage'])) {
            $filters['total_percentage'] = floatval($filters['total_percentage']);
        }

        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $filters;
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\MissingOptionsException
     * @throws \Symfony\Component\OptionsResolver\Exception\OptionDefinitionException
     * @throws \Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function findByFilters(Client $client, array $filters, ?Pagination $pagination = null): Paginator
    {
        $filters = $this->prepareFilters($client, $filters);
        return $this->repository->findByFilters($client, $filters, $pagination);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getByCriteria(Client $client, array $criteria): ?Anomaly
    {
        return $this->repository->getByCriteria($client, $criteria);
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
    public function deleteByFilters(Client $client, array $filters): void
    {
        $filters = $this->prepareFilters($client, $filters);
        $this->repository->deleteByFilters($client, $filters);
    }

    public function count(array $criteria): int
    {
        return $this->repository->count($criteria);
    }

    public function getCountAnomalies(Client $client): int
    {
        return (int)$this->repository->getCountAnomalies($client);
    }

    public function getStats(Client $client): array
    {
        return $this->repository->getCountAnomalies($client);
    }
}
