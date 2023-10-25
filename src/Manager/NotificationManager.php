<?php

declare(strict_types=1);

namespace App\Manager;

use App\Entity\Notification;
use App\Entity\User;
use App\OptionResolver\Notification\DeleteOptions;
use App\OptionResolver\Notification\SearchOptions;
use App\Repository\NotificationRepository;
use App\Request\Pagination;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Pagination\Paginator;
use InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\OptionsResolver\Exception\OptionDefinitionException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;

class NotificationManager
{
    private NotificationRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Notification::class);
    }

    /**
     * @param array $criteria
     * @return Notification|null
     * @throws NonUniqueResultException
     */
    public function getByCriteria(array $criteria): ?Notification
    {
        return $this->repository->getByCriteria($criteria);
    }

    /**
     * @throws AccessException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     * @throws OptionDefinitionException
     * @throws UndefinedOptionsException|InvalidArgumentException
     * @throws ORMException
     */
    public function findByFilters(array $filters, ?Pagination $pagination = null): Paginator
    {
        if (!empty($filters['user']) && !$filters['user'] instanceof User) {
            $filters['user'] = $this->entityManager->getReference(User::class, $filters['user']);
        }

        $resolver = new SearchOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->findByFilters($filters, $pagination);
    }

    public function insert(Notification $notification): void
    {
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function update(Notification $notification): void
    {
        $notification->setUpdatedAt(new DateTime());
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }


    public function markAllAsRead(User $user): void
    {
        $this->repository->markAllAsRead($user);
    }

    public function delete(Notification $notification): void
    {
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }

    /**
     * @param array $filters
     * @return mixed
     * @throws ORMException
     */
    public function deleteByFilters(array $filters)
    {
        if (!empty($filters['user']) && !$filters['user'] instanceof User) {
            $filters['user'] = $this->entityManager->getReference(User::class, $filters['user']);
        }

        $resolver = new DeleteOptions();
        $filters = $resolver->resolve($filters);

        return $this->repository->deleteByFilters($filters);
    }
}