<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\DeliveryPoint;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class DeliveryPointFixtures extends Fixture implements DependentFixtureInterface
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
        $contract4 = $this->getReference('contract-4');
        $file1 = $this->getReference('file-1');

        $deliveryPoint1 = new DeliveryPoint();
        $deliveryPoint1->setName('DeliveryPoint1');
        $deliveryPoint1->setReference('REF_DEV_POINT_1');
        $deliveryPoint1->setCode('CODE_DEV_POINT_1');
        $deliveryPoint1->setAddress('1 Bd de la Croisette');
        $deliveryPoint1->setLatitude('43.551420');
        $deliveryPoint1->setLongitude('7.018060');
        $deliveryPoint1->setMeterReference('whatever');
        $deliveryPoint1->setPower('7.2');
        $deliveryPoint1->setClient($client1);
        $deliveryPoint1->setContract($contract1);
        $deliveryPoint1->setPhoto($file1);
        $deliveryPoint1->setDescription('description du pdl');
        $deliveryPoint1->setCreationMode(DeliveryPoint::CREATION_MODE_SCOPE_IMPORT);
        $deliveryPoint1->setIsInScope(true);
        $deliveryPoint1->setScopeDate(new \DateTime('2020-08-15'));
        $manager->persist($deliveryPoint1);

        $deliveryPoint2 = new DeliveryPoint();
        $deliveryPoint2->setName('DeliveryPoint2');
        $deliveryPoint2->setReference('REF_DEV_POINT_2');
        $deliveryPoint2->setCode('CODE_DEV_POINT_2');
        $deliveryPoint2->setAddress('2 Bd de la Croisette');
        $deliveryPoint2->setMeterReference('whatever');
        $deliveryPoint2->setPower('7.2');
        $deliveryPoint2->setClient($client1);
        $deliveryPoint2->setContract($contract2);
        $deliveryPoint2->setDescription(null);
        $deliveryPoint2->setIsInScope(false);
        $deliveryPoint2->setCreationMode(DeliveryPoint::CREATION_MODE_MANUAL);
        $manager->persist($deliveryPoint2);

        $deliveryPoint3 = new DeliveryPoint();
        $deliveryPoint3->setName('DeliveryPoint3');
        $deliveryPoint3->setReference('REF_DEV_POINT_3');
        $deliveryPoint3->setAddress('3 Bd de la Croisette');
        $deliveryPoint3->setMeterReference('whatever');
        $deliveryPoint3->setPower('7.3');
        $deliveryPoint3->setClient($client2);
        $deliveryPoint3->setContract($contract4);
        $deliveryPoint3->setDescription('description pdl');
        $deliveryPoint3->setIsInScope(true);
        $deliveryPoint3->setCreationMode(DeliveryPoint::CREATION_MODE_INVOICE_IMPORT);
        $deliveryPoint3->setScopeDate(new \DateTime('2020-04-15'));
        $manager->persist($deliveryPoint3);

        $manager->flush();

        $this->setReference('deliveryPoint-1', $deliveryPoint1);
        $this->setReference('deliveryPoint-2', $deliveryPoint2);
        $this->setReference('deliveryPoint-3', $deliveryPoint3);
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
            ContractFixtures::class,
            FileFixtures::class
        ];
    }
}