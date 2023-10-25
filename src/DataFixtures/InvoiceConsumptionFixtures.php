<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Invoice\InvoiceConsumption;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class InvoiceConsumptionFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $deliveryPointInvoice1 = $this->getReference('delivery-point-invoice-1');
        $deliveryPointInvoice2 = $this->getReference('delivery-point-invoice-2');
        $deliveryPointInvoice3 = $this->getReference('delivery-point-invoice-3');
        $deliveryPointInvoice4 = $this->getReference('delivery-point-invoice-4');
        $deliveryPointInvoice5 = $this->getReference('delivery-point-invoice-5');
        $deliveryPointInvoice6 = $this->getReference('delivery-point-invoice-6');
        $deliveryPointInvoice7 = $this->getReference('delivery-point-invoice-7');
        $deliveryPointInvoice8 = $this->getReference('delivery-point-invoice-8');
        $deliveryPointInvoice9 = $this->getReference('delivery-point-invoice-9');
        $deliveryPointInvoice10 = $this->getReference('delivery-point-invoice-10');
        $deliveryPointInvoice11 = $this->getReference('delivery-point-invoice-11');
        $deliveryPointInvoice12 = $this->getReference('delivery-point-invoice-12');

        $invoiceConsumption1 = new InvoiceConsumption();
        $invoiceConsumption1->setDeliveryPointInvoice($deliveryPointInvoice1);
        $invoiceConsumption1->setIndexStart(40903);
        $invoiceConsumption1->setIndexStartedAt(new \DateTime('2018-03-01'));
        $invoiceConsumption1->setIndexFinish(43272);
        $invoiceConsumption1->setIndexFinishedAt(new \DateTime('2018-04-30'));
        $invoiceConsumption1->setStartedAt(new \DateTime('2018-03-01'));
        $invoiceConsumption1->setFinishedAt(new \DateTime('2018-04-30'));
        $invoiceConsumption1->setQuantity(2369);
        $invoiceConsumption1->setUnitPrice(546);
        $invoiceConsumption1->setTotal(2015);
        $manager->persist($invoiceConsumption1);

        $invoiceConsumption2 = new InvoiceConsumption();
        $invoiceConsumption2->setDeliveryPointInvoice($deliveryPointInvoice2);
        $invoiceConsumption2->setIndexStart(43272);
        $invoiceConsumption2->setIndexStartedAt(new \DateTime('2018-01-01'));
        $invoiceConsumption2->setIndexFinish(44154);
        $invoiceConsumption2->setIndexFinishedAt(new \DateTime('2018-02-28'));
        $invoiceConsumption2->setStartedAt(new \DateTime('2018-01-01'));
        $invoiceConsumption2->setFinishedAt(new \DateTime('2018-02-28'));
        $invoiceConsumption2->setQuantity(882);
        $invoiceConsumption2->setUnitPrice(546);
        $invoiceConsumption2->setTotal(2015);
        $manager->persist($invoiceConsumption2);

        $invoiceConsumption3 = new InvoiceConsumption();
        $invoiceConsumption3->setDeliveryPointInvoice($deliveryPointInvoice3);
        $invoiceConsumption3->setIndexStart(44154);
        $invoiceConsumption3->setIndexStartedAt(new \DateTime('2018-07-01'));
        $invoiceConsumption3->setIndexFinish(44988);
        $invoiceConsumption3->setIndexFinishedAt(new \DateTime('2018-08-31'));
        $invoiceConsumption3->setStartedAt(new \DateTime('2018-07-01'));
        $invoiceConsumption3->setFinishedAt(new \DateTime('2018-08-31'));
        $invoiceConsumption3->setQuantity(834);
        $invoiceConsumption3->setUnitPrice(546);
        $invoiceConsumption3->setTotal(2015);
        $manager->persist($invoiceConsumption3);

        $invoiceConsumption4 = new InvoiceConsumption();
        $invoiceConsumption4->setDeliveryPointInvoice($deliveryPointInvoice4);
        $invoiceConsumption4->setIndexStart(47213);
        $invoiceConsumption4->setIndexStartedAt(new \DateTime('2019-11-01'));
        $invoiceConsumption4->setIndexFinish(49544);
        $invoiceConsumption4->setIndexFinishedAt(new \DateTime('2020-01-17'));
        $invoiceConsumption4->setStartedAt(new \DateTime('2019-12-18'));
        $invoiceConsumption4->setFinishedAt(new \DateTime('2020-02-17'));
        $invoiceConsumption4->setQuantity(2661);
        $invoiceConsumption4->setUnitPrice(546);
        $invoiceConsumption4->setTotal(2015);
        $manager->persist($invoiceConsumption4);

        $invoiceConsumption5 = new InvoiceConsumption();
        $invoiceConsumption5->setDeliveryPointInvoice($deliveryPointInvoice5);
        $invoiceConsumption5->setIndexStart(49544);
        $invoiceConsumption5->setIndexStartedAt(new \DateTime('2019-01-01'));
        $invoiceConsumption5->setIndexFinish(51319);
        $invoiceConsumption5->setIndexFinishedAt(new \DateTime('2019-02-28'));
        $invoiceConsumption5->setStartedAt(new \DateTime('2019-01-01'));
        $invoiceConsumption5->setFinishedAt(new \DateTime('2019-02-28'));
        $invoiceConsumption5->setQuantity(1775);
        $invoiceConsumption5->setUnitPrice(546);
        $invoiceConsumption5->setTotal(2015);
        $manager->persist($invoiceConsumption5);

        $invoiceConsumption6 = new InvoiceConsumption();
        $invoiceConsumption6->setDeliveryPointInvoice($deliveryPointInvoice6);
        $invoiceConsumption6->setIndexStart(53319);
        $invoiceConsumption6->setIndexStartedAt(new \DateTime('2019-07-01'));
        $invoiceConsumption6->setIndexFinish(54122);
        $invoiceConsumption6->setIndexFinishedAt(new \DateTime('2019-08-31'));
        $invoiceConsumption6->setStartedAt(new \DateTime('2019-07-01'));
        $invoiceConsumption6->setFinishedAt(new \DateTime('2019-08-31'));
        $invoiceConsumption6->setQuantity(803);
        $invoiceConsumption6->setUnitPrice(546);
        $invoiceConsumption6->setTotal(2015);
        $manager->persist($invoiceConsumption6);

        $invoiceConsumption7 = new InvoiceConsumption();
        $invoiceConsumption7->setDeliveryPointInvoice($deliveryPointInvoice7);
        $invoiceConsumption7->setIndexStart(49544);
        $invoiceConsumption7->setIndexStartedAt(new \DateTime('2020-01-01'));
        $invoiceConsumption7->setIndexFinish(51319);
        $invoiceConsumption7->setIndexFinishedAt(new \DateTime('2020-02-28'));
        $invoiceConsumption7->setStartedAt(new \DateTime('2020-01-01'));
        $invoiceConsumption7->setFinishedAt(new \DateTime('2020-02-28'));
        $invoiceConsumption7->setQuantity(1775);
        $invoiceConsumption7->setUnitPrice(546);
        $invoiceConsumption7->setTotal(2015);
        $manager->persist($invoiceConsumption7);

        $invoiceConsumption8 = new InvoiceConsumption();
        $invoiceConsumption8->setDeliveryPointInvoice($deliveryPointInvoice8);
        $invoiceConsumption8->setIndexStart(53319);
        $invoiceConsumption8->setIndexStartedAt(new \DateTime('2020-07-01'));
        $invoiceConsumption8->setIndexFinish(54122);
        $invoiceConsumption8->setIndexFinishedAt(new \DateTime('2020-08-31'));
        $invoiceConsumption8->setStartedAt(new \DateTime('2020-07-01'));
        $invoiceConsumption8->setFinishedAt(new \DateTime('2020-08-31'));
        $invoiceConsumption8->setQuantity(803);
        $invoiceConsumption8->setUnitPrice(546);
        $invoiceConsumption8->setTotal(2015);
        $manager->persist($invoiceConsumption8);

        $invoiceConsumption9 = new InvoiceConsumption();
        $invoiceConsumption9->setDeliveryPointInvoice($deliveryPointInvoice9);
        $invoiceConsumption9->setIndexStart(53319);
        $invoiceConsumption9->setIndexStartedAt(new \DateTime('2020-07-01'));
        $invoiceConsumption9->setIndexFinish(54122);
        $invoiceConsumption9->setIndexFinishedAt(new \DateTime('2020-08-31'));
        $invoiceConsumption9->setStartedAt(new \DateTime('2020-07-01'));
        $invoiceConsumption9->setFinishedAt(new \DateTime('2020-08-31'));
        $invoiceConsumption9->setQuantity(803);
        $invoiceConsumption9->setUnitPrice(546);
        $invoiceConsumption9->setTotal(2015);
        $manager->persist($invoiceConsumption9);

        $invoiceConsumption10 = new InvoiceConsumption();
        $invoiceConsumption10->setDeliveryPointInvoice($deliveryPointInvoice10);
        $invoiceConsumption10->setIndexStart(53319);
        $invoiceConsumption10->setIndexStartedAt(new \DateTime('2019-07-01'));
        $invoiceConsumption10->setIndexFinish(54122);
        $invoiceConsumption10->setIndexFinishedAt(new \DateTime('2019-08-31'));
        $invoiceConsumption10->setStartedAt(new \DateTime('2019-07-01'));
        $invoiceConsumption10->setFinishedAt(new \DateTime('2019-08-31'));
        $invoiceConsumption10->setQuantity(803);
        $invoiceConsumption10->setUnitPrice(546);
        $invoiceConsumption10->setTotal(2015);
        $manager->persist($invoiceConsumption10);

        $invoiceConsumption11 = new InvoiceConsumption();
        $invoiceConsumption11->setDeliveryPointInvoice($deliveryPointInvoice11);
        $invoiceConsumption11->setIndexStart(53319);
        $invoiceConsumption11->setIndexStartedAt(new \DateTime('2019-07-01'));
        $invoiceConsumption11->setIndexFinish(54122);
        $invoiceConsumption11->setIndexFinishedAt(new \DateTime('2019-08-31'));
        $invoiceConsumption11->setStartedAt(new \DateTime('2019-07-01'));
        $invoiceConsumption11->setFinishedAt(new \DateTime('2019-08-31'));
        $invoiceConsumption11->setQuantity(803);
        $invoiceConsumption11->setUnitPrice(546);
        $invoiceConsumption11->setTotal(2015);
        $manager->persist($invoiceConsumption11);

        $invoiceConsumption12 = new InvoiceConsumption();
        $invoiceConsumption12->setDeliveryPointInvoice($deliveryPointInvoice12);
        $invoiceConsumption12->setIndexStart(35674);
        $invoiceConsumption12->setIndexStartedAt(new \DateTime('2020-08-14'));
        $invoiceConsumption12->setIndexFinish(42611);
        $invoiceConsumption12->setIndexFinishedAt(new \DateTime('2020-10-13'));
        $invoiceConsumption12->setStartedAt(new \DateTime('2020-08-14'));
        $invoiceConsumption12->setFinishedAt(new \DateTime('2020-10-13'));
        $invoiceConsumption12->setQuantity(-6937);
        $invoiceConsumption12->setUnitPrice(intval(6.91*10**5));
        $invoiceConsumption12->setTotal(-789*10**7);
        $manager->persist($invoiceConsumption12);

        $manager->flush();

        $this->setReference('invoice-consumption-1', $invoiceConsumption1);
        $this->setReference('invoice-consumption-2', $invoiceConsumption2);
        $this->setReference('invoice-consumption-3', $invoiceConsumption3);
        $this->setReference('invoice-consumption-4', $invoiceConsumption4);
        $this->setReference('invoice-consumption-5', $invoiceConsumption5);
        $this->setReference('invoice-consumption-6', $invoiceConsumption6);
        $this->setReference('invoice-consumption-7', $invoiceConsumption7);
        $this->setReference('invoice-consumption-8', $invoiceConsumption8);
        $this->setReference('invoice-consumption-9', $invoiceConsumption9);
        $this->setReference('invoice-consumption-10', $invoiceConsumption10);
        $this->setReference('invoice-consumption-11', $invoiceConsumption11);
    }

    public function getDependencies(): array
    {
        return [
            DeliveryPointInvoiceFixtures::class
        ];
    }
}