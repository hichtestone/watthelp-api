<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Tax;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class TaxFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $client1 = $this->getReference('client-1');
        $client2 = $this->getReference('client-2');

        $tax1 = new Tax();
        $tax1->setCspe(1412);
        $tax1->setTdcfe(4654);
        $tax1->setTccfe(1107);
        $tax1->setCta(2704);
        $tax1->setStartedAt(new \DateTimeImmutable('2019-09-17T00:00:00+00:00'));
        $tax1->setFinishedAt(new \DateTimeImmutable('2020-09-17T00:00:00+00:00'));
        $tax1->setClient($client1);
        $manager->persist($tax1);

        $tax2 = new Tax();
        $tax2->setCspe(2112);
        $tax2->setTdcfe(4654);
        $tax2->setTccfe(489);
        $tax2->setCta(2704);
        $tax2->setStartedAt(new \DateTimeImmutable('2018-01-00T00:00:00+00:00'));
        $tax2->setFinishedAt(new \DateTimeImmutable('2019-01-00T00:00:00+00:00'));
        $tax2->setClient($client1);
        $manager->persist($tax2);

        $tax7 = new Tax();
        $tax7->setCspe(812);
        $tax7->setTdcfe(4654);
        $tax7->setTccfe(489);
        $tax7->setCta(2704);
        $tax7->setStartedAt(new \DateTimeImmutable('2017-07-14T00:00:00+00:00'));
        $tax7->setFinishedAt(new \DateTimeImmutable('2018-07-14T00:00:00+00:00'));
        $tax7->setClient($client1);
        $manager->persist($tax7);

        $tax3 = new Tax();
        $tax3->setCspe(512);
        $tax3->setTdcfe(4654);
        $tax3->setTccfe(489);
        $tax3->setCta(2704);
        $tax3->setStartedAt(new \DateTimeImmutable('2019-07-16T00:00:00+00:00'));
        $tax3->setFinishedAt(new \DateTimeImmutable('2019-09-23T00:00:00+00:00'));
        $tax3->setClient($client1);
        $manager->persist($tax3);

        $tax4 = new Tax();
        $tax4->setCspe(140);
        $tax4->setTdcfe(4654);
        $tax4->setTccfe(489);
        $tax4->setCta(2704);
        $tax4->setStartedAt(new \DateTimeImmutable('2018-01-01T00:00:00+00:00'));
        $tax4->setFinishedAt(new \DateTimeImmutable('2020-07-17T00:00:00+00:00'));
        $tax4->setClient($client1);
        $manager->persist($tax4);

        $tax5 = new Tax();
        $tax5->setCspe(1412);
        $tax5->setTdcfe(4654);
        $tax5->setTccfe(489);
        $tax5->setCta(2704);
        $tax5->setStartedAt(new \DateTimeImmutable('2020-01-01T00:00:00+00:00'));
        $tax5->setFinishedAt(new \DateTimeImmutable('2021-01-01T00:00:00+00:00'));
        $tax5->setClient($client2);
        $manager->persist($tax5);

        $tax6 = new Tax();
        $tax6->setCspe(1207);
        $tax6->setTdcfe(554);
        $tax6->setTccfe(487);
        $tax6->setCta(2704);
        $tax6->setStartedAt(new \DateTimeImmutable('2018-07-16T00:00:00+00:00'));
        $tax6->setFinishedAt(new \DateTimeImmutable('2020-07-17T00:00:00+00:00'));
        $tax6->setClient($client2);
        $manager->persist($tax6);

        $manager->flush();

        $this->setReference('tax-1', $tax1);
        $this->setReference('tax-2', $tax2);
        $this->setReference('tax-3', $tax3);
        $this->setReference('tax-4', $tax4);
        $this->setReference('tax-5', $tax5);
        $this->setReference('tax-6', $tax6);
        $this->setReference('tax-7', $tax7);
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class
        ];
    }
}