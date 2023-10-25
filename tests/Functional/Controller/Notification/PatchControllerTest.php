<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Notification;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group notification
 * @group notification-patch
 */
class PatchControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanPatch(string $user, string $id, array $patch): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_PATCH, "/notification/$id", [], [], [], \json_encode($patch));

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPatch(string $user, string $id, array $patch, int $errorCode, array $expected): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_PATCH, "/notification/$id", [], [], [], \json_encode($patch));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }
}
