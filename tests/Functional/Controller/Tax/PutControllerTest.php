<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Tax;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group tax
 * @group tax-put
 */
class PutControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCanPut(string $user, int $id, array $put, array $expected): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_PUT, "/tax/$id", [], [], [], \json_encode($put));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $response = $this->client->getResponse()->getContent();

        $this->assertMatchesPattern(\json_encode($expected), $response, $response);
    }

    /**
      * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPut(string $user, int $id, array $put, int $expectedStatusCode, array $expectedErrors): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_PUT, "/tax/$id", [], [], [], \json_encode($put));

        $this->assertEquals($expectedStatusCode, $this->client->getResponse()->getStatusCode());
        $this->assertMatchesPattern(\json_encode($expectedErrors), $this->client->getResponse()->getContent());
    }
}