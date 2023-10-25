<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Invoice\Analysis\ItemAnalysis;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Gedmo\Translatable\Entity\Repository\TranslationRepository;
use Gedmo\Translatable\Entity\Translation;

class ItemAnalysisFixtures extends Fixture implements DependentFixtureInterface
{
    private TranslationRepository $transRepo;

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->transRepo = $manager->getRepository(Translation::class);

        $analysis1 = $this->getReference('analysis-1');
        $analysis2 = $this->getReference('analysis-2');
        $analysis3 = $this->getReference('analysis-3');
        $analysis4 = $this->getReference('analysis-4');
        $deliveryPointInvoiceAnalysis1 = $this->getReference('delivery-point-invoice-analysis-1');
        $deliveryPointInvoiceAnalysis2 = $this->getReference('delivery-point-invoice-analysis-2');

        $itemAnalysis1 = new ItemAnalysis();
        $itemAnalysis1->setAnalysis($analysis1);
        $itemAnalysis1->setDeliveryPointInvoiceAnalysis($deliveryPointInvoiceAnalysis1);
        $itemAnalysis1->setStatus('warning');
        $itemAnalysis1->setField('consumption.finished_at');
        $itemAnalysis1->addMessage('Impossible de trouver une date de fin de consommation.');
        $itemAnalysis1->setGroup('invoice');
        $this->transRepo->translate($itemAnalysis1, 'messages', 'en', ['Unable to find a consumption date.']);
        $manager->persist($itemAnalysis1);

        $itemAnalysis2 = new ItemAnalysis();
        $itemAnalysis2->setAnalysis($analysis2);
        $itemAnalysis2->setDeliveryPointInvoiceAnalysis($deliveryPointInvoiceAnalysis2);
        $manager->persist($itemAnalysis2);

        $itemAnalysis3 = new ItemAnalysis();
        $itemAnalysis3->setAnalysis($analysis3);
        $manager->persist($itemAnalysis3);

        $itemAnalysis4 = new ItemAnalysis();
        $itemAnalysis4->setAnalysis($analysis4);
        $manager->persist($itemAnalysis4);

        $itemAnalysis5 = new ItemAnalysis();
        $itemAnalysis5->setGroup('tax');
        $itemAnalysis5->addMessage('warning: message 1');
        $itemAnalysis5->addMessage('warning: message 2');
        $manager->persist($itemAnalysis5);

        $manager->flush();

        $this->setReference('item-analysis-1', $itemAnalysis1);
        $this->setReference('item-analysis-2', $itemAnalysis2);
        $this->setReference('item-analysis-3', $itemAnalysis3);
        $this->setReference('item-analysis-4', $itemAnalysis4);
        $this->setReference('item-analysis-5', $itemAnalysis5);
    }

    public function getDependencies(): array
    {
        return [
            InvoiceFixtures::class,
            DeliveryPointInvoiceAnalysisFixtures::class
        ];
    }
}