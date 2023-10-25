<?php

declare(strict_types=1);

namespace App\Manager\Invoice\Anomaly;

use App\Entity\Client;
use App\Entity\Invoice\Anomaly\Note;
use App\Repository\Invoice\Anomaly\NoteRepository;
use App\Request\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

class NoteManager
{
    private NoteRepository $repository;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Note::class);
    }

    public function insert(Note $anomaly): void
    {
        $this->entityManager->persist($anomaly);
        $this->entityManager->flush();
    }
}