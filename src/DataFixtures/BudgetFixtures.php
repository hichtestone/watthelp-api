<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Budget;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class BudgetFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $client1 = $this->getReference('client-1');
        $client2 = $this->getReference('client-2');

        $budget1 = new Budget();
        $budget1->setClient($client1);
        $budget1->setYear(2018);
        $budget1->setTotalHours(3650);
        $budget1->setAveragePrice(15*10**5); // 0,15€
        $budget1->setTotalConsumption(58349*10**2); // 58349 kWh
        $budget1->setTotalAmount((int) (8752.35*10**7)); // 8752.35€
        $budget1->setCreatedAt(new \DateTime('2019-09-01'));
        $budget1->setUpdatedAt(new \DateTime('2019-09-01'));
        $manager->persist($budget1);

        $budget2 = new Budget();
        $budget2->setClient($client1);
        $budget2->setYear(2019);
        $budget2->setTotalHours(3650);
        $budget2->setAveragePrice(16*10**5); // 0,16€
        $budget2->setTotalConsumption(69123*10**2); // 69123 kWh
        $budget2->setTotalAmount((int) (11059.68*10**7)); // 11059.68€
        $budget2->setCreatedAt(new \DateTime('2020-09-01'));
        $budget2->setUpdatedAt(new \DateTime('2020-09-01'));
        $manager->persist($budget2);

        $budget3 = new Budget();
        $budget3->setClient($client2);
        $budget3->setYear(2016);
        $budget3->setTotalHours(3660);
        $budget3->setAveragePrice(14*10**5);
        $budget3->setTotalConsumption(45792);
        $budget3->setTotalAmount((int) (6410.88*10**7));
        $budget3->setCreatedAt(new \DateTime('2018-09-01'));
        $budget3->setUpdatedAt(new \DateTime('2018-09-01'));
        $manager->persist($budget3);

        $manager->flush();

        $this->setReference('budget-1', $budget1);
        $this->setReference('budget-2', $budget2);
        $this->setReference('budget-3', $budget3);
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class
        ];
    }
}