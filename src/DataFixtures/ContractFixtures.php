<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Contract;
use App\Entity\Pricing;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ContractFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $client1 = $this->getReference('client-1');
        $client2 = $this->getReference('client-2');

        $contract1 = new Contract();
        $contract1->setReference('CONTRACT_REF_645894623');
        $contract1->setProvider(Contract::PROVIDER_EDF);
        $contract1->setType(Pricing::TYPE_NEGOTIATED);
        $contract1->setInvoicePeriod(Contract::INVOICE_PERIOD_6);
        $contract1->setStartedAt(new \DateTimeImmutable('2019-01-01T00:00:00+00:00'));
        $contract1->setFinishedAt(new \DateTimeImmutable('2020-01-01T00:00:00+00:00'));
        $contract1->setClient($client1);
        $manager->persist($contract1);

        $contract2 = new Contract();
        $contract2->setReference('CONTRACT_REF_89756326');
        $contract2->setProvider(Contract::PROVIDER_ENGIE);
        $contract2->setType(Pricing::TYPE_NEGOTIATED);
        $contract2->setInvoicePeriod(Contract::INVOICE_PERIOD_1);
        $contract2->setStartedAt(new \DateTimeImmutable('2021-01-01T00:00:00+00:00'));
        $contract2->setClient($client1);
        $manager->persist($contract2);

        $contract3 = new Contract();
        $contract3->setReference('CONTRACT_REF_232399773');
        $contract3->setProvider(Contract::PROVIDER_EDF);
        $contract3->setType(Pricing::TYPE_NEGOTIATED);
        $contract3->setInvoicePeriod(Contract::INVOICE_PERIOD_2);
        $contract3->setStartedAt(new \DateTimeImmutable('2020-08-01T00:00:00+00:00'));
        $contract3->setFinishedAt(new \DateTimeImmutable('2021-01-01T00:00:00+00:00'));
        $contract3->setClient($client1);
        $manager->persist($contract3);

        $contract4 = new Contract();
        $contract4->setReference('CONTRACT_REF_789127986');
        $contract4->setProvider(Contract::PROVIDER_DIRECT_ENERGIE);
        $contract4->setType(Pricing::TYPE_REGULATED);
        $contract4->setInvoicePeriod(null);
        $contract4->setStartedAt(new \DateTimeImmutable('2020-01-01T00:00:00+00:00'));
        $contract4->setFinishedAt(new \DateTimeImmutable('2021-01-01T00:00:00+00:00'));
        $contract4->setClient($client2);
        $manager->persist($contract4);

        $contract5 = new Contract();
        $contract5->setReference('CONTRACT_REF_856423167');
        $contract5->setProvider(Contract::PROVIDER_DIRECT_ENERGIE);
        $contract5->setType(Pricing::TYPE_NEGOTIATED);
        $contract5->setInvoicePeriod(null);
        $contract5->setStartedAt(new \DateTimeImmutable('2019-01-01T00:00:00+00:00'));
        $contract5->setFinishedAt(new \DateTimeImmutable('2020-01-01T00:00:00+00:00'));
        $contract5->setClient($client2);
        $manager->persist($contract5);

        $manager->flush();

        $this->setReference('contract-1', $contract1);
        $this->setReference('contract-2', $contract2);
        $this->setReference('contract-3', $contract3);
        $this->setReference('contract-4', $contract4);
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class
        ];
    }
}
