<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\ImportReport;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImportReportFixtures extends Fixture implements DependentFixtureInterface
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
        $invoice1 = $this->getReference('invoice-1');
        $invoice2 = $this->getReference('invoice-2');
        $invoice3 = $this->getReference('invoice-3');
        $invoice4 = $this->getReference('invoice-4');
        $invoice5 = $this->getReference('invoice-5');
        $invoice6 = $this->getReference('invoice-6');
        $import1 = $this->getReference('invoice-import-1');
        $import2 = $this->getReference('invoice-import-2');
        $import3 = $this->getReference('invoice-import-3');
        $import4 = $this->getReference('invoice-import-4');
        $budgetImport1 = $this->getReference('budget-import-1');
        $anomaly6 = $this->getReference('anomaly-6');
        $anomaly7 = $this->getReference('anomaly-7');
        $budget1 = $this->getReference('budget-1');
        $budget2 = $this->getReference('budget-2');

        $importReport1 = new ImportReport();
        $importReport1->setUser($user1);
        $importReport1->setImport($import1);
        $importReport1->setStatus(ImportReport::STATUS_OK);
        $importReport1->setInvoices(new ArrayCollection([$invoice1, $invoice2, $invoice3]));
        $manager->persist($importReport1);

        $importReport2 = new ImportReport();
        $importReport2->setUser($user2);
        $importReport2->setImport($import2);
        $importReport2->setStatus(ImportReport::STATUS_ERROR);
        $importReport2->setMessages(['Les factures 105105, 145450 ont déjà été importées.']);
        $manager->persist($importReport2);

        $importReport3 = new ImportReport();
        $importReport3->setUser($user1);
        $importReport3->setImport($import3);
        $importReport3->setStatus(ImportReport::STATUS_ERROR);
        $importReport3->setMessages(['Le fichier sites_elec.csv est incorrect: Cellule A1, valeur: "Numéro de facture", nous attendions "Date de la facture".', 'Le fichier sites_elec.csv est incorrect: Cellule B1, valeur: "Date de la facture", nous attendions "Numéro de facture".']);
        $manager->persist($importReport3);

        $importReport4 = new ImportReport();
        $importReport4->setUser($user1);
        $importReport4->setImport($import4);
        $importReport4->setStatus(ImportReport::STATUS_WARNING);
        $importReport4->setInvoices(new ArrayCollection([$invoice4, $invoice5, $invoice6]));
        $importReport4->setAnomalies(new ArrayCollection([$anomaly6, $anomaly7]));
        $manager->persist($importReport4);

        $importReport5 = new ImportReport();
        $importReport5->setUser($user1);
        $importReport5->setImport($budgetImport1);
        $importReport5->setStatus(ImportReport::STATUS_OK);
        $importReport5->setBudgets(new ArrayCollection([$budget1, $budget2]));
        $manager->persist($importReport5);

        $manager->flush();

        $this->setReference('import-report-1', $importReport1);
        $this->setReference('import-report-2', $importReport2);
        $this->setReference('import-report-3', $importReport3);
        $this->setReference('import-report-4', $importReport4);
        $this->setReference('import-report-5', $importReport5);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            InvoiceFixtures::class,
            ImportFixtures::class,
            AnomalyFixtures::class,
            BudgetFixtures::class
        ];
    }
}