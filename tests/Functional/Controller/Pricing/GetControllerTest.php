<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Pricing;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group pricing
 * @group pricing-get
 */
class GetControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanGet(string $user, int $id, array $expected): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_GET, "/pricing/$id");

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotGet(int $id, int $errorCode, array $expectedError): void
    {
        $this->connectUser('admin@test.fr');

        $this->client->request(Request::METHOD_GET, "/pricing/$id");

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}