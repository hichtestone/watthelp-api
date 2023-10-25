<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Invoice\InvoiceTax;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class InvoiceTaxFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $deliveryPointInvoice1 = $this->getReference('delivery-point-invoice-1');
        $deliveryPointInvoice12 = $this->getReference('delivery-point-invoice-12');

        $invoiceTax1 = new InvoiceTax();
        $invoiceTax1->addDeliveryPointInvoice($deliveryPointInvoice1);
        $invoiceTax1->setType(InvoiceTax::TYPE_TAX_CSPE);
        $invoiceTax1->setStartedAt(new \DateTime('2018-06-17'));
        $invoiceTax1->setFinishedAt(new \DateTime('2018-08-17'));
        $invoiceTax1->setQuantity(369);
        $invoiceTax1->setUnitPrice(165);
        $invoiceTax1->setTotal(609);
        $manager->persist($invoiceTax1);

        $invoiceTax2 = new InvoiceTax();
        $invoiceTax2->addDeliveryPointInvoice($deliveryPointInvoice1);
        $invoiceTax2->setType(InvoiceTax::TYPE_TAX_TDCFE);
        $invoiceTax2->setStartedAt(new \DateTime('2018-06-17'));
        $invoiceTax2->setFinishedAt(new \DateTime('2018-08-17'));
        $invoiceTax2->setQuantity(369);
        $invoiceTax2->setUnitPrice(317);
        $invoiceTax2->setTotal(117);
        $manager->persist($invoiceTax2);

        $invoiceTax3 = new InvoiceTax();
        $invoiceTax3->addDeliveryPointInvoice($deliveryPointInvoice1);
        $invoiceTax3->setType(InvoiceTax::TYPE_TAX_TCCFE);
        $invoiceTax3->setStartedAt(new \DateTime('2018-06-17'));
        $invoiceTax3->setFinishedAt(new \DateTime('2018-08-17'));
        $invoiceTax3->setQuantity(369);
        $invoiceTax3->setUnitPrice(609);
        $invoiceTax3->setTotal(225);
        $manager->persist($invoiceTax3);

        $invoiceTax4 = new InvoiceTax();
        $invoiceTax4->addDeliveryPointInvoice($deliveryPointInvoice1);
        $invoiceTax4->setType(InvoiceTax::TYPE_TAX_CTA);
        $invoiceTax4->setQuantity(1404);
        $invoiceTax4->setUnitPrice(2704);
        $invoiceTax4->setTotal(380);
        $manager->persist($invoiceTax4);

        $invoiceTax5 = new InvoiceTax();
        $invoiceTax5->addDeliveryPointInvoice($deliveryPointInvoice1);
        $invoiceTax5->setType(InvoiceTax::TYPE_TAX_CSPE);
        $invoiceTax5->setStartedAt(new \DateTime('2018-06-17'));
        $invoiceTax5->setFinishedAt(new \DateTime('2018-08-17'));
        $invoiceTax5->setQuantity(369);
        $invoiceTax5->setUnitPrice(165);
        $invoiceTax5->setTotal(609);
        $manager->persist($invoiceTax5);

        $invoiceTax6 = new InvoiceTax();
        $invoiceTax6->addDeliveryPointInvoice($deliveryPointInvoice12);
        $invoiceTax6->setType(InvoiceTax::TYPE_TAX_CSPE);
        $invoiceTax6->setStartedAt($deliveryPointInvoice12->getConsumption()->getStartedAt());
        $invoiceTax6->setFinishedAt($deliveryPointInvoice12->getConsumption()->getFinishedAt());
        $invoiceTax6->setTotal((int) (156.08*10**7));
        $manager->persist($invoiceTax6);

        $invoiceTax7 = new InvoiceTax();
        $invoiceTax7->addDeliveryPointInvoice($deliveryPointInvoice12);
        $invoiceTax7->setType(InvoiceTax::TYPE_TAX_TCFE);
        $invoiceTax7->setStartedAt($deliveryPointInvoice12->getConsumption()->getStartedAt());
        $invoiceTax7->setFinishedAt($deliveryPointInvoice12->getConsumption()->getFinishedAt());
        $invoiceTax7->setTotal((int) (55.17*10**7));
        $manager->persist($invoiceTax7);

        $invoiceTax8 = new InvoiceTax();
        $invoiceTax8->addDeliveryPointInvoice($deliveryPointInvoice12);
        $invoiceTax8->setType(InvoiceTax::TYPE_TAX_CTA);
        $invoiceTax8->setStartedAt($deliveryPointInvoice12->getConsumption()->getStartedAt());
        $invoiceTax8->setFinishedAt($deliveryPointInvoice12->getConsumption()->getFinishedAt());
        $invoiceTax8->setTotal((int) (29.52*10**7));
        $manager->persist($invoiceTax8);

        $manager->flush();

        $this->setReference('invoice-tax-1', $invoiceTax1);
        $this->setReference('invoice-tax-2', $invoiceTax2);
        $this->setReference('invoice-tax-3', $invoiceTax3);
        $this->setReference('invoice-tax-4', $invoiceTax4);
        $this->setReference('invoice-tax-5', $invoiceTax5);
        $this->setReference('invoice-tax-6', $invoiceTax6);
        $this->setReference('invoice-tax-7', $invoiceTax7);
        $this->setReference('invoice-tax-8', $invoiceTax8);
    }

    public function getDependencies(): array
    {
        return [
            DeliveryPointInvoiceFixtures::class,
            InvoiceConsumptionFixtures::class
        ];
    }
}