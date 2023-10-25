<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Budget\DeliveryPointBudget;

use App\Manager\Budget\DeliveryPointBudgetManager;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group budget
 * @group delivery-point-budget
 * @group delivery-point-budget-delete
 */
class DeleteControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanDelete(int $budgetId, array $deliveryPointBudgetIds, array $body): void
    {
        $this->connectUser('admin@test.fr');
        $clientId = 1;

        $deliveryPointBudgetManager = self::$container->get(DeliveryPointBudgetManager::class);
        $this->assertEquals(count($deliveryPointBudgetIds), $deliveryPointBudgetManager->count(['id' => $deliveryPointBudgetIds]));

        $this->client->request(Request::METHOD_POST, "/budget/$budgetId/delivery-point-budget", [], [], [], \json_encode($body));
        $this->assertEquals(204, $this->client->getResponse()->getStatusCode());

        $this->assertEquals(0, $deliveryPointBudgetManager->count(['id' => $deliveryPointBudgetIds]));
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotDelete(string $user, int $budgetId, array $body, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_POST, "/budget/$budgetId/delivery-point-budget", [], [], [], \json_encode($body));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}