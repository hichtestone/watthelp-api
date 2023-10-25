<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Permission;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $client1 = $this->getReference('client-1');
        $client2 = $this->getReference('client-2');
        $client3 = $this->getReference('client-3');

        $adminClient1 = new Role();
        $adminClient1->setClient($client1);
        $adminClient1->setName('ROLE_ADMIN');
        $adminClient1->setDescription('Administrator');
        $adminClient1->setPermissions(new ArrayCollection($manager->getRepository(Permission::class)->findAll()));
        $adminClient1->setCreatedAt(new \DateTime('2020-10-15'));
        $adminClient1->setUpdatedAt(new \DateTime('2020-10-15'));
        $manager->persist($adminClient1);

        $dev = new Role();
        $dev->setClient($client1);
        $dev->setName('ROLE_DEV');
        $dev->setDescription('Dev');
        $dev->setPermissions(new ArrayCollection([
            $this->getReference('permission-' . Permission::DELIVERY_POINT_VIEW),
            $this->getReference('permission-' . Permission::DELIVERY_POINT_EDIT),
            $this->getReference('permission-' . Permission::DELIVERY_POINT_DELETE)
        ]));
        $dev->setCreatedAt(new \DateTime('2020-09-15'));
        $dev->setUpdatedAt(new \DateTime('2020-09-17'));
        $manager->persist($dev);

        $roleManager = new Role();
        $roleManager->setClient($client1);
        $roleManager->setName('ROLE_MANAGER');
        $roleManager->setDescription('Manager');
        $roleManager->setPermissions(new ArrayCollection([
            $this->getReference('permission-' . Permission::ROLE_VIEW),
            $this->getReference('permission-' . Permission::ROLE_EDIT),
            $this->getReference('permission-' . Permission::ROLE_DELETE),
            $this->getReference('permission-' . Permission::INVOICE_VIEW),
            $this->getReference('permission-' . Permission::INVOICE_EDIT),
            $this->getReference('permission-' . Permission::INVOICE_DELETE),
            $this->getReference('permission-' . Permission::INVOICE_ANALYZE)
        ]));
        $roleManager->setCreatedAt(new \DateTime('2020-11-01'));
        $roleManager->setUpdatedAt(new \DateTime('2020-11-01'));
        $manager->persist($roleManager);

        $adminClient2 = new Role();
        $adminClient2->setClient($client2);
        $adminClient2->setName('ROLE_ADMIN');
        $adminClient2->setDescription('Administrator');
        $adminClient2->setPermissions(new ArrayCollection($manager->getRepository(Permission::class)->findAll()));
        $adminClient2->setCreatedAt(new \DateTime('2019-10-15'));
        $adminClient2->setUpdatedAt(new \DateTime('2019-10-15'));
        $manager->persist($adminClient2);

        $adminClient3 = new Role();
        $adminClient3->setClient($client3);
        $adminClient3->setName('ROLE_ADMIN');
        $adminClient3->setDescription('Administrator');
        $adminClient3->setPermissions(new ArrayCollection($manager->getRepository(Permission::class)->findAll()));
        $adminClient3->setCreatedAt(new \DateTime('2020-10-15'));
        $adminClient3->setUpdatedAt(new \DateTime('2020-10-15'));
        $manager->persist($adminClient3);


        $manager->flush();

        $this->setReference('role-admin-1', $adminClient1);
        $this->setReference('role-dev', $dev);
        $this->setReference('role-manager', $roleManager);
        $this->setReference('role-admin-2', $adminClient2);
        $this->setReference('role-admin-3', $adminClient3);
    }

    public function getDependencies(): array
    {
        return [
            ClientFixtures::class,
            PermissionFixtures::class
        ];
    }
}