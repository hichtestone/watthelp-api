<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Role;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group role
 * @group role-put
 */
class PutControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCanPut(int $roleId, ?string $expand, array $put, array $expected): void
    {
        $this->connectUser('admin@test.fr');

        if ($expand !== null) {
            $this->setExpandData($expand);
        }

        $this->client->request(Request::METHOD_PUT, "/role/$roleId", [], [], [], \json_encode($put));

        $response = $this->client->getResponse()->getContent();
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $response);

        $this->assertMatchesPattern(\json_encode($expected), $response, $response);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPut(string $user, int $roleId, array $put, int $errorCode, array $expectedErrors): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_PUT, "/role/$roleId", [], [], [], \json_encode($put));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode());
        $this->assertMatchesPattern(\json_encode($expectedErrors), $this->client->getResponse()->getContent());
    }
}