<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\DeliveryPoint;

use App\Query\Criteria;
use App\Manager\DeliveryPointManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group delivery-point
 * @group delivery-point-delete
 */
class DeleteControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanDelete(int $id): void
    {
        $this->connectUser('admin@test.fr');
        $user = $this->getUser('admin@test.fr');

        $deliveryPointManager = self::$container->get(DeliveryPointManager::class);
        $deliveryPoint = $deliveryPointManager->getByCriteria($user->getClient(), [new Criteria\DeliveryPoint\Id($id)]);
        $this->client->request(Request::METHOD_DELETE, "/delivery-point/$id");

        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());
        $deliveryPoint = $deliveryPointManager->getByCriteria($user->getClient(), [new Criteria\DeliveryPoint\Id($id)]);
        $this->assertNull($deliveryPoint);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotDelete(string $user, int $id, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_DELETE, "/delivery-point/$id");

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}