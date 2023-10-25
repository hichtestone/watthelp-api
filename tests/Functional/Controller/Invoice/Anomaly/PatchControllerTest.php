<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Invoice\Anomaly;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group invoice
 * @group invoice-anomaly
 * @group invoice-anomaly-patch
 */
class PatchControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanPatch(int $id, array $patch, array $expected): void
    {
        $this->connectUser('admin@test.fr');
        $this->client->request(Request::METHOD_PATCH, "/invoice/anomaly/$id", [], [], [], json_encode($patch));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPatch(string $user, int $id, array $patch, int $errorCode, array $expected): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_PATCH, "/invoice/anomaly/$id", [], [], [], \json_encode($patch));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }
}