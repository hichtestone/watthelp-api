<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Permission;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PermissionFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        foreach (Permission::AVAILABLE_PERMISSIONS as $code => $data) {
            $permission = new Permission();
            $permission->setCode($code);
            $permission->setDescription($data['description']);
            $manager->persist($permission);

            $this->setReference("permission-$code", $permission);
        }

        $manager->flush();
    }
}