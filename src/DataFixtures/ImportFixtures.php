<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Contract;
use App\Entity\Import;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImportFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $user1 = $this->getReference('user-1');
        $user2 = $this->getReference('user-2');
        $file3 = $this->getReference('file-3');
        $file4 = $this->getReference('file-4');
        $file19 = $this->getReference('file-19');

        $invoiceImport1 = new Import();
        $invoiceImport1->setUser($user1);
        $invoiceImport1->setFile($file4);
        $invoiceImport1->setType(Import::TYPE_INVOICE);
        $invoiceImport1->setProvider(Contract::PROVIDER_EDF);
        $manager->persist($invoiceImport1);

        $invoiceImport2 = new Import();
        $invoiceImport2->setUser($user2);
        $invoiceImport2->setFile($file3);
        $invoiceImport2->setType(Import::TYPE_INVOICE);
        $invoiceImport2->setProvider(Contract::PROVIDER_ENGIE);
        $manager->persist($invoiceImport2);

        $invoiceImport3 = new Import();
        $invoiceImport3->setUser($user1);
        $invoiceImport3->setFile($file4);
        $invoiceImport3->setType(Import::TYPE_INVOICE);
        $invoiceImport3->setProvider(Contract::PROVIDER_EDF);
        $manager->persist($invoiceImport3);

        $invoiceImport4 = new Import();
        $invoiceImport4->setUser($user1);
        $invoiceImport4->setFile($file4);
        $invoiceImport4->setType(Import::TYPE_INVOICE);
        $invoiceImport4->setProvider(Contract::PROVIDER_EDF);
        $manager->persist($invoiceImport4);

        $budgetImport1 = new Import();
        $budgetImport1->setUser($user1);
        $budgetImport1->setFile($file19);
        $budgetImport1->setType(Import::TYPE_BUDGET);
        $manager->persist($budgetImport1);

        $manager->flush();

        $this->setReference('invoice-import-1', $invoiceImport1);
        $this->setReference('invoice-import-2', $invoiceImport2);
        $this->setReference('invoice-import-3', $invoiceImport3);
        $this->setReference('invoice-import-4', $invoiceImport4);
        $this->setReference('budget-import-1', $budgetImport1);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            FileFixtures::class
        ];
    }
}