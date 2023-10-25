<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Invoice;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class InvoiceFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $client1 = $this->getReference('client-1');
        $client2 = $this->getReference('client-2');
        $client3 = $this->getReference('client-3');

        $invoice1 = new Invoice();
        $invoice1->setClient($client1);
        $invoice1->setReference('FL012018');
        $invoice1->setAmountHT(900);
        $invoice1->setAmountTTC(1100);
        $invoice1->setAmountTVA(200);
        $invoice1->setEmittedAt(new \DateTime('2018-01-01'));
        $manager->persist($invoice1);

        $invoice2 = new Invoice();
        $invoice2->setClient($client1);
        $invoice2->setReference('FL032018');
        $invoice2->setAmountHT(1000);
        $invoice2->setAmountTTC(1200);
        $invoice2->setAmountTVA(200);
        $invoice2->setPdf($this->getReference('file-8'));
        $invoice2->setEmittedAt(new \DateTime('2018-03-01'));
        $manager->persist($invoice2);

        $invoice3 = new Invoice();
        $invoice3->setClient($client1);
        $invoice3->setReference('FL092018');
        $invoice3->setAmountHT(1000);
        $invoice3->setAmountTTC(1200);
        $invoice3->setAmountTVA(200);
        $invoice3->setEmittedAt(new \DateTime('2018-09-01'));
        $manager->persist($invoice3);

        $invoice4 = new Invoice();
        $invoice4->setClient($client1);
        $invoice4->setReference('FL012019');
        $invoice4->setAmountHT(900);
        $invoice4->setAmountTTC(1100);
        $invoice4->setAmountTVA(200);
        $invoice4->setEmittedAt(new \DateTime('2019-01-01'));
        $manager->persist($invoice4);

        $invoice5 = new Invoice();
        $invoice5->setClient($client1);
        $invoice5->setReference('FL032019');
        $invoice5->setAmountHT(1000);
        $invoice5->setAmountTTC(1200);
        $invoice5->setAmountTVA(200);
        $invoice5->setEmittedAt(new \DateTime('2019-03-01'));
        $manager->persist($invoice5);

        $invoice6 = new Invoice();
        $invoice6->setClient($client1);
        $invoice6->setReference('FL092019');
        $invoice6->setAmountHT(1000);
        $invoice6->setAmountTTC(1200);
        $invoice6->setAmountTVA(200);
        $invoice6->setEmittedAt(new \DateTime('2019-09-01'));
        $manager->persist($invoice6);

        $invoice7 = new Invoice();
        $invoice7->setClient($client1);
        $invoice7->setReference('FL032020');
        $invoice7->setAmountHT(1000);
        $invoice7->setAmountTTC(1200);
        $invoice7->setAmountTVA(200);
        $invoice7->setEmittedAt(new \DateTime('2020-03-01'));
        $manager->persist($invoice7);

        $invoice8 = new Invoice();
        $invoice8->setClient($client1);
        $invoice8->setReference('FL092020');
        $invoice8->setAmountHT(1000);
        $invoice8->setAmountTTC(1200);
        $invoice8->setAmountTVA(200);
        $invoice8->setEmittedAt(new \DateTime('2020-10-01'));
        $manager->persist($invoice8);

        $invoice9 = new Invoice();
        $invoice9->setClient($client2);
        $invoice9->setReference('FL2092020');
        $invoice9->setAmountHT(1000);
        $invoice9->setAmountTTC(1200);
        $invoice9->setAmountTVA(200);
        $invoice9->setEmittedAt(new \DateTime('2020-09-01'));
        $manager->persist($invoice9);

        $invoice10 = new Invoice();
        $invoice10->setClient($client3);
        $invoice10->setReference('DIRECT_ENERGIE_TEST_REF');
        $invoice10->setAmountHT(1000*10**7);
        $invoice10->setAmountTTC(1200*10**7);
        $invoice10->setAmountTVA(200*10**7);
        $invoice10->setEmittedAt(new \DateTime('2020-09-01'));
        $manager->persist($invoice10);

        $invoice11 = new Invoice();
        $invoice11->setClient($client2);
        $invoice11->setReference('CLIENT2_CREDIT_NOTE');
        $invoice11->setAmountHT(-1000*10**7);
        $invoice11->setAmountTTC(-1200*10**7);
        $invoice11->setAmountTVA(-200*10**7);
        $invoice11->setEmittedAt(new \DateTime('2020-11-01'));
        $manager->persist($invoice11);

        $manager->flush();

        $this->setReference('invoice-1', $invoice1);
        $this->setReference('invoice-2', $invoice2);
        $this->setReference('invoice-3', $invoice3);
        $this->setReference('invoice-4', $invoice4);
        $this->setReference('invoice-5', $invoice5);
        $this->setReference('invoice-6', $invoice6);
        $this->setReference('invoice-7', $invoice7);
        $this->setReference('invoice-8', $invoice8);
        $this->setReference('invoice-9', $invoice9);
        $this->setReference('invoice-10', $invoice10);
        $this->setReference('invoice-11', $invoice11);
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
            FileFixtures::class
        ];
    }
}