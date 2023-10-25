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
 * @group delivery-point-budget-put
 */
class PutControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     */
    public function testCanPut(int $budgetId, int $dpBudgetId, ?string $expand, array $put, array $expected): void
    {
        $this->connectUser('admin@test.fr');
        
        if ($expand !== null) {
            $this->setExpandData($expand);
        }

        $this->client->request(Request::METHOD_PUT, "/budget/$budgetId/delivery-point-budget/$dpBudgetId", [], [], [], json_encode($put));

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expected), $this->client->getResponse()->getContent());
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPut(string $user, int $budgetId, int $dpBudgetId, array $put, int $errorCode, array $expectedError): void
    {
        $this->connectUser($user);

        $this->client->request(Request::METHOD_PUT, "/budget/$budgetId/delivery-point-budget/$dpBudgetId", [], [], [], json_encode($put));

        $this->assertEquals($errorCode, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertMatchesPattern(\json_encode($expectedError), $this->client->getResponse()->getContent());
    }
}