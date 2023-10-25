<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Invoice\InvoiceSubscription;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class InvoiceSubscriptionFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $deliveryPointInvoice1 = $this->getReference('delivery-point-invoice-1');
        $deliveryPointInvoice2 = $this->getReference('delivery-point-invoice-2');
        $deliveryPointInvoice3 = $this->getReference('delivery-point-invoice-3');
        $deliveryPointInvoice12 = $this->getReference('delivery-point-invoice-12');

        $invoiceSubscription1 = new InvoiceSubscription();
        $invoiceSubscription1->setDeliveryPointInvoice($deliveryPointInvoice1);
        $invoiceSubscription1->setStartedAt(new \DateTime('2018-06-01'));
        $invoiceSubscription1->setFinishedAt(new \DateTime('2018-07-31'));
        $invoiceSubscription1->setQuantity(1);
        $invoiceSubscription1->setUnitPrice(1125);
        $invoiceSubscription1->setTotal(1125);
        $manager->persist($invoiceSubscription1);

        $invoiceSubscription2 = new InvoiceSubscription();
        $invoiceSubscription2->setDeliveryPointInvoice($deliveryPointInvoice2);
        $invoiceSubscription2->setStartedAt(new \DateTime('2018-06-01'));
        $invoiceSubscription2->setFinishedAt(new \DateTime('2018-07-31'));
        $invoiceSubscription2->setQuantity(1);
        $invoiceSubscription2->setUnitPrice(1125);
        $invoiceSubscription2->setTotal(1125);
        $manager->persist($invoiceSubscription2);

        $invoiceSubscription3 = new InvoiceSubscription();
        $invoiceSubscription3->setDeliveryPointInvoice($deliveryPointInvoice3);
        $invoiceSubscription3->setStartedAt(new \DateTime('2018-06-01'));
        $invoiceSubscription3->setFinishedAt(new \DateTime('2018-07-31'));
        $invoiceSubscription3->setQuantity(1);
        $invoiceSubscription3->setUnitPrice(1125);
        $invoiceSubscription3->setTotal(1125);
        $manager->persist($invoiceSubscription3);

        $invoiceSubscription4 = new InvoiceSubscription();
        $invoiceSubscription4->setDeliveryPointInvoice($deliveryPointInvoice12);
        $invoiceSubscription4->setStartedAt($deliveryPointInvoice12->getConsumption()->getStartedAt());
        $invoiceSubscription4->setFinishedAt($deliveryPointInvoice12->getConsumption()->getFinishedAt());
        $invoiceSubscription4->setTotal((int) (154.64*10**7));
        $manager->persist($invoiceSubscription4);

        $manager->flush();

        $this->setReference('invoice-subscription-1', $invoiceSubscription1);
        $this->setReference('invoice-subscription-2', $invoiceSubscription2);
        $this->setReference('invoice-subscription-3', $invoiceSubscription3);
        $this->setReference('invoice-subscription-4', $invoiceSubscription4);
    }

    public function getDependencies(): array
    {
        return [
            DeliveryPointInvoiceFixtures::class,
            InvoiceConsumptionFixtures::class
        ];
    }
}