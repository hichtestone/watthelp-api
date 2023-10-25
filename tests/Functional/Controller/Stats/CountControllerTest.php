<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Stats;

use App\Tests\FunctionalWebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group stats
 * @group stats-get
 */
class CountControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanGet(array $expected): void
    {
        $this->connectUser('admin@test.fr');

        $this->client->request(Request::METHOD_GET, '/stats/count');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }
    
    /**
     * @dataProvider getDataProvider
     */
    public function testCannotGet(string $user, int $errorCode, array $expected): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_GET, '/stats/count');

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }
}
