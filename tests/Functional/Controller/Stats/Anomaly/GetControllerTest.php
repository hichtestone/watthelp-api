<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Stats\Anomaly;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group stats
 * @group stats-anomaly
 */
class GetControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanGet(array $expected): void
    {
        $this->connectUser('admin@test.fr');

        $this->client->request(Request::METHOD_GET, '/stats/anomaly');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getDataProvider
     */
    public function testCannotGet(string $user, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_GET, '/stats/anomaly');

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }

}