<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        $client1 = $this->getReference('client-1');
        $client2 = $this->getReference('client-2');
        $client3 = $this->getReference('client-3');

        $time = new \DateTime();

        $user1 = new User();
        $user1->setEmail('admin@test.fr');
        $user1->setFirstname('admin');
        $user1->setLastname('istrator');
        $user1->setPhone('+33601020304');
        $user1->setPassword('$argon2id$v=19$m=65536,t=6,p=1$N0VQOTVCdzk3eTZQLzY2WA$rzXs9S9mYAjLjVrSFePXiCq360xk8WXYxLsSCpCTc4k'); // admin
        $user1->setSuperAdmin(true);
        $user1->setCreatedAt($time);
        $user1->setUpdatedAt($time);
        $user1->setLanguage(User::LANGUAGE_FR);
        $user1->setUserRoles(new ArrayCollection([$this->getReference('role-admin-1')]));
        $client1->addUser($user1);
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('blanc@test.fr');
        $user2->setFirstname('Michel');
        $user2->setLastname('Blanc');
        $user2->setPhone('+376656652');
        $user2->setPassword('$argon2id$v=19$m=65536,t=6,p=1$N0VQOTVCdzk3eTZQLzY2WA$rzXs9S9mYAjLjVrSFePXiCq360xk8WXYxLsSCpCTc4k'); // admin
        $user2->setCreatedAt($time);
        $user2->setUpdatedAt($time);
        $user2->setLanguage(User::LANGUAGE_FR);
        $user2->setUserRoles(new ArrayCollection([$this->getReference('role-dev'), $this->getReference('role-manager')]));
        $client1->addUser($user2);
        $manager->persist($user2);

        $user3 = new User();
        $user3->setEmail('michel@berger.fr');
        $user3->setFirstname('Michel');
        $user3->setLastname('Berger');
        $user3->setPassword('$argon2id$v=19$m=65536,t=6,p=1$N0VQOTVCdzk3eTZQLzY2WA$rzXs9S9mYAjLjVrSFePXiCq360xk8WXYxLsSCpCTc4k'); // admin
        $user3->setCreatedAt($time);
        $user3->setUpdatedAt($time);
        $user3->setLanguage(User::LANGUAGE_FR);
        $user3->setUserRoles(new ArrayCollection([$this->getReference('role-admin-2')]));
        $client2->addUser($user3);
        $manager->persist($user3);

        $user4 = new User();
        $user4->setEmail('fugain@test.fr');
        $user4->setFirstname('Michel');
        $user4->setLastname('Fugain');
        $user4->setPassword('$argon2id$v=19$m=65536,t=6,p=1$N0VQOTVCdzk3eTZQLzY2WA$rzXs9S9mYAjLjVrSFePXiCq360xk8WXYxLsSCpCTc4k'); // admin
        $user4->setCreatedAt($time);
        $user4->setUpdatedAt($time);
        $user4->setLanguage(User::LANGUAGE_FR);
        $user4->setUserRoles(new ArrayCollection([$this->getReference('role-admin-2')]));
        $user4->setDashboard(['test' => 12]);
        $client2->addUser($user4);
        $manager->persist($user4);

        $user5 = new User();
        $user5->setEmail('polnareff@test.fr');
        $user5->setFirstname('Michel');
        $user5->setLastname('Polnareff');
        $user5->setPassword('$argon2id$v=19$m=65536,t=6,p=1$N0VQOTVCdzk3eTZQLzY2WA$rzXs9S9mYAjLjVrSFePXiCq360xk8WXYxLsSCpCTc4k'); // admin
        $user5->setCreatedAt($time);
        $user5->setUpdatedAt($time);
        $user5->setLanguage(User::LANGUAGE_FR);
        $client2->addUser($user5);
        $manager->persist($user5);

        $user6 = new User();
        $user6->setEmail('marcel@patoulachi.fr');
        $user6->setFirstname('Marcel');
        $user6->setLastname('Patoulachi');
        $user6->setPassword('$argon2id$v=19$m=65536,t=6,p=1$N0VQOTVCdzk3eTZQLzY2WA$rzXs9S9mYAjLjVrSFePXiCq360xk8WXYxLsSCpCTc4k'); // admin
        $user6->setCreatedAt($time);
        $user6->setUpdatedAt($time);
        $user6->setLanguage(User::LANGUAGE_EN);
        $user6->setUserRoles(new ArrayCollection([$this->getReference('role-admin-3')]));
        $client3->addUser($user6);
        $manager->persist($user6);

        $manager->persist($client1);
        $manager->persist($client2);
        $manager->persist($client3);

        $manager->flush();

        $this->setReference('user-1', $user1);
        $this->setReference('user-2', $user2);
        $this->setReference('user-3', $user3);
        $this->setReference('user-4', $user4);
        $this->setReference('user-5', $user5);
        $this->setReference('user-6', $user6);
    }

    public function getDependencies()
    {
        return [
            ClientFixtures::class,
            RoleFixtures::class
        ];
    }
}
