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
 * @group role-post
 */
class PostControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCanPost(?string $expand, array $post, array $expected): void
    {
        $this->connectUser('admin@test.fr');
        $newRoleId = 6;

        if ($expand !== null) {
            $this->setExpandData($expand);
        }

        $roleManager = self::$container->get(RoleManager::class);
        $this->assertEquals(0, $roleManager->count(['id' => [$newRoleId]]));

        $this->client->request(Request::METHOD_POST, '/role', [], [], [], \json_encode($post));

        $response = $this->client->getResponse()->getContent();
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), $response);

        $this->assertMatchesPattern(\json_encode($expected), $response, $response);
        $this->assertEquals(1, $roleManager->count(['id' => [$newRoleId]]));
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPost(string $user, array $post, int $errorCode, array $expectedErrors): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_POST, '/role', [], [], [], \json_encode($post));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode());
        $this->assertMatchesPattern(\json_encode($expectedErrors), $this->client->getResponse()->getContent());
    }
}