<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller\Budget\Import;

use App\Manager\BudgetManager;
use App\Manager\ClientManager;
use App\Query\Criteria;
use App\Tests\FunctionalWebTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group functional
 * @group controller
 * @group import
 * @group budget
 * @group budget-import
 * @group budget-import-post
 */
class PostControllerTest extends FunctionalWebTestCase
{
    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCanPost(array $budgets, int $existingBudgets, array $post, array $expected): void
    {
        $this->connectUser('admin@test.fr');
        $clientManager = self::$container->get(ClientManager::class);
        $client = $clientManager->getByCriteria([new Criteria\Client\Id(1)]);

        $years = array_keys($budgets);

        $budgetManager = self::$container->get(BudgetManager::class);
        $this->assertEquals($existingBudgets, $budgetManager->count(['year' => $years, 'client' => $client]));

        $this->client->request(Request::METHOD_POST, '/budget/import', [], [], [], \json_encode($post));

        $response = $this->client->getResponse()->getContent();

        $this->assertEquals(201, $this->client->getResponse()->getStatusCode(), $response);
        $budgetsImported = $budgetManager->findByFilters($client, ['years' => $years]);
        $this->assertEquals(count($years), count($budgetsImported));

        foreach ($budgetsImported as $budgetImported) {
            $douze = $budgetImported->getDeliveryPointBudgets();
            $dpbIds = $budgets[$budgetImported->getYear()];
            $this->assertEquals(count($budgetImported->getDeliveryPointBudgets()), count($dpbIds));
            foreach ($budgetImported->getDeliveryPointBudgets() as $dpBudget) {
                $this->assertContains($dpBudget->getId(), $dpbIds);
            }
        }

        $this->assertMatchesPattern(\json_encode($expected), $response, $response);
    }

    /**
     * @dataProvider getDataProvider
     *
     * @throws NonUniqueResultException
     */
    public function testCannotPost(string $user, array $post, int $expectedStatusCode, array $expectedResponse): void
    {
        $this->connectUser($user);
        $this->client->request(Request::METHOD_POST, '/budget/import', [], [], [], \json_encode($post));

        $this->assertEquals($expectedStatusCode, $this->client->getResponse()->getStatusCode());
        $this->assertMatchesPattern(\json_encode($expectedResponse), $this->client->getResponse()->getContent());
    }
}