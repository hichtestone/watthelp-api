<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ClientFixtures extends Fixture
{
    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $client1 = new Client();
        $client1->setName('Client1');
        $client1->setEnabled(true);
        $client1->setZipCode('71450');
        $client1->setCity('Blanzy');
        $client1->setInsee('71040');
        $client1->setDepartment('71');
        $client1->setDefaultLanguage(User::LANGUAGE_FR);
        $manager->persist($client1);

        $client2 = new Client();
        $client2->setName('Engie');
        $client2->setEnabled(true);
        $client2->setZipCode('83520');
        $client2->setCity('Roquebrune sur Argens');
        $client2->setInsee('83107');
        $client2->setDepartment('83');
        $client2->setDefaultLanguage(User::LANGUAGE_FR);
        $manager->persist($client2);

        $client3 = new Client();
        $client3->setName('Direct Energie');
        $client3->setEnabled(true);
        $client3->setAddress('3 Boulevard de la Croisette');
        $client3->setZipCode('93600');
        $client3->setCity('Aulnay sous Bois');
        $client3->setInsee('93005');
        $client3->setDepartment('93');
        $client3->setDefaultLanguage(User::LANGUAGE_EN);
        $manager->persist($client3);

        $manager->flush();

        $this->setReference('client-1', $client1);
        $this->setReference('client-2', $client2);
        $this->setReference('client-3', $client3);
    }
}
