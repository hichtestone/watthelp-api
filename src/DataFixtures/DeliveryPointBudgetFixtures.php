<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Budget\DeliveryPointBudget;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class DeliveryPointBudgetFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $budget1 = $this->getReference('budget-1');
        $budget2 = $this->getReference('budget-2');
        $budget3 = $this->getReference('budget-3');
        $deliveryPoint1 = $this->getReference('deliveryPoint-1');
        $deliveryPoint2 = $this->getReference('deliveryPoint-2');
        $deliveryPoint3 = $this->getReference('deliveryPoint-3');

        $point1Budget1 = new DeliveryPointBudget();
        $point1Budget1->setBudget($budget1);
        $point1Budget1->setDeliveryPoint($deliveryPoint1);
        $point1Budget1->setInstalledPower('12');
        $point1Budget1->setEquipmentPowerPercentage(2000); // 20%
        $point1Budget1->setGradation(7000); // 70%
        $point1Budget1->setGradationHours(1600);
        $point1Budget1->setSubTotalConsumption(45648*10**2); // 45648 kWh
        $point1Budget1->setRenovation(true);
        $point1Budget1->setRenovatedAt(new \DateTime('2018-04-01'));
        $point1Budget1->setNewInstalledPower('10');
        $point1Budget1->setNewEquipmentPowerPercentage(0);
        $point1Budget1->setNewGradation(7000); // 70%
        $point1Budget1->setNewGradationHours(1600);
        $point1Budget1->setNewSubTotalConsumption(31700*10**2); // 31700 kWh
        $point1Budget1->setTotalConsumption(3634933); // 36349.33 kWh
        $point1Budget1->setTotal(545240*10**5); // 5452.40 €
        $point1Budget1->setCreatedAt(new \DateTime('2019-09-01'));
        $point1Budget1->setUpdatedAt(new \DateTime('2019-11-01'));
        $manager->persist($point1Budget1);

        $point2Budget1 = new DeliveryPointBudget();
        $point2Budget1->setBudget($budget1);
        $point2Budget1->setDeliveryPoint($deliveryPoint2);
        $point2Budget1->setInstalledPower('10');
        $point2Budget1->setEquipmentPowerPercentage(1000); // 10%
        $point2Budget1->setGradation(5000); // 50%
        $point2Budget1->setGradationHours(1800);
        $point2Budget1->setSubTotalConsumption(30250*10**2); // 30250kWh
        $point2Budget1->setRenovation(true);
        $point2Budget1->setRenovatedAt(new \DateTime('2018-06-01'));
        $point2Budget1->setNewInstalledPower('5');
        $point2Budget1->setNewEquipmentPowerPercentage(0);
        $point2Budget1->setNewGradation(5000); // 50%
        $point2Budget1->setNewGradationHours(1800);
        $point2Budget1->setNewSubTotalConsumption(13750*10**2); // 13750 kWh 
        $point2Budget1->setTotalConsumption(22000*10**2); // 22000 kWh
        $point2Budget1->setTotal(3300*10**7); // 3300€
        $point2Budget1->setCreatedAt(new \DateTime('2019-09-01'));
        $point2Budget1->setUpdatedAt(new \DateTime('2019-11-01'));
        $manager->persist($point2Budget1);

        $point1Budget2 = new DeliveryPointBudget();
        $point1Budget2->setBudget($budget2);
        $point1Budget2->setDeliveryPoint($deliveryPoint1);
        $point1Budget2->setInstalledPower('12');
        $point1Budget2->setEquipmentPowerPercentage(2000); // 20%
        $point1Budget2->setGradation(6500); // 65%
        $point1Budget2->setGradationHours(1500);
        $point1Budget2->setSubTotalConsumption(45000*10**2); // 45000kWh
        $point1Budget2->setRenovation(true);
        $point1Budget2->setRenovatedAt(new \DateTime('2019-08-01'));
        $point1Budget2->setNewInstalledPower('10');
        $point1Budget2->setNewEquipmentPowerPercentage(0);
        $point1Budget2->setNewGradation(6500); // 65%
        $point1Budget2->setNewGradationHours(1500);
        $point1Budget2->setNewSubTotalConsumption(31250*10**2); // 31250 kWh
        $point1Budget2->setTotalConsumption(4099867); // 40998.67 kWh
        $point1Budget2->setTotal(655979*10**5); // 6559,79€
        $point1Budget2->setCreatedAt(new \DateTime('2020-09-01'));
        $point1Budget2->setUpdatedAt(new \DateTime('2020-11-01'));
        $manager->persist($point1Budget2);

        $point2Budget2 = new DeliveryPointBudget();
        $point2Budget2->setBudget($budget2);
        $point2Budget2->setDeliveryPoint($deliveryPoint2);
        $point2Budget2->setInstalledPower('10');
        $point2Budget2->setEquipmentPowerPercentage(1000); // 10%
        $point2Budget2->setGradation(6500); // 65%
        $point2Budget2->setGradationHours(1500);
        $point2Budget2->setSubTotalConsumption(34375*10**2); // 34375 kWh
        $point2Budget2->setRenovation(true);
        $point2Budget2->setRenovatedAt(new \DateTime('2019-08-01'));
        $point2Budget2->setNewInstalledPower('5');
        $point2Budget2->setNewEquipmentPowerPercentage(0);
        $point2Budget2->setNewGradation(6500); // 65%
        $point2Budget2->setNewGradationHours(1600);
        $point2Budget2->setNewSubTotalConsumption(15625*10**2); // 15625 kWh
        $point2Budget2->setTotalConsumption(28125*10**2); // 28125 kWh
        $point2Budget2->setTotal(4500*10**7); // 4500€
        $point2Budget2->setCreatedAt(new \DateTime('2020-09-01'));
        $point2Budget2->setUpdatedAt(new \DateTime('2020-11-01'));
        $manager->persist($point2Budget2);

        $Point3Budget2016LightClient2 = new DeliveryPointBudget();
        $Point3Budget2016LightClient2->setBudget($budget3);
        $Point3Budget2016LightClient2->setDeliveryPoint($deliveryPoint3);
        $Point3Budget2016LightClient2->setInstalledPower('12');
        $Point3Budget2016LightClient2->setEquipmentPowerPercentage(2000); // 20%
        $Point3Budget2016LightClient2->setGradation(7000); // 70%
        $Point3Budget2016LightClient2->setGradationHours(1600);
        $Point3Budget2016LightClient2->setSubTotalConsumption(45792*10**2); // 45792 kWh
        $Point3Budget2016LightClient2->setRenovation(false);
        $Point3Budget2016LightClient2->setTotalConsumption(45792*10**2); // 45792 kWh
        $Point3Budget2016LightClient2->setTotal(641088*10**5); // 6410,88 €
        $Point3Budget2016LightClient2->setCreatedAt(new \DateTime('2018-09-01'));
        $Point3Budget2016LightClient2->setUpdatedAt(new \DateTime('2018-11-01'));
        $manager->persist($Point3Budget2016LightClient2);

        $manager->flush();

        $this->setReference('budget-1', $budget1);
        $this->setReference('budget-2', $budget2);
        $this->setReference('budget-3', $budget3);
    }

    public function getDependencies(): array
    {
        return [
            BudgetFixtures::class,
            DeliveryPointFixtures::class
        ];
    }
}