<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Pricing;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class PricingFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $client1 = $this->getReference('client-1');
        $client2 = $this->getReference('client-2');
        $contract1 = $this->getReference('contract-1');
        $contract2 = $this->getReference('contract-2');
        $contract3 = $this->getReference('contract-3');
        $contract4 = $this->getReference('contract-4');

        $pricing1 = new Pricing();
        $pricing1->setName('Pricing_1');
        $pricing1->setType(Pricing::TYPE_NEGOTIATED);
        $pricing1->setConsumptionBasePrice(12);
        $pricing1->setStartedAt(new \DateTimeImmutable('2019-09-17T00:00:00+00:00'));
        $pricing1->setFinishedAt(new \DateTimeImmutable('2020-07-18T00:00:00+00:00'));
        $pricing1->setClient($client1);
        $pricing1->addContract($contract1);
        $manager->persist($pricing1);

        $pricing2 = new Pricing();
        $pricing2->setName('Pricing_2');
        $pricing2->setType(Pricing::TYPE_NEGOTIATED);
        $pricing2->setConsumptionBasePrice(175);
        $pricing2->setStartedAt(new \DateTimeImmutable('2020-07-19T00:00:00+00:00'));
        $pricing2->setFinishedAt(new \DateTimeImmutable('2020-09-17T00:00:00+00:00'));
        $pricing2->setClient($client1);
        $pricing2->addContract($contract2);
        $manager->persist($pricing2);

        $pricing3 = new Pricing();
        $pricing3->setName('Pricing_3');
        $pricing3->setType(Pricing::TYPE_NEGOTIATED);
        $pricing3->setConsumptionBasePrice(723);
        $pricing3->setStartedAt(new \DateTimeImmutable('2020-09-18T00:00:00+00:00'));
        $pricing3->setFinishedAt(new \DateTimeImmutable('2021-01-01T00:00:00+00:00'));
        $pricing3->setClient($client1);
        $pricing3->addContract($contract2);
        $manager->persist($pricing3);

        $pricing4 = new Pricing();
        $pricing4->setName('Pricing_4');
        $pricing4->setType(Pricing::TYPE_NEGOTIATED);
        $pricing4->setConsumptionBasePrice(812);
        $pricing4->setStartedAt(new \DateTimeImmutable('2018-07-19T00:00:00+00:00'));
        $pricing4->setFinishedAt(new \DateTimeImmutable('2020-01-01T00:00:00+00:00'));
        $pricing4->setClient($client1);
        $pricing4->addContract($contract2);
        $pricing4->addContract($contract3);
        $manager->persist($pricing4);

        $pricing5 = new Pricing();
        $pricing5->setName('Pricing_5');
        $pricing5->setType(Pricing::TYPE_REGULATED);
        $pricing5->setSubscriptionPrice(709);
        $pricing5->setConsumptionBasePrice(378);
        $pricing5->setStartedAt(new \DateTimeImmutable('2020-01-01T00:00:00+00:00'));
        $pricing5->setFinishedAt(new \DateTimeImmutable('2021-01-01T00:00:00+00:00'));
        $pricing5->setClient($client2);
        $pricing5->addContract($contract4);
        $manager->persist($pricing5);

        $pricing6 = new Pricing();
        $pricing6->setName('Pricing_6');
        $pricing6->setType(Pricing::TYPE_NEGOTIATED);
        $pricing6->setConsumptionBasePrice(378);
        $pricing6->setStartedAt(new \DateTimeImmutable('2020-01-01T00:00:00+00:00'));
        $pricing6->setFinishedAt(new \DateTimeImmutable('2021-01-01T00:00:00+00:00'));
        $pricing6->setClient($client2);
        $manager->persist($pricing6);

        $manager->flush();

        $this->setReference('pricing-1', $pricing1);
        $this->setReference('pricing-2', $pricing2);
        $this->setReference('pricing-3', $pricing3);
        $this->setReference('pricing-4', $pricing4);
        $this->setReference('pricing-5', $pricing5);
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
            ContractFixtures::class
        ];
    }
}