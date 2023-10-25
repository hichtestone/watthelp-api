<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Role;

use App\Manager\RoleManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group role
 * @group role-delete
 */
class DeleteControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanDelete(int $roleId): void
    {
        $this->connectUser('admin@test.fr');
        $clientId = 1;

        $roleManager = self::$container->get(RoleManager::class);
        $this->assertEquals(1, $roleManager->count(['id' => [$roleId], 'client' => $clientId]));

        $this->client->request(Request::METHOD_DELETE, "/role/$roleId");
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(0, $roleManager->count(['id' => [$roleId], 'client' => $clientId]));
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotDelete(string $user, int $roleId, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_DELETE, "/role/$roleId");

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}