<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Invoice\Analysis\DeliveryPointInvoiceAnalysis;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class DeliveryPointInvoiceAnalysisFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $deliveryPointInvoiceAnalysis1 = new DeliveryPointInvoiceAnalysis();
        $deliveryPointInvoiceAnalysis1->setAnalysis($this->getReference('analysis-1'));
        $deliveryPointInvoiceAnalysis1->setDeliveryPointInvoice($this->getReference('delivery-point-invoice-1'));

        $manager->persist($deliveryPointInvoiceAnalysis1);


        $deliveryPointInvoiceAnalysis2 = new DeliveryPointInvoiceAnalysis();
        $deliveryPointInvoiceAnalysis2->setAnalysis($this->getReference('analysis-2'));
        $deliveryPointInvoiceAnalysis2->setDeliveryPointInvoice($this->getReference('delivery-point-invoice-2'));

        $manager->persist($deliveryPointInvoiceAnalysis2);

        $manager->flush();

        $this->setReference('delivery-point-invoice-analysis-1', $deliveryPointInvoiceAnalysis1);
        $this->setReference('delivery-point-invoice-analysis-2', $deliveryPointInvoiceAnalysis2);
    }

    public function getDependencies(): array
    {
        return [
            AnalysisFixtures::class,
            DeliveryPointInvoiceFixtures::class
        ];
    }
}