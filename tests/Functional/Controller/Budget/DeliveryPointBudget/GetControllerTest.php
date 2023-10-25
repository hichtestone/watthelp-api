<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Budget\DeliveryPointBudget;

use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group budget
 * @group delivery-point-budget
 * @group delivery-point-budget-get
 */
class GetControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanGet(string $user, int $budgetId, int $dpBudgetId, ?string $expand, array $expected): void
    {
        $this->connectUser($user);
        
        if ($expand !== null) {
            $this->setExpandData($expand);
        }

        $this->client->request(Request::METHOD_GET, "/budget/$budgetId/delivery-point-budget/$dpBudgetId");

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotGet(int $budgetId, int $dpBudgetId, int $errorCode, array $expectedError): void
    {
        $this->connectUser('admin@test.fr');

        $this->client->request(Request::METHOD_GET, "/budget/$budgetId/delivery-point-budget/$dpBudgetId");

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}