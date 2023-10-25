<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\DeliveryPoint;

use App\Tests\FunctionalWebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group delivery-point
 * @group delivery-point-map
 */
class MapControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanGetMap(array $expected): void
    {
        $this->connectUser('admin@test.fr');

        $this->client->request(Request::METHOD_GET, '/delivery-point/map');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testCannotGetMap(string $user, int $errorCode, array $expected): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_GET, '/delivery-point/map');

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }
}