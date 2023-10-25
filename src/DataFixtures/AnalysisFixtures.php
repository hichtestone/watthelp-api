<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Invoice\Analysis;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AnalysisFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $analysis = new Analysis();
        $analysis->setInvoice($this->getReference('invoice-1'));
        $manager->persist($analysis);

        $analysis2 = new Analysis();
        $analysis2->setInvoice($this->getReference('invoice-2'));
        $analysis2->setStatus(Analysis::STATUS_WARNING);
        $manager->persist($analysis2);

        $analysis3 = new Analysis();
        $analysis3->setInvoice($this->getReference('invoice-3'));
        $analysis3->setStatus(Analysis::STATUS_ERROR);
        $manager->persist($analysis3);

        $analysis4 = new Analysis();
        $analysis4->setInvoice($this->getReference('invoice-4'));
        $manager->persist($analysis4);

        $analysis5 = new Analysis();
        $analysis5->setInvoice($this->getReference('invoice-9'));
        $manager->persist($analysis5);

        $manager->flush();

        $this->setReference('analysis-1', $analysis);
        $this->setReference('analysis-2', $analysis2);
        $this->setReference('analysis-3', $analysis3);
        $this->setReference('analysis-4', $analysis4);
        $this->setReference('analysis-5', $analysis5);
    }

    public function getDependencies(): array
    {
        return [
            InvoiceFixtures::class
        ];
    }
}