<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\File;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FileFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $client1 = $this->getReference('client-1');
        $user1 = $this->getReference('user-1');

        $file1 = new File();
        $file1->setName('file1.png');
        $file1->setRaw('000000001/file1.png');
        $file1->setThumb('000000001/thumb/file1.png');
        $file1->setMime('image/png');
        $file1->setUser($user1);
        $manager->persist($file1);

        $invoiceDirectEnergy = new File();
        $invoiceDirectEnergy->setName('1/phpcboQ26.xlsx');
        $invoiceDirectEnergy->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpcboQ26.xlsx');
        $invoiceDirectEnergy->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/thumb/1/phpcboQ26.xlsx');
        $invoiceDirectEnergy->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $invoiceDirectEnergy->setUser($user1);
        $manager->persist($invoiceDirectEnergy);

        $invoiceEngie2019 = new File();
        $invoiceEngie2019->setName('1/phpsh7NN6.zip');
        $invoiceEngie2019->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpsh7NN6.zip');
        $invoiceEngie2019->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/thumb/1/phpsh7NN6.zip');
        $invoiceEngie2019->setMime('application/zip');
        $invoiceEngie2019->setUser($user1);
        $manager->persist($invoiceEngie2019);

        $invoiceEdf2017 = new File();
        $invoiceEdf2017->setName('1/phpoHG77Y.zip');
        $invoiceEdf2017->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpoHG77Y.zip');
        $invoiceEdf2017->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/thumb/1/phpoHG77Y.zip');
        $invoiceEdf2017->setMime('application/zip');
        $invoiceEdf2017->setUser($user1);
        $manager->persist($invoiceEdf2017);

        $deliveryPointImportCorrectFile = new File();
        $deliveryPointImportCorrectFile->setName('1/phpw5KqR2.xlsx');
        $deliveryPointImportCorrectFile->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpw5KqR2.xlsx');
        $deliveryPointImportCorrectFile->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/thumb/1/phpw5KqR2.xlsx');
        $deliveryPointImportCorrectFile->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $deliveryPointImportCorrectFile->setUser($user1);
        $manager->persist($deliveryPointImportCorrectFile);

        $deliveryPointImportInvalidDataFile = new File();
        $deliveryPointImportInvalidDataFile->setName('1/phpxMDvQY.xlsx');
        $deliveryPointImportInvalidDataFile->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpxMDvQY.xlsx');
        $deliveryPointImportInvalidDataFile->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/thumb/1/phpxMDvQY.xlsx');
        $deliveryPointImportInvalidDataFile->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $deliveryPointImportInvalidDataFile->setUser($user1);
        $manager->persist($deliveryPointImportInvalidDataFile);

        $deliveryPointImportInvalidColumnsFile = new File();
        $deliveryPointImportInvalidColumnsFile->setName('1/phpqqXQRq.xlsx');
        $deliveryPointImportInvalidColumnsFile->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpqqXQRq.xlsx');
        $deliveryPointImportInvalidColumnsFile->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/thumb/1/phpqqXQRq.xlsx');
        $deliveryPointImportInvalidColumnsFile->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $deliveryPointImportInvalidColumnsFile->setUser($user1);
        $manager->persist($deliveryPointImportInvalidColumnsFile);

        $invoicePdf = new File();
        $invoicePdf->setName('1/phpKblDKA.pdf');
        $invoicePdf->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpKblDKA.pdf');
        $invoicePdf->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/thumb/1/phpKblDKA.pdf');
        $invoicePdf->setMime('application/pdf');
        $invoicePdf->setUser($user1);
        $manager->persist($invoicePdf);

        $clientLogo = new File();
        $clientLogo->setName('1/phpyWK3T1.png');
        $clientLogo->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpyWK3T1.png');
        $clientLogo->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/phpyWK3T1.png');
        $clientLogo->setMime('image/png');
        $clientLogo->setUser($user1);
        $manager->persist($clientLogo);

        $client1->setLogo($clientLogo);
        $manager->persist($client1);

        // -------------------------------- EDF --------------------------------    
        $edfInvoiceInvalidColumns = new File();
        $edfInvoiceInvalidColumns->setName('1/phpDmK10Q.zip');
        $edfInvoiceInvalidColumns->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpDmK10Q.zip');
        $edfInvoiceInvalidColumns->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/phpDmK10Q.zip');
        $edfInvoiceInvalidColumns->setMime('application/zip');
        $edfInvoiceInvalidColumns->setUser($user1);
        $manager->persist($edfInvoiceInvalidColumns);

        $edfInvoiceInvalidData = new File();
        $edfInvoiceInvalidData->setName('1/phpBRoiWK.zip');
        $edfInvoiceInvalidData->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpBRoiWK.zip');
        $edfInvoiceInvalidData->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/phpBRoiWK.zip');
        $edfInvoiceInvalidData->setMime('application/zip');
        $edfInvoiceInvalidData->setUser($user1);
        $manager->persist($edfInvoiceInvalidData);
        
        $edfInvoiceExistingInvoices = new File();
        $edfInvoiceExistingInvoices->setName('1/phppR9bTG.zip');
        $edfInvoiceExistingInvoices->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phppR9bTG.zip');
        $edfInvoiceExistingInvoices->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/phppR9bTG.zip');
        $edfInvoiceExistingInvoices->setMime('application/zip');
        $edfInvoiceExistingInvoices->setUser($user1);
        $manager->persist($edfInvoiceExistingInvoices);

        // -------------------------------- ENGIE --------------------------------
        $engieInvoiceInvalidColumns = new File();
        $engieInvoiceInvalidColumns->setName('1/phpztjU93.zip');
        $engieInvoiceInvalidColumns->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpztjU93.zip');
        $engieInvoiceInvalidColumns->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/phpztjU93.zip');
        $engieInvoiceInvalidColumns->setMime('application/zip');
        $engieInvoiceInvalidColumns->setUser($user1);
        $manager->persist($engieInvoiceInvalidColumns);

        $engieInvoiceInvalidData = new File();
        $engieInvoiceInvalidData->setName('1/phpqtMpfS.zip');
        $engieInvoiceInvalidData->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpqtMpfS.zip');
        $engieInvoiceInvalidData->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/phpqtMpfS.zip');
        $engieInvoiceInvalidData->setMime('application/zip');
        $engieInvoiceInvalidData->setUser($user1);
        $manager->persist($engieInvoiceInvalidData);
        
        $engieInvoiceExistingInvoices = new File();
        $engieInvoiceExistingInvoices->setName('2/phphBLO7d.zip');
        $engieInvoiceExistingInvoices->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/2/phphBLO7d.zip');
        $engieInvoiceExistingInvoices->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/2/thumb/phphBLO7d.zip');
        $engieInvoiceExistingInvoices->setMime('application/zip');
        $engieInvoiceExistingInvoices->setUser($user1);
        $manager->persist($engieInvoiceExistingInvoices);

        // -------------------------------- Direct Energie --------------------------------    
        $directEnergieInvoiceInvalidColumns = new File();
        $directEnergieInvoiceInvalidColumns->setName('1/php66jbCB.xlsx');
        $directEnergieInvoiceInvalidColumns->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/php66jbCB.xlsx');
        $directEnergieInvoiceInvalidColumns->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/php66jbCB.xlsx');
        $directEnergieInvoiceInvalidColumns->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $directEnergieInvoiceInvalidColumns->setUser($user1);
        $manager->persist($directEnergieInvoiceInvalidColumns);

        $directEnergieInvoiceInvalidData = new File();
        $directEnergieInvoiceInvalidData->setName('2/phpmDWC8s.xlsx');
        $directEnergieInvoiceInvalidData->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/2/phpmDWC8s.xlsx');
        $directEnergieInvoiceInvalidData->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/2/thumb/phpmDWC8s.xlsx');
        $directEnergieInvoiceInvalidData->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $directEnergieInvoiceInvalidData->setUser($user1);
        $manager->persist($directEnergieInvoiceInvalidData);
        
        $directEnergieInvoiceExistingInvoices = new File();
        $directEnergieInvoiceExistingInvoices->setName('3/phpen1SkR.xlsx');
        $directEnergieInvoiceExistingInvoices->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/3/phpen1SkR.xlsx');
        $directEnergieInvoiceExistingInvoices->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/3/thumb/phpen1SkR.xlsx');
        $directEnergieInvoiceExistingInvoices->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $directEnergieInvoiceExistingInvoices->setUser($user1);
        $manager->persist($directEnergieInvoiceExistingInvoices);


        // -------------------------------- Budget --------------------------------
        $budgetValidFile = new File();
        $budgetValidFile->setName('1/phpipnfNa.xlsx');
        $budgetValidFile->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpipnfNa.xlsx');
        $budgetValidFile->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/phpipnfNa.xlsx');
        $budgetValidFile->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $budgetValidFile->setUser($user1);
        $manager->persist($budgetValidFile);

        $budgetInvalidColumnsFile = new File();
        $budgetInvalidColumnsFile->setName('1/phpYQYKDE.xlsx');
        $budgetInvalidColumnsFile->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpYQYKDE.xlsx');
        $budgetInvalidColumnsFile->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/phpYQYKDE.xlsx');
        $budgetInvalidColumnsFile->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $budgetInvalidColumnsFile->setUser($user1);
        $manager->persist($budgetInvalidColumnsFile);

        $budgetInvalidBudgetSheetFile = new File();
        $budgetInvalidBudgetSheetFile->setName('1/php39UPDi.xlsx');
        $budgetInvalidBudgetSheetFile->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/php39UPDi.xlsx');
        $budgetInvalidBudgetSheetFile->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/php39UPDi.xlsx');
        $budgetInvalidBudgetSheetFile->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $budgetInvalidBudgetSheetFile->setUser($user1);
        $manager->persist($budgetInvalidBudgetSheetFile);

        $budgetInvalidDpBudgetSheetFile = new File();
        $budgetInvalidDpBudgetSheetFile->setName('1/phpZWOS0t.xlsx');
        $budgetInvalidDpBudgetSheetFile->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpZWOS0t.xlsx');
        $budgetInvalidDpBudgetSheetFile->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/phpZWOS0t.xlsx');
        $budgetInvalidDpBudgetSheetFile->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $budgetInvalidDpBudgetSheetFile->setUser($user1);
        $manager->persist($budgetInvalidDpBudgetSheetFile);

        $budgetValidFileExistingYear = new File();
        $budgetValidFileExistingYear->setName('1/phpN8I8so.xlsx');
        $budgetValidFileExistingYear->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpN8I8so.xlsx');
        $budgetValidFileExistingYear->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/phpN8I8so.xlsx');
        $budgetValidFileExistingYear->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $budgetValidFileExistingYear->setUser($user1);
        $manager->persist($budgetValidFileExistingYear);


        $reimportInvoiceFakeDirectEnergie = new File();
        $reimportInvoiceFakeDirectEnergie->setName('1/php0GNbVM.xlsx');
        $reimportInvoiceFakeDirectEnergie->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/php0GNbVM.xlsx');
        $reimportInvoiceFakeDirectEnergie->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/php0GNbVM.xlsx');
        $reimportInvoiceFakeDirectEnergie->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $reimportInvoiceFakeDirectEnergie->setUser($user1);
        $manager->persist($reimportInvoiceFakeDirectEnergie);

        $pricing = new File();
        $pricing->setName('1/phpoEgfiP.xlsx');
        $pricing->setRaw('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/phpoEgfiP.xlsx');
        $pricing->setThumb('https://sprint-watthelp-file.s3.eu-west-3.amazonaws.com/1/thumb/phpoEgfiP.xlsx');
        $pricing->setMime('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $pricing->setUser($user1);
        $manager->persist($pricing);


        $manager->flush();

        $this->setReference('file-1', $file1);
        $this->setReference('file-2', $invoiceDirectEnergy);
        $this->setReference('file-3', $invoiceEngie2019);
        $this->setReference('file-4', $invoiceEdf2017);
        $this->setReference('file-5', $deliveryPointImportCorrectFile);
        $this->setReference('file-6', $deliveryPointImportInvalidDataFile);
        $this->setReference('file-7', $deliveryPointImportInvalidColumnsFile);
        $this->setReference('file-8', $invoicePdf);
        $this->setReference('file-9', $clientLogo);
        $this->setReference('file-10', $edfInvoiceInvalidColumns);
        $this->setReference('file-11', $edfInvoiceInvalidData);
        $this->setReference('file-12', $edfInvoiceExistingInvoices);
        $this->setReference('file-13', $engieInvoiceInvalidColumns);
        $this->setReference('file-14', $engieInvoiceInvalidData);
        $this->setReference('file-15', $engieInvoiceExistingInvoices);
        $this->setReference('file-16', $directEnergieInvoiceInvalidColumns);
        $this->setReference('file-17', $directEnergieInvoiceInvalidData);
        $this->setReference('file-18', $directEnergieInvoiceExistingInvoices);
        $this->setReference('file-19', $budgetValidFile);
        $this->setReference('file-20', $budgetInvalidColumnsFile);
        $this->setReference('file-21', $budgetInvalidBudgetSheetFile);
        $this->setReference('file-22', $budgetInvalidDpBudgetSheetFile);
        $this->setReference('file-23', $budgetValidFileExistingYear);
        $this->setReference('file-24', $reimportInvoiceFakeDirectEnergie);
        $this->setReference('file-25', $pricing);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ClientFixtures::class
        ];
    }
}
