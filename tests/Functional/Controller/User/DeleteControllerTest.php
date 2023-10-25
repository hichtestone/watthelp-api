<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\User;

use App\Query\Criteria;
use App\Manager\UserManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group user
 * @group user-delete
 */
class DeleteControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanDelete(int $id): void
    {
        $this->connectUser('admin@test.fr');
        $connectedUser = $this->getUser('admin@test.fr');

        $userManager = self::$container->get(UserManager::class);
        $user = $userManager->getByCriteria($connectedUser->getClient(), [new Criteria\User\Id($id)]);
        $this->assertNotNull($user);

        $this->client->request(Request::METHOD_DELETE, "/user/$id");

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $user = $userManager->getByCriteria($connectedUser->getClient(), [new Criteria\User\Id($id)]);
        $this->assertNull($user);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotDelete(string $user, int $id, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_DELETE, "/user/$id");

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}