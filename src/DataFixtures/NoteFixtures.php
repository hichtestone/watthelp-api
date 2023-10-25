<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Invoice\Anomaly\Note;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class NoteFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $note = new Note();
        $note->setUser($this->getReference('user-1'));
        $note->setAnomaly($this->getReference('anomaly-1'));
        $note->setContent('En cours de traitement par l\'urbanisme.');

        $manager->persist($note);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            AnomalyFixtures::class
        ];
    }
}