<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Invoice\Anomaly;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AnomalyFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $itemAnalysis1 = $this->getReference('item-analysis-1');
        $itemAnalysis2 = $this->getReference('item-analysis-2');
        $itemAnalysis3 = $this->getReference('item-analysis-3');
        $itemAnalysis4 = $this->getReference('item-analysis-4');
        $itemAnalysis5 = $this->getReference('item-analysis-5');

        $anomaly1 = new Anomaly();
        $anomaly1->setType(Anomaly::TYPE_CONSUMPTION);
        $anomaly1->setStatus(Anomaly::STATUS_PROCESSING);
        $anomaly1->setProfit(Anomaly::PROFIT_CLIENT);
        $anomaly1->setContent('anomaly1');
        $anomaly1->setTotal(1*10**7); // 1€
        $anomaly1->setTotalPercentage(4.97);
        $anomaly1->setCreatedAt((new \DateTime('2017-01-01'))->setTime(23, 30, 30));
        $anomaly1->setItemAnalysis($itemAnalysis1);
        $itemAnalysis1->setAnomaly($anomaly1);
        $manager->persist($itemAnalysis1);
        $manager->persist($anomaly1);

        $anomaly2 = new Anomaly();
        $anomaly2->setType(Anomaly::TYPE_UNIT_PRICE);
        $anomaly2->setStatus(Anomaly::STATUS_IGNORED);
        $anomaly2->setProfit(Anomaly::PROFIT_PROVIDER);
        $anomaly2->setContent('anomaly2');
        $anomaly2->setTotal(2*10**6); // 0.2€
        $anomaly2->setTotalPercentage(17.0);
        $anomaly2->setCreatedAt(new \DateTime('2017-02-01'));
        $anomaly2->setItemAnalysis($itemAnalysis2);
        $itemAnalysis2->setAnomaly($anomaly2);
        $manager->persist($itemAnalysis2);
        $manager->persist($anomaly2);

        $anomaly3 = new Anomaly();
        $anomaly3->setType(Anomaly::TYPE_INDEX);
        $anomaly3->setStatus(Anomaly::STATUS_SOLVED);
        $anomaly3->setContent('anomaly3');
        $anomaly3->setProfit(Anomaly::PROFIT_PROVIDER);
        $anomaly3->setTotalPercentage(27.95);
        $anomaly3->setTotal(3*10**8); // 30€
        $anomaly3->setItemAnalysis($itemAnalysis3);
        $anomaly3->setCreatedAt(new \DateTime('2017-03-01'));
        $itemAnalysis3->setAnomaly($anomaly3);
        $manager->persist($itemAnalysis3);
        $manager->persist($anomaly3);

        $anomaly4 = new Anomaly();
        $anomaly4->setType(Anomaly::TYPE_INDEX);
        $anomaly4->setStatus(Anomaly::STATUS_SOLVED);
        $anomaly4->setProfit(Anomaly::PROFIT_CLIENT);
        $anomaly4->setContent('anomaly4');
        $anomaly4->setTotal(3*10**5); // 0.03€
        $anomaly4->setTotalPercentage(0.97);
        $anomaly4->setItemAnalysis($itemAnalysis4);
        $anomaly4->setCreatedAt(new \DateTime('2017-04-01'));
        $itemAnalysis4->setAnomaly($anomaly4);
        $manager->persist($itemAnalysis4);
        $manager->persist($anomaly4);

        $anomaly5 = new Anomaly();
        $anomaly5->setType(Anomaly::TYPE_INDEX);
        $anomaly5->setStatus(Anomaly::STATUS_SOLVED);
        $anomaly5->setProfit(Anomaly::PROFIT_PROVIDER);
        $anomaly5->setContent('anomaly5');
        $anomaly5->setTotal(3*10**9); // 300€
        $anomaly5->setTotalPercentage(87.99);
        $anomaly5->setItemAnalysis($itemAnalysis5);
        $anomaly5->setCreatedAt(new \DateTime('2017-05-01'));
        $itemAnalysis5->setAnomaly($anomaly5);
        $manager->persist($itemAnalysis5);
        $manager->persist($anomaly5);

        $anomaly6 = new Anomaly();
        $anomaly6->setType(Anomaly::TYPE_DELIVERY_POINT_CHANGE);
        $anomaly6->setStatus(Anomaly::STATUS_PROCESSING);
        $anomaly6->setContent('La puissance du point de livraison 12193921837009 est manquante sur la facture 10063017297');
        $anomaly6->setReference('46qsdqsds64');
        $anomaly6->setCreatedAt(new \DateTime('2019-06-01'));
        $manager->persist($anomaly6);

        $anomaly7 = new Anomaly();
        $anomaly7->setType(Anomaly::TYPE_DELIVERY_POINT_CHANGE);
        $anomaly7->setStatus(Anomaly::STATUS_PROCESSING);
        $anomaly7->setContent('La puissance du point de livraison 12119102689380 est manquante sur la facture 10054300340');
        $anomaly7->setReference('46qs45dqsds64dazdqs87');
        $anomaly7->setCreatedAt(new \DateTime('2018-06-15'));
        $manager->persist($anomaly7);

        $manager->flush();
        
        $this->setReference('anomaly-1', $anomaly1);
        $this->setReference('anomaly-2', $anomaly2);
        $this->setReference('anomaly-3', $anomaly3);
        $this->setReference('anomaly-4', $anomaly4);
        $this->setReference('anomaly-5', $anomaly5);
        $this->setReference('anomaly-6', $anomaly6);
        $this->setReference('anomaly-7', $anomaly7);
    }

    public function getDependencies(): array
    {
        return [
            ItemAnalysisFixtures::class
        ];
    }
}