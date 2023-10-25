<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Invoice\DeliveryPointInvoice;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class DeliveryPointInvoiceFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $deliveryPoint1 = $this->getReference('deliveryPoint-1');
        $deliveryPoint2 = $this->getReference('deliveryPoint-2');
        $deliveryPoint3 = $this->getReference('deliveryPoint-3');
        
        $invoice1 = $this->getReference('invoice-1');
        $invoice2 = $this->getReference('invoice-2');
        $invoice3 = $this->getReference('invoice-3');
        $invoice4 = $this->getReference('invoice-4');
        $invoice5 = $this->getReference('invoice-5');
        $invoice6 = $this->getReference('invoice-6');
        $invoice7 = $this->getReference('invoice-7');
        $invoice8 = $this->getReference('invoice-8');
        $invoice9 = $this->getReference('invoice-9');
        $invoice11 = $this->getReference('invoice-11');

        $deliveryPointInvoice1 = new DeliveryPointInvoice();
        $deliveryPointInvoice1->setInvoice($invoice1);
        $deliveryPointInvoice1->setDeliveryPoint($deliveryPoint1);
        $deliveryPointInvoice1->setType(DeliveryPointInvoice::TYPE_ESTIMATED);
        $deliveryPointInvoice1->setAmountHT(4636);
        $deliveryPointInvoice1->setAmountTTC(685);
        $deliveryPointInvoice1->setAmountTVA(5321);
        $deliveryPointInvoice1->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice1);

        $deliveryPointInvoice2 = new DeliveryPointInvoice();
        $deliveryPointInvoice2->setInvoice($invoice2);
        $deliveryPointInvoice2->setDeliveryPoint($deliveryPoint1);
        $deliveryPointInvoice2->setType(DeliveryPointInvoice::TYPE_ESTIMATED);
        $deliveryPointInvoice2->setAmountHT(4636);
        $deliveryPointInvoice2->setAmountTTC(685);
        $deliveryPointInvoice2->setAmountTVA(5321);
        $deliveryPointInvoice2->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice2);

        $deliveryPointInvoice3 = new DeliveryPointInvoice();
        $deliveryPointInvoice3->setInvoice($invoice3);
        $deliveryPointInvoice3->setDeliveryPoint($deliveryPoint1);
        $deliveryPointInvoice3->setType(DeliveryPointInvoice::TYPE_ESTIMATED);
        $deliveryPointInvoice3->setAmountHT(4636);
        $deliveryPointInvoice3->setAmountTTC(685);
        $deliveryPointInvoice3->setAmountTVA(5321);
        $deliveryPointInvoice3->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice3);

        $deliveryPointInvoice4 = new DeliveryPointInvoice();
        $deliveryPointInvoice4->setInvoice($invoice4);
        $deliveryPointInvoice4->setDeliveryPoint($deliveryPoint1);
        $deliveryPointInvoice4->setType(DeliveryPointInvoice::TYPE_ESTIMATED);
        $deliveryPointInvoice4->setAmountHT(4636);
        $deliveryPointInvoice4->setAmountTTC(685);
        $deliveryPointInvoice4->setAmountTVA(5321);
        $deliveryPointInvoice4->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice4);

        $deliveryPointInvoice5 = new DeliveryPointInvoice();
        $deliveryPointInvoice5->setInvoice($invoice5);
        $deliveryPointInvoice5->setDeliveryPoint($deliveryPoint1);
        $deliveryPointInvoice5->setType(DeliveryPointInvoice::TYPE_ESTIMATED);
        $deliveryPointInvoice5->setAmountHT(4636);
        $deliveryPointInvoice5->setAmountTTC(685);
        $deliveryPointInvoice5->setAmountTVA(5321);
        $deliveryPointInvoice5->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice5);

        $deliveryPointInvoice6 = new DeliveryPointInvoice();
        $deliveryPointInvoice6->setInvoice($invoice6);
        $deliveryPointInvoice6->setDeliveryPoint($deliveryPoint1);
        $deliveryPointInvoice6->setType(DeliveryPointInvoice::TYPE_ESTIMATED);
        $deliveryPointInvoice6->setAmountHT(4636);
        $deliveryPointInvoice6->setAmountTTC(685);
        $deliveryPointInvoice6->setAmountTVA(5321);
        $deliveryPointInvoice6->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice6);

        $deliveryPointInvoice7 = new DeliveryPointInvoice();
        $deliveryPointInvoice7->setInvoice($invoice7);
        $deliveryPointInvoice7->setDeliveryPoint($deliveryPoint1);
        $deliveryPointInvoice7->setType(DeliveryPointInvoice::TYPE_ESTIMATED);
        $deliveryPointInvoice7->setAmountHT(4636);
        $deliveryPointInvoice7->setAmountTTC(685);
        $deliveryPointInvoice7->setAmountTVA(5321);
        $deliveryPointInvoice7->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice7);

        $deliveryPointInvoice8 = new DeliveryPointInvoice();
        $deliveryPointInvoice8->setInvoice($invoice8);
        $deliveryPointInvoice8->setDeliveryPoint($deliveryPoint1);
        $deliveryPointInvoice8->setType(DeliveryPointInvoice::TYPE_ESTIMATED);
        $deliveryPointInvoice8->setAmountHT(4636);
        $deliveryPointInvoice8->setAmountTTC(685);
        $deliveryPointInvoice8->setAmountTVA(5321);
        $deliveryPointInvoice8->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice8);

        $deliveryPointInvoice9 = new DeliveryPointInvoice();
        $deliveryPointInvoice9->setInvoice($invoice6);
        $deliveryPointInvoice9->setDeliveryPoint($deliveryPoint2);
        $deliveryPointInvoice9->setType(DeliveryPointInvoice::TYPE_REAL);
        $deliveryPointInvoice9->setAmountHT(1000);
        $deliveryPointInvoice9->setAmountTTC(1200);
        $deliveryPointInvoice9->setAmountTVA(200);
        $deliveryPointInvoice9->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice9);

        $deliveryPointInvoice10 = new DeliveryPointInvoice();
        $deliveryPointInvoice10->setInvoice($invoice7);
        $deliveryPointInvoice10->setDeliveryPoint($deliveryPoint2);
        $deliveryPointInvoice10->setType(DeliveryPointInvoice::TYPE_REAL);
        $deliveryPointInvoice10->setAmountHT(1000);
        $deliveryPointInvoice10->setAmountTTC(1200);
        $deliveryPointInvoice10->setAmountTVA(200);
        $deliveryPointInvoice10->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice10);

        $deliveryPointInvoice11 = new DeliveryPointInvoice();
        $deliveryPointInvoice11->setInvoice($invoice9);
        $deliveryPointInvoice11->setDeliveryPoint($deliveryPoint3);
        $deliveryPointInvoice11->setType(DeliveryPointInvoice::TYPE_ESTIMATED);
        $deliveryPointInvoice11->setAmountHT(4636);
        $deliveryPointInvoice11->setAmountTTC(5321);
        $deliveryPointInvoice11->setAmountTVA(685);
        $deliveryPointInvoice11->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice11);

        $deliveryPointInvoice12 = new DeliveryPointInvoice();
        $deliveryPointInvoice12->setInvoice($invoice11);
        $deliveryPointInvoice12->setDeliveryPoint($deliveryPoint3);
        $deliveryPointInvoice12->setType(DeliveryPointInvoice::TYPE_REAL);
        $deliveryPointInvoice12->setAmountHT(-5636*10**7);
        $deliveryPointInvoice12->setAmountTTC(-6521*10**7);
        $deliveryPointInvoice12->setAmountTVA(-885*10**7);
        $deliveryPointInvoice12->setPowerSubscribed('48');
        $manager->persist($deliveryPointInvoice12);

        $manager->flush();

        $this->setReference('delivery-point-invoice-1', $deliveryPointInvoice1);
        $this->setReference('delivery-point-invoice-2', $deliveryPointInvoice2);
        $this->setReference('delivery-point-invoice-3', $deliveryPointInvoice3);
        $this->setReference('delivery-point-invoice-4', $deliveryPointInvoice4);
        $this->setReference('delivery-point-invoice-5', $deliveryPointInvoice5);
        $this->setReference('delivery-point-invoice-6', $deliveryPointInvoice6);
        $this->setReference('delivery-point-invoice-7', $deliveryPointInvoice7);
        $this->setReference('delivery-point-invoice-8', $deliveryPointInvoice8);
        $this->setReference('delivery-point-invoice-9', $deliveryPointInvoice9);
        $this->setReference('delivery-point-invoice-10', $deliveryPointInvoice10);
        $this->setReference('delivery-point-invoice-11', $deliveryPointInvoice11);
        $this->setReference('delivery-point-invoice-12', $deliveryPointInvoice12);
    }

    public function getDependencies(): array
    {
        return [
            InvoiceFixtures::class,
            DeliveryPointFixtures::class
        ];
    }
}